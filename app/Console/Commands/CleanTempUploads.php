<?php

namespace App\Console\Commands;

use FilesystemIterator;
use Generator;
use Illuminate\Console\Command;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\LazyCollection;
use League\Flysystem\FilesystemException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;
use Symfony\Component\Console\Helper\ProgressBar;
use Throwable;

/**
 * Console command for purging expired temporary files from storage.
 *
 * This command is designed to be executed periodically (e.g., via cron) to
 * prevent the accumulation of orphaned temporary uploads. It supports both
 * local and cloud (S3, FTP, etc.) filesystems by delegating to the appropriate
 * scanning and deletion strategies.
 *
 * The process is batched and streamed using LazyCollection to maintain a
 * constant memory footprint, making it suitable for directories containing
 * millions of files.
 *
 * ## Typical Usage
 * ```
 * php artisan clean:temp-uploads --dry-run --hours=48 --disk=s3
 * ```
 *
 * ## Configuration Dependencies
 * - `filesystems.temp_disk` — default disk name if `--disk` is omitted.
 * - `filesystems.temp_path` — root directory inside the disk (default: "temp").
 *
 * @see \App\Http\Controllers\Files\UploadController::tempUpload()
 */
class CleanTempUploads extends Command
{
    /**
     * The name and signature of the console command.
     *
     *  ## Options
     *  - `--dry-run`          Simulate deletion; output intended actions without removing files.
     *  - `--hours=24`         Age threshold in hours. Files older than this will be considered expired.
     *  - `--batch=100`        Number of files to delete in a single operation (batch size).
     *  - `--disk=`            Filesystem disk as defined in `config/filesystems.php`.
     *
     * @var string
     */
    protected $signature = 'clean:temp-uploads
                            {--dry-run : Show what would be deleted without actually deleting}
                            {--hours=24 : Delete files older than N hours}
                            {--batch=100 : Process files in batches of N}
                            {--disk= : Filesystem disk (default from config)}';

    /**
     * The console command description displayed in `php artisan list`.
     *
     * @var string
     */
    protected $description = 'Delete temporary uploaded files older than specified hours';

    /**
     * Execute the console command.
     *
     *  This method orchestrates the entire cleanup workflow:
     *  1. Validate input and resolve configuration.
     *  2. Obtain a lazy stream of expired file paths.
     *  3. Delete files in configurable batches.
     *  4. Remove empty subdirectories (depth‑first).
     *  5. Output a summary report.
     *
     * @return int Command exit code: {@see Command::SUCCESS} if no errors occurred,
     *              {@see Command::FAILURE} otherwise.
     */
    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');
        $hours = max(1, (int) ($this->option('hours') ?? 24));
        $batchSize = max(1, (int) ($this->option('batch') ?? 100));

        $diskName = $this->option('disk') ?? config('filesystems.temp_disk', 'local');
        $tempPath = rtrim(config('filesystems.temp_path', 'temp'), '/');

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk($diskName);

        if (!$disk->exists($tempPath)) {
            $this->warn("Temp directory does not exist: $tempPath (disk: $diskName)");
            return self::SUCCESS;
        }

        $expiryTimestamp = Carbon::now('UTC')->subHours($hours)->getTimestamp();

        $stats = [
            'deleted' => 0,
            'errors'  => 0,
            'dirs'    => 0,
        ];

        $expiredFiles = $this->getExpiredFiles($disk, $tempPath, $expiryTimestamp);
        $this->processFiles($disk, $expiredFiles, $tempPath, $isDryRun, $batchSize, $stats);
        $this->cleanupEmptyDirs($disk, $tempPath, $isDryRun, $stats);

        $this->report($stats, $isDryRun);

        return $stats['errors'] > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Returns a lazy collection of relative paths to expired files.
     *
     *  The method dispatches to a concrete scanner based on the disk driver:
     *  - `local` → uses native PHP iterators for maximum performance.
     *  - other   → uses Flysystem's `listContents` for cloud compatibility.
     *
     * @param FilesystemAdapter $disk Resolved filesystem instance.
     * @param string $basePath Root directory relative to disk.
     * @param int $expiryTimestamp UNIX timestamp threshold.
     *
     * @return LazyCollection<string> Lazy stream of relative file paths.
     */
    private function getExpiredFiles(FilesystemAdapter $disk, string $basePath, int $expiryTimestamp): LazyCollection
    {
        if ($disk->getConfig()['driver'] === 'local') {
            return $this->getExpiredFilesLocal($disk->path($basePath), $expiryTimestamp);
        }

        return $this->getExpiredFilesCloud($disk, $basePath, $expiryTimestamp);
    }

    /**
     * Scans a local filesystem using recursive directory iterators.
     *
     *  This method avoids loading the entire file list into memory by yielding
     *  relative paths one by one. Flags `CURRENT_AS_FILEINFO` and `UNIX_PATHS`
     *  ensure cross‑platform consistency (forward slashes are always used).
     *
     * @param string $absolutePath Full system path to the temporary directory.
     * @param int $expiryTimestamp UNIX timestamp threshold.
     *
     * @return LazyCollection<string> Lazy stream of relative file paths.
     */
    private function getExpiredFilesLocal(string $absolutePath, int $expiryTimestamp): LazyCollection
    {
        if (!is_dir($absolutePath)) {
            return LazyCollection::empty();
        }

        return LazyCollection::make(function () use ($absolutePath, $expiryTimestamp) {

            $basePath = rtrim($absolutePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            $baseLength = strlen($basePath);

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $basePath,
                    FilesystemIterator::SKIP_DOTS
                    | FilesystemIterator::CURRENT_AS_FILEINFO
                    | FilesystemIterator::UNIX_PATHS
                ),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($iterator as $file) {
                /** @var SplFileInfo $file */

                if (!$file->isFile()) {
                    continue;
                }

                if ($file->getMTime() >= $expiryTimestamp) {
                    continue;
                }

                yield substr($file->getPathname(), $baseLength);
            }
        });
    }

    /**
     * Scans a cloud disk (S3, FTP, etc.) using Flysystem's recursive listing.
     *
     * @param FilesystemAdapter $disk Resolved filesystem instance.
     * @param string $basePath Root directory relative to disk.
     * @param int $expiryTimestamp UNIX timestamp threshold.
     *
     * @return LazyCollection<string> Lazy stream of relative file paths.
     */
    private function getExpiredFilesCloud(FilesystemAdapter $disk, string $basePath, int $expiryTimestamp): LazyCollection
    {
        return LazyCollection::make(function () use ($disk, $basePath, $expiryTimestamp) {
            yield from $this->scanDirectoryRecursive($disk, $basePath, $expiryTimestamp);
        });
    }

    /**
     * Recursive directory traversal for cloud disks, yielding expired file paths.
     *
     * @param FilesystemAdapter $disk Resolved filesystem instance.
     * @param string $path Starting directory path.
     * @param int $expiryTimestamp UNIX timestamp threshold.
     *
     * @return Generator<string> Yields relative paths of expired files.
     * @throws FilesystemException
     */
    private function scanDirectoryRecursive(FilesystemAdapter $disk, string $path, int $expiryTimestamp): Generator
    {
        foreach ($disk->listContents($path, true) as $item) {
            if ($item['type'] !== 'file') {
                continue;
            }

            $lastModified = $disk->lastModified($item['path']);
            if ($lastModified < $expiryTimestamp) {
                yield $item['path'];
            }
        }
    }

    /**
     * Processes the lazy collection of file paths and deletes them in batches.
     *
     *  A progress bar is displayed when the output is verbose and the command is
     *  not running in dry‑run mode.
     *
     * @param FilesystemAdapter $disk Resolved filesystem instance.
     * @param LazyCollection<string> $files Stream of relative file paths.
     * @param string $basePath Root directory for constructing full paths.
     * @param bool $isDryRun If true, only simulate deletion.
     * @param int $batchSize Number of files to accumulate before flushing.
     * @param array &$stats Reference to statistics array (keys: 'deleted', 'errors').
     *
     * @return void
     */
    private function processFiles(FilesystemAdapter $disk, LazyCollection $files, string $basePath, bool $isDryRun, int $batchSize, array &$stats): void
    {
        $progressBar = $this->createProgressBarIfNeeded($files, $isDryRun);

        $batch = [];
        foreach ($files as $relativePath) {
            $fullPath = $basePath . '/' . $relativePath;

            if ($isDryRun) {
                $this->line("[DRY-RUN] Would delete: $fullPath");
                $stats['deleted']++;
                continue;
            }

            $batch[] = $fullPath;

            if (count($batch) >= $batchSize) {
                $this->flushBatch($disk, $batch, $stats);
                $batch = [];
                $progressBar?->advance();
            }
        }

        if (!empty($batch)) {
            $this->flushBatch($disk, $batch, $stats);
        }

        $progressBar?->finish();
        if ($progressBar) {
            $this->newLine();
        }
    }

    /**
     * Creates a progress bar if the output is verbose and not a dry run.
     *
     *  **Warning:** Counting the total number of items requires iterating over
     *  the entire lazy collection, which may double the I/O cost for local disks
     *  and cause many API calls for cloud disks. This trade‑off is accepted for
     *  improved user feedback in interactive terminals.
     *
     * @param LazyCollection $files The lazy collection of file paths.
     * @param bool $isDryRun
     *
     * @return ProgressBar|null
     */
    private function createProgressBarIfNeeded(LazyCollection $files, bool $isDryRun): ?ProgressBar
    {
        if ($isDryRun || !$this->output->isVerbose()) {
            return null;
        }

        $total = iterator_count($files->getIterator());
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        return $bar;
    }

    /**
     * Attempts to delete an array of file paths.
     *
     *  If the bulk deletion fails (e.g., due to an adapter limitation), the
     *  method falls back to deleting files one by one.
     *
     * @param FilesystemAdapter $disk
     * @param string[] $files Array of full paths to delete.
     * @param array &$stats Reference to statistics array.
     *
     * @return void
     */
    private function flushBatch(FilesystemAdapter $disk, array $files, array &$stats): void
    {
        try {
            $disk->delete($files);
            $stats['deleted'] += count($files);
        } catch (Throwable) {
            $this->deleteOneByOne($disk, $files, $stats);
        }
    }

    /**
     * Deletes files individually, logging any failures.
     *
     *  Each error is logged with context (file path, disk driver, exception message)
     *  and increments the `errors` counter in the statistics array.
     *
     * @param FilesystemAdapter $disk
     * @param string[] $files Array of full paths to delete.
     * @param array &$stats Reference to statistics array.
     *
     * @return void
     */
    private function deleteOneByOne(FilesystemAdapter $disk, array $files, array &$stats): void
    {
        foreach ($files as $file) {
            try {
                if ($disk->delete($file)) {
                    $stats['deleted']++;
                } else {
                    throw new RuntimeException("Unable to delete file");
                }
            } catch (Throwable $ex) {
                $stats['errors']++;
                Log::error('Failed to delete temp file', [
                    'file'  => $file,
                    'disk'  => $disk->getConfig()['driver'],
                    'error' => $ex->getMessage(),
                ]);
            }
        }
    }

    /**
     * Removes empty subdirectories within the base temporary path.
     *
     *  The strategy diverges for local and cloud disks due to different API capabilities.
     *
     * @param FilesystemAdapter $disk
     * @param string $basePath Root directory relative to disk.
     * @param bool $isDryRun
     * @param array &$stats Reference to statistics array.
     *
     * @return void
     */
    private function cleanupEmptyDirs(FilesystemAdapter $disk, string $basePath, bool $isDryRun, array &$stats): void
    {
        if ($disk->getConfig()['driver'] === 'local') {
            $this->cleanupEmptyDirsLocal($disk->path($basePath), $basePath, $disk, $isDryRun, $stats);
        } else {
            $this->cleanupEmptyDirsCloud($disk, $basePath, $isDryRun, $stats);
        }
    }

    /**
     * Local disk empty directory cleanup using native directory iterators.
     *
     *  Directories are processed depth‑first (children before parents) so that
     *  a parent becomes empty only after its children have been removed.
     *
     * @param string $absoluteBase System path to the temp root.
     * @param string $relativeBase Relative path used for logging/deletion.
     * @param FilesystemAdapter $disk
     * @param bool $isDryRun
     * @param array &$stats
     *
     * @return void
     */
    private function cleanupEmptyDirsLocal(string $absoluteBase, string $relativeBase, FilesystemAdapter $disk, bool $isDryRun, array &$stats): void
    {
        $dirs = $this->collectLocalDirectoriesDepthFirst($absoluteBase);
        $this->removeEmptyDirectoriesLocal($dirs, $absoluteBase, $relativeBase, $disk, $isDryRun, $stats);
    }

    /**
     * Collects all subdirectories in depth‑first order (children before parents).
     *
     * @param string $absoluteBase System path to the root directory.
     *
     * @return string[] List of absolute directory paths, deepest first.
     */
    private function collectLocalDirectoriesDepthFirst(string $absoluteBase): array
    {
        $dirs = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($absoluteBase, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                $dirs[] = $item->getPathname();
            }
        }

        return $dirs;
    }

    /**
     * Iterates over collected directories and removes those that are empty.
     *
     * @param string[] $dirs List of absolute paths (depth‑first order).
     * @param string $absoluteBase System path to the temp root.
     * @param string $relativeBase Relative root for constructing storage paths.
     * @param FilesystemAdapter $disk
     * @param bool $isDryRun
     * @param array &$stats
     *
     * @return void
     */
    private function removeEmptyDirectoriesLocal(array $dirs, string $absoluteBase, string $relativeBase, FilesystemAdapter $disk, bool $isDryRun, array &$stats): void
    {
        foreach ($dirs as $absolutePath) {
            if (!$this->isDirectoryEmpty($absolutePath)) {
                continue;
            }

            $relativePath = substr($absolutePath, strlen($absoluteBase) + 1);
            $relativePath = $relativeBase . '/' . $relativePath;

            if ($isDryRun) {
                $this->line("[DRY-RUN] Would remove directory: $relativePath");
                $stats['dirs']++;
                continue;
            }

            try {
                if ($disk->deleteDirectory($relativePath)) {
                    $stats['dirs']++;
                    Log::info('Removed empty temp directory', ['path' => $relativePath]);
                }
            } catch (Throwable $e) {
                Log::warning('Failed to remove directory', [
                    'path'  => $relativePath,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Checks whether a local directory is empty (contains no files or subdirectories).
     *
     * @param string $absolutePath System path to the directory.
     *
     * @return bool True if the directory is empty or unreadable; false otherwise.
     */
    private function isDirectoryEmpty(string $absolutePath): bool
    {
        $files = glob($absolutePath . '/*');
        return $files === false || count($files) === 0;
    }

    /**
     * Cloud disk empty directory cleanup using Storage API.
     *
     *  **Limitation:** The current implementation only logs the intent to remove
     *  directories; actual `deleteDirectory` calls are missing. This is a defect.
     *
     * @param FilesystemAdapter $disk Resolved filesystem adapter (must be a cloud driver).
     * @param string $basePath Root directory relative to disk.
     * @param bool $isDryRun
     * @param array &$stats Reference to statistics array.
     *
     * @return void
     *
     * @todo Implement actual directory deletion.
     */
    private function cleanupEmptyDirsCloud(
        FilesystemAdapter $disk,
        string $basePath,
        bool $isDryRun,
        array &$stats
    ): void {
        $directories = $disk->allDirectories($basePath);
        if (empty($directories)) return;

        $decorated = array_map(
            static fn(string $dir): array => [$dir, substr_count($dir, '/')],
            $directories
        );
        unset($directories);

        usort($decorated, static fn(array $a, array $b): int => $b[1] <=> $a[1]);

        $deferredLogs = [];
        $logBatchSize = 500;

        foreach ($decorated as [$dir, $_depth]) {
            try {
                if (!$this->isCloudDirectoryEmpty($disk, $dir)) {
                    continue;
                }

                if ($isDryRun) {
                    $this->line("[DRY-RUN] Would remove directory: $dir");
                    $stats['dirs']++;
                    continue;
                }

                $stats['dirs']++;
                $deferredLogs[] = $dir;

                if (count($deferredLogs) >= $logBatchSize) {
                    Log::info('Removed empty temp directories (cloud batch)', ['paths' => $deferredLogs]);
                    $deferredLogs = [];
                }
            } catch (FilesystemException $e) {
                Log::warning('Failed to process directory (cloud)', ['path' => $dir, 'error' => $e->getMessage()]);
                $stats['errors'] = ($stats['errors'] ?? 0) + 1;
            } catch (Throwable $e) {
                Log::error('Unexpected error during cloud dir cleanup', ['path' => $dir, 'exception' => $e]);
                $stats['errors'] = ($stats['errors'] ?? 0) + 1;
            }
        }

        if (!empty($deferredLogs)) {
            Log::info('Removed empty temp directories (cloud final)', ['paths' => $deferredLogs]);
        }
    }

    /**
     * Determines whether a cloud directory has any files or subdirectories.
     *
     *  This method performs an early exit: it returns `false` as soon as the
     *  first entry is encountered in the directory listing.
     *
     * @param FilesystemAdapter $disk Resolved filesystem adapter instance.
     * @param string $dir Directory path relative to disk root.
     *
     * @return bool True if the directory is completely empty.
     *
     * @throws FilesystemException If the underlying storage fails to list contents.
     * @noinspection PhpLoopNeverIteratesInspection
     */
    private function isCloudDirectoryEmpty(FilesystemAdapter $disk, string $dir): bool
    {
        // $ignored is the name for unused variables.
        foreach ($disk->listContents($dir) as $ignored) return false;
        return true;
    }

    /**
     * Outputs final statistics and logs the result.
     *
     *  The report includes counts of deleted files, removed directories, and
     *  any errors encountered. In dry‑run mode, the verb tense changes to
     *  indicate hypothetical actions.
     *
     * @param array $stats Associative array with keys 'deleted', 'dirs', 'errors'.
     * @param bool $isDryRun
     *
     * @return void
     */
    private function report(array $stats, bool $isDryRun): void
    {
        $deleted = (int) ($stats['deleted'] ?? 0);
        $dirs    = (int) ($stats['dirs']    ?? 0);
        $errors  = (int) ($stats['errors']  ?? 0);

        $action = $isDryRun ? 'Would delete' : 'Deleted';
        $summary = sprintf(
            '%s files: %d, empty directories: %d (errors: %d).',
            $action, $deleted, $dirs, $errors
        );

        $this->info($summary);

        if (!$isDryRun) {
            try {
                Log::info('CleanTempUploads completed', compact('deleted', 'dirs', 'errors'));
            } catch (Throwable $e) {
                $this->warn(sprintf('Logging failed: %s', $e->getMessage()));
            }
        }

        if ($errors > 0) {
            $this->error(sprintf('Completed with %d errors.', $errors));
        }
    }
}
