<?php

declare(strict_types=1);

use App\Models\Product;
use App\Observers\ContentMediaObserver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

it('deletes replaced media using paths captured before after-commit callbacks', function (): void {
    Storage::fake('public');
    Storage::fake('local');

    $folder = 'products/public-test-id';
    Storage::disk('local')->put($folder.'/old.pdf', 'old file');
    Storage::disk('local')->put($folder.'/new.pdf', 'new file');
    Storage::disk('public')->put($folder.'/old.jpg', 'old cover');
    Storage::disk('public')->put($folder.'/new.jpg', 'new cover');

    $product = new Product();
    $product->setRawAttributes([
        'public_id' => 'public-test-id',
        'file' => 'old.pdf',
        'cover' => 'old.jpg',
    ], true);
    $product->setAttribute('file', 'new.pdf');
    $product->setAttribute('cover', 'new.jpg');
    $product->syncChanges();

    // Simulate Laravel's post-save original-state synchronization before the
    // transaction's after-commit callback executes.
    DB::shouldReceive('afterCommit')
        ->once()
        ->andReturnUsing(function (Closure $callback) use ($product): void {
            $product->syncOriginal();
            $callback();
        });

    app(ContentMediaObserver::class)->updated($product);

    Storage::disk('local')->assertMissing($folder.'/old.pdf');
    Storage::disk('local')->assertExists($folder.'/new.pdf');
    Storage::disk('public')->assertMissing($folder.'/old.jpg');
    Storage::disk('public')->assertExists($folder.'/new.jpg');
});
