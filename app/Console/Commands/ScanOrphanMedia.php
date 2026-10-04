<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Repositories\Contracts\ContentMediaOwnershipRepositoryInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Throwable;

final class ScanOrphanMedia extends Command
{
    protected $signature = 'media:scan-orphans {--disk=public : Filesystem disk to scan} {--delete : Delete folders older than the safety window} {--hours=24 : Minimum folder age before deletion}';
    protected $description = 'Scan content media for folders that have no database owner; deletion is opt-in.';

    /**
     * Executes the Artisan command and its associated side effects.
     *
     * @param ContentMediaOwnershipRepositoryInterface $owners
     * @return int
     */
    public function handle(ContentMediaOwnershipRepositoryInterface $owners): int
    {
        $disk = Storage::disk((string) $this->option('disk'));
        $roots = ['articles','products','reference','recipes','courses','teachers'];
        $expected = array_fill_keys(iterator_to_array($owners->expectedFolders()), true);
        $orphans = [];
        foreach ($roots as $root) {
            if (!$disk->exists($root)) continue;
            foreach ($disk->directories($root) as $directory) {
                if (!isset($expected[$directory])) $orphans[] = $directory;
            }
        }

        $delete = (bool) $this->option('delete');
        $hours = max(1, (int) $this->option('hours'));
        $threshold = Carbon::now()->subHours($hours)->getTimestamp();
        $deleted = 0;

        foreach ($orphans as $directory) {
            $files = $disk->allFiles($directory);
            $modified = 0;
            foreach ($files as $file) {
                $modified = max($modified, $disk->lastModified($file));
            }
            if ($modified === 0) {
                $this->line('[ORPHAN/EMPTY-SKIP] '.$directory);
                continue;
            }
            $this->line(($delete && $modified < $threshold ? '[ORPHAN/DELETE]' : '[ORPHAN]').' '.$directory);
            if ($delete && $modified < $threshold) {
                try {
                    $disk->deleteDirectory($directory);
                    $deleted++;
                } catch (Throwable $e) {
                    $this->error($e->getMessage());
                    return self::FAILURE;
                }
            }
        }

        $this->info(sprintf('Найдено orphan-папок: %d; удалено: %d.', count($orphans), $deleted));
        return self::SUCCESS;
    }
}
