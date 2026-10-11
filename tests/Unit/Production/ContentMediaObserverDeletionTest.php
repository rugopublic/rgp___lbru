<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\Recipe;
use App\Models\RecipeStep;
use App\Observers\ContentMediaObserver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

it('removes nested media after hard-deleting a recipe step', function (): void {
    Storage::fake('public');

    $recipe = new Recipe();
    $recipe->setAttribute('public_id', 'recipe-test-id');

    $step = new RecipeStep();
    $step->setAttribute('id', 42);
    $step->setRelation('recipe', $recipe);

    $folder = $step->mediaFolder();
    Storage::disk('public')->put($folder.'/image.jpg', 'image bytes');

    DB::shouldReceive('afterCommit')
        ->once()
        ->andReturnUsing(static function (Closure $callback): void {
            $callback();
        });

    app(ContentMediaObserver::class)->deleted($step);

    Storage::disk('public')->assertMissing($folder.'/image.jpg');
});

it('preserves media when a soft-deletable content model is only soft-deleted', function (): void {
    Storage::fake('public');

    $product = new Product();
    $product->setRawAttributes([
        'public_id' => 'product-test-id',
        'deleted_at' => now(),
    ], true);

    $folder = $product->mediaFolder();
    Storage::disk('public')->put($folder.'/cover.jpg', 'cover bytes');

    DB::shouldReceive('afterCommit')->never();

    app(ContentMediaObserver::class)->deleted($product);

    Storage::disk('public')->assertExists($folder.'/cover.jpg');
});
