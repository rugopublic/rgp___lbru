<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

final class SecureProductFiles extends Command
{
    protected $signature = 'products:secure-files {--move : Copy legacy public files to private storage and remove the public source after success}';
    protected $description = 'Moves legacy product files from the public disk into private storage.';

    /**
     * Initializes the secure product files instance and wires its required dependencies.
     *
     * @param ProductRepositoryInterface $products
     * @return void
     */
    public function __construct(private readonly ProductRepositoryInterface $products)
    {
        parent::__construct();
    }

    /**
     * Executes the Artisan command and its associated side effects.
     * @return int
     */
    public function handle(): int
    {
        $public = Storage::disk('public');
        $private = Storage::disk((string) config('files.product_disk', 'local'));
        $moved = 0;
        $skipped = 0;
        $missing = 0;
        $failed = 0;

        foreach ($this->products->allWithFiles() as $product) {
            $raw = (string) $product->file;
            $basename = basename($raw);
            $target = $product->mediaFolder() . '/' . $basename;

            if ($private->exists($target)) {
                if ($raw !== $basename) {
                    $this->products->update($product, ['file' => $basename]);
                }
                if ($this->option('move')) {
                    foreach (array_unique([$raw, $product->mediaFolder().'/'.$basename]) as $legacyPath) {
                        if ($public->exists($legacyPath)) $public->delete($legacyPath);
                    }
                }
                $skipped++;
                continue;
            }

            $source = collect(array_unique([$raw, $product->mediaFolder().'/'.$basename]))
                ->first(fn (string $path): bool => $public->exists($path));

            if ($source === null) {
                $missing++;
                $this->warn("#{$product->id}: public source not found ({$raw})");
                continue;
            }

            $stream = $public->readStream($source);
            if ($stream === null) {
                $failed++;
                $this->error("#{$product->id}: cannot read {$source}");
                continue;
            }

            try {
                $stored = $private->put($target, $stream);
            } finally {
                fclose($stream);
            }

            if (!$stored) {
                $failed++;
                $this->error("#{$product->id}: cannot write {$target}");
                continue;
            }

            $this->products->update($product, ['file' => $basename]);
            $moved++;

            if ($this->option('move')) {
                $public->delete($source);
            }

            $this->line(($this->option('move') ? 'Moved' : 'Prepared') . ": #{$product->id} {$source} -> {$target}");
        }

        $this->newLine();
        $this->info("Processed: {$moved} | already private: {$skipped} | missing: {$missing} | failed: {$failed}");
        if (!$this->option('move')) {
            $this->comment('Dry run only. Re-run with --move during deployment to remove legacy public copies.');
        }

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
