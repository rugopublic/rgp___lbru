<?php

declare(strict_types=1);

namespace App\Services\Admin\Generic;

use App\Models\User;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ItemNotFoundException;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Class FileService
 *
 * Handles the full lifecycle of file management:
 *  - `upload`: Manages the upload process of the given file.
 *  - `ready`: Checks whether an upload is ready to be placed.
 *  - `place`: Moves the previously uploaded file to the given location.
 *  - `delete`: Deletes the previously uploaded file safely.
 *  - `exists`: Checks whether the given upload exists.
 *  - `extension`: Retrieves the extension of the given uploaded file.
 *
 * This service encapsulates all the logic related to file management,
 * keeping controllers thin and focused on HTTP layer responsibilities.
 */
class FileService
{
    /**
     * The base directory for temporary files.
     *
     * @var string
     */
    private string $baseDir;

    /**
     * The disk for file management.
     *
     * @var string
     */
    private string $disk;

    /**
     * The storage instance for file management.
     *
     * @var Filesystem
     */
    private Filesystem $storage;

    /**
     * Initializes the file service instance and wires its required dependencies.
     * @return void
     */
    public function __construct()
    {
        $rootFolder = config('files.temp_folder');

        $this->baseDir = "{$rootFolder}";
        $this->disk    = config('filesystems.default');
        $this->storage = Storage::disk($this->disk);
    }

    /**
     * Builds a file path from the given parameters.
     *
     * @param User      $owner      The user uploading this file.
     * @param string    $uploadId   The unique identifier of this upload.
     *
     * @return string The path relative to the disk's root.
     */
    protected function assemblePath(
        User            $owner,
        string          $uploadId
    ): string
    {
        return "{$this->baseDir}/{$owner->id}/"
                . segmentUuid($uploadId)->join('/')
                . "/{$uploadId}";
    }

    /**
     * Puts the provided file into the appropriate temporary folder.
     * This should be used for non-chunked uploads.
     *
     * @param UploadedFile $file          The file sent through a form.
     * @param User         $owner         The user uploading this file.
     * @param string       $uploadId      The unique identifier of this upload.
     *
     * @return string|false The path of the stored file relative to the disk's root
     *                      on success or false on failure.
     */
    private function uploadWhole(
        UploadedFile    $file,
        User            $owner,
        string          $uploadId
    ): string|false
    {
        $path = $this->assemblePath($owner, $uploadId);
        $name = "file.{$file->extension()}";

        return $this->storage->putFileAs($path, $file, $name);
    }

    /**
     * Puts the provided file chunk into the appropriate temporary folder.
     * This should be used for chunked uploads.
     *
     * @param UploadedFile $file          The file chunk sent through a form.
     * @param User         $owner         The user uploading this file.
     * @param string       $uploadId      The unique identifier of this upload.
     * @param int          $chunkNumber   The number of the provided chunk.
     *
     * @return string|false The path of the stored file chunk relative to the disk's root
     *                      on success or false on failure.
     */
    private function uploadChunk(
        UploadedFile    $file,
        User            $owner,
        string          $uploadId,
        int             $chunkNumber
    ): string|false
    {
        $path = $this->assemblePath($owner, $uploadId);
        $extension = Str::afterLast($file->getClientOriginalName(), '.');

        $name = "file.{$extension}.part{$chunkNumber}";

        return $this->storage->putFileAs($path, $file, $name);
    }

    /**
     * Merge the file from its chunks in the appropriate temporary folder.
     * This should be used for chunked uploads.
     *
     * @param User         $owner         The user uploading this file.
     * @param string       $uploadId      The unique identifier of this upload.
     * @param int          $chunksTotal   The total number of chunks for this file.
     *
     * @return string|false The path of the merged file relative to the disk's root
     *                      on success or false if we still wait for the chunks.
     */
    private function mergeChunks(
        User            $owner,
        string          $uploadId,
        int             $chunksTotal
    ): string|false
    {
        $path = $this->assemblePath($owner, $uploadId);

        $chunks = collect($this->storage->files($path));

        // Check if any chunks are missing.
        for($chunkId = 1; $chunkId <= $chunksTotal; $chunkId++) {
            try {
                $chunks->firstOrFail(
                    fn ($chunk)
                        => Str::endsWith($chunk, ".part{$chunkId}") !== false
                );
            } catch (ItemNotFoundException) {
                return false;
            }
        }

        // Resolve the complete chunk set in numeric order. Do not rely on
        // filesystem listing order: remote adapters are not required to sort it.
        $chunkNames = [];
        $merged = null;

        for ($chunkId = 1; $chunkId <= $chunksTotal; $chunkId++) {
            $suffix = ".part{$chunkId}";
            $matches = $chunks->filter(
                static fn (string $chunk): bool => Str::endsWith($chunk, $suffix)
            )->values();

            if ($matches->count() !== 1) {
                return false;
            }

            $chunkName = $matches->first();
            $candidate = substr($chunkName, 0, -strlen($suffix));

            if ($merged !== null && $candidate !== $merged) {
                // All chunks must belong to the same original file name.
                return false;
            }

            $merged = $candidate;
            $chunkNames[] = $chunkName;
        }

        if ($merged === null) {
            return false;
        }

        // Use streams only. Filesystem adapters for S3/FTP/etc. do not
        // necessarily expose a local absolute path through path().
        $stream = fopen('php://temp/maxmemory:10485760', 'w+b');

        if ($stream === false) {
            return false;
        }

        try {
            foreach ($chunkNames as $chunkName) {
                $chunk = $this->storage->readStream($chunkName);

                if ($chunk === null) {
                    throw new RuntimeException("Failed to read chunk {$chunkName}.");
                }

                try {
                    if (stream_copy_to_stream($chunk, $stream) === false) {
                        throw new RuntimeException("Failed to merge chunk {$chunkName}.");
                    }
                } finally {
                    if (is_resource($chunk)) {
                        fclose($chunk);
                    }
                }
            }

            if (!rewind($stream) || !$this->storage->writeStream($merged, $stream)) {
                throw new RuntimeException("Failed to write merged upload {$merged}.");
            }
        } catch (Throwable) {
            // Cleanup of any incomplete destination object.
            $this->storage->delete($merged);

            return false;
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        // Cleanup of chunks only after the merged object was written successfully.
        foreach ($chunkNames as $chunkName) {
            $this->storage->delete($chunkName);
        }

        return $merged;
    }

    /**
     * Retrieves a given file to proceed with the upload.
     * If $chunksTotal = 1, the file is treated as a whole.
     * Otherwise, it will be processed as a chunked file.
     *
     * @param UploadedFile $file          The file sent through a form.
     * @param User         $owner         The user uploading this file.
     * @param string       $uploadId      The unique identifier of this upload.
     * @param int          $chunkNumber   The number of a chunk of this file.
     * @param int          $chunksTotal   The total number of chunks for this file.
     *
     * @return array{
     *     partial: bool,
     *     success: bool
     * } The upload status.
     */
    public function upload(
        UploadedFile    $file,
        User            $owner,
        string          $uploadId,
        int             $chunkNumber = 1,
        int             $chunksTotal = 1
    ): array
    {
        // Instant upload.
        if($chunksTotal === 1) {
            return [
                'partial' => false,
                'success' => $this->uploadWhole($file, $owner, $uploadId) !== false
            ];
        }

        // Chunked upload.
        $chunk = $this->uploadChunk($file, $owner, $uploadId, $chunkNumber);

        // Early exit on failed chunk upload to not waste resources on merge attempt.
        if($chunk === false)
            return [
                'partial' => true,
                'success' => false
            ];

        // Attempt to merge chunks if every chunk is present.
        $merged = $this->mergeChunks($owner, $uploadId, $chunksTotal);

        return [
            'partial' => ($merged === false),
            'success' => true
        ];
    }

    /**
     * Checks whether the upload is ready to be placed.
     *
     * An upload is considered ready when its directory exists
     * and contains exactly one file.
     *
     * @param User   $owner    The user owning this upload.
     * @param string $uploadId The unique identifier of this upload.
     *
     * @return bool Whether the upload is ready to be placed.
     */
    public function ready(
        User $owner,
        string $uploadId
    ): bool
    {
        $path = $this->assemblePath($owner, $uploadId);

        return $this->storage->exists($path)
            && count($this->storage->files($path)) === 1;
    }

    /**
     * Moves the previously uploaded file to the given location.
     *
     * @param User      $owner     The user owning this upload.
     * @param string    $uploadId  The unique identifier of this upload.
     * @param string    $to        The target filepath.
     * @param string    $name      The target filename without extension.
     * @param bool      $cleanup   Whether to remove the upload folder. Defaults to `true`.
     * @param ?string   $disk      The disk to move the file to. Defaults to `null`.
     *
     * @return array{
     *     success:   bool,
     *     extension: string|null
     * } The placement result and file extension.
     */
    public function place(
        User    $owner,
        string  $uploadId,
        string  $to,
        string  $name,
        bool    $cleanup = true,
        ?string $disk = null
    ): array
    {
        $source = $this->assemblePath($owner, $uploadId);
        $files = $this->storage->files($source);

        if (!$this->ready($owner, $uploadId)) {
            Log::warning(
                "Unable to place upload. Expected exactly one file. "
                . "Upload ID: {$uploadId}. Owned by: {$owner->id}."
            );
            return [
                'success'   => false,
                'extension' => null
            ];
        }

        $file = $files[0];

        $extension   = Str::afterLast($file, '.');
        $destination = Str::rtrim($to, '/') . "/{$name}.{$extension}";

        $success = false;

        if($disk === null || $disk === $this->disk) {
            $success = $this->storage->move($file, $destination);
        } else {
            $toFilesystem = Storage::disk($disk);

            $stream = $this->storage->readStream($file);

            if ($stream === null) {
                Log::warning(
                    "Failed to setup the place() stream. "
                    . "Upload ID: {$uploadId}. Owned by: {$owner->id}."
                );
                return [
                    'success'   => false,
                    'extension' => $extension
                ];
            }

            try {
                $success = $toFilesystem->put($destination, $stream);
            } finally {
                fclose($stream);
            }
        }

        if ($success && $cleanup) {
            $cleanupSuccess = false;

            if ($disk === null || $disk === $this->disk) {
                $cleanupSuccess = $this->storage->deleteDirectory($source);
            } else {
                $cleanupSuccess =
                    $this->storage->delete($file)
                    && $this->storage->deleteDirectory($source);
            }

            if (!$cleanupSuccess) {
                Log::warning(
                    "The upload directory was not removed. "
                    . "Upload ID: {$uploadId}. Owned by: {$owner->id}."
                );
            }
        }

        return [
            'success'   => $success,
            'extension' => $extension
        ];
    }

    /**
     * Deletes the previously uploaded file safely.
     *
     * The whole upload session directory is removed, including
     * the uploaded file or all currently uploaded chunks.
     *
     * @param User   $owner    The user owning this upload.
     * @param string $uploadId The unique identifier of this upload.
     *
     * @return bool Whether the upload session was deleted successfully.
     */
    public function delete(
        User $owner,
        string $uploadId
    ): bool
    {
        $path = $this->assemblePath($owner, $uploadId);

        return $this->storage->deleteDirectory($path);
    }

    /**
     * Checks whether the upload exists for the given owner.
     *
     * @param User   $owner    The user owning this upload.
     * @param string $uploadId The unique identifier of this upload.
     *
     * @return bool Whether the upload exists.
     */
    public function exists(
        User $owner,
        string $uploadId
    ): bool
    {
        $path = $this->assemblePath($owner, $uploadId);

        return $this->storage->exists($path);
    }

    /**
     * Gets the file extension of the given upload.
     *
     * @param  User   $owner         The user owning this upload.
     * @param  string $uploadId      The unique identifier of this upload.
     * @param ?bool   $useMetadata   Whether to use PHP's metadata check. Defaults to `false`.
     *
     * @throws RuntimeException If there is no file in the upload directory.
     *
     * @return string|null Returns null if file doesn't exist or its extension otherwise.
     */

    /**
     * Returns the detected MIME type of a ready upload without moving it.
     */
    public function mimeType(User $owner, string $uploadId): ?string
    {
        if (!$this->ready($owner, $uploadId)) {
            return null;
        }

        $path = $this->assemblePath($owner, $uploadId);
        $files = $this->storage->files($path);
        return $files === [] ? null : $this->storage->mimeType($files[0]);
    }

    /**
     * Returns the size in bytes of a ready upload.
     */
    public function size(User $owner, string $uploadId): ?int
    {
        if (!$this->ready($owner, $uploadId)) {
            return null;
        }

        $path = $this->assemblePath($owner, $uploadId);
        $files = $this->storage->files($path);
        return $files === [] ? null : $this->storage->size($files[0]);
    }

    /**
     * Returns the extension value used by the service.
     *
     * @param User $owner
     * @param string $uploadId
     * @param bool $useMetadata
     * @return string|null
     */
    public function extension(
        User    $owner,
        string  $uploadId,
        bool    $useMetadata = false
    ): string|null
    {
        if(!$this->exists($owner, $uploadId))
            return null;

        $path = $this->assemblePath($owner, $uploadId);
        $files = $this->storage->files($path);

        if(count($files) === 0) {
            throw new RuntimeException("The upload with ID {$uploadId} has no files.");
        }

        $file  = $files[0];

        if (!$this->ready($owner, $uploadId)) {
            return Str::of($file)->beforeLast('.')->afterLast('.');
        }

        return $useMetadata
                ? pathinfo($file, PATHINFO_EXTENSION)
                : Str::afterLast($file, '.');
    }
}
