<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\Recipe;
use App\Models\ReferenceItem;
use App\Models\User;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\RecipeRepositoryInterface;
use App\Repositories\Contracts\RecipeStepRepositoryInterface;
use App\Repositories\Contracts\RecipeStructureRepositoryInterface;
use App\Repositories\Contracts\ReferenceRepositoryInterface;
use App\Repositories\Contracts\SeoRepositoryInterface;
use App\Services\Admin\Content\ContentMediaService;
use App\Services\Admin\Content\ProductService;
use App\Services\Admin\Content\RecipeService;
use App\Services\Admin\Content\RecipeStructureService;
use App\Services\Admin\Content\ReferenceService;
use App\Services\Admin\Content\RichTextMediaService;
use App\Services\Admin\Generic\FileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('cleans staged product media and aborts related changes when persistence returns false', function (): void {
    Storage::fake('local');
    config(['files.product_disk' => 'local']);

    $owner = new User();
    $owner->setAttribute('id', 51);
    $product = new Product();
    $product->setAttribute('public_id', 'product-save-failure');

    $uploadId = '55555555-5555-4555-8555-555555555555';
    $storedPath = null;
    $files = Mockery::mock(FileService::class);
    $files->shouldReceive('ready')->once()->with($owner, $uploadId)->andReturn(true);
    $files->shouldReceive('extension')->once()->with($owner, $uploadId, true)->andReturn('pdf');
    $files->shouldReceive('mimeType')->once()->with($owner, $uploadId)->andReturn('application/pdf');
    $files->shouldReceive('size')->once()->with($owner, $uploadId)->andReturn(128);
    $files->shouldReceive('place')
        ->once()
        ->with($owner, $uploadId, 'products/product-save-failure', Mockery::type('string'), true, 'local')
        ->andReturnUsing(function ($owner, $id, $folder, $name) use (&$storedPath): array {
            $storedPath = $folder.'/'.$name.'.pdf';
            Storage::disk('local')->put($storedPath, 'private file');
            return ['success' => true, 'extension' => 'pdf'];
        });

    $repository = Mockery::mock(ProductRepositoryInterface::class);
    $repository->shouldReceive('update')->once()->andReturn(false);
    $repository->shouldNotReceive('syncArticles');
    $repository->shouldNotReceive('syncRecipes');
    $repository->shouldNotReceive('syncReferenceItems');

    $service = new ProductService(
        $repository,
        Mockery::mock(SeoRepositoryInterface::class),
        new ContentMediaService($files),
    );

    expect(fn () => $service->update($product, $owner, ['file' => $uploadId]))
        ->toThrow(RuntimeException::class, 'Товар не сохранён.');

    expect($storedPath)->toBeString();
    Storage::disk('local')->assertMissing($storedPath);
});

it('cleans staged reference media when persistence returns false', function (): void {
    Storage::fake('public');

    $owner = new User();
    $owner->setAttribute('id', 52);
    $item = new ReferenceItem();
    $item->setAttribute('public_id', 'reference-save-failure');

    $uploadId = '66666666-6666-4666-8666-666666666666';
    $storedPath = null;
    $files = Mockery::mock(FileService::class);
    $files->shouldReceive('ready')->once()->with($owner, $uploadId)->andReturn(true);
    $files->shouldReceive('extension')->once()->with($owner, $uploadId, true)->andReturn('jpg');
    $files->shouldReceive('mimeType')->once()->with($owner, $uploadId)->andReturn('image/jpeg');
    $files->shouldReceive('place')
        ->once()
        ->with($owner, $uploadId, 'reference/reference-save-failure', Mockery::type('string'), true, 'public')
        ->andReturnUsing(function ($owner, $id, $folder, $name) use (&$storedPath): array {
            $storedPath = $folder.'/'.$name.'.jpg';
            Storage::disk('public')->put($storedPath, 'cover bytes');
            return ['success' => true, 'extension' => 'jpg'];
        });

    $repository = Mockery::mock(ReferenceRepositoryInterface::class);
    $repository->shouldReceive('update')->once()->andReturn(false);
    $repository->shouldNotReceive('syncProducts');

    $service = new ReferenceService(
        $repository,
        Mockery::mock(SeoRepositoryInterface::class),
        new ContentMediaService($files),
        new RichTextMediaService($files),
    );

    expect(fn () => $service->update($item, $owner, ['cover' => $uploadId]))
        ->toThrow(RuntimeException::class, 'Справочник не сохранён.');

    expect($storedPath)->toBeString();
    Storage::disk('public')->assertMissing($storedPath);
});

it('cleans staged recipe media when persistence returns false', function (): void {
    Storage::fake('public');

    $owner = new User();
    $owner->setAttribute('id', 53);
    $recipe = new Recipe();
    $recipe->setAttribute('public_id', 'recipe-save-failure');

    $uploadId = '77777777-7777-4777-8777-777777777777';
    $storedPath = null;
    $files = Mockery::mock(FileService::class);
    $files->shouldReceive('ready')->once()->with($owner, $uploadId)->andReturn(true);
    $files->shouldReceive('extension')->once()->with($owner, $uploadId, true)->andReturn('jpg');
    $files->shouldReceive('mimeType')->once()->with($owner, $uploadId)->andReturn('image/jpeg');
    $files->shouldReceive('place')
        ->once()
        ->with($owner, $uploadId, 'recipes/recipe-save-failure', Mockery::type('string'), true, 'public')
        ->andReturnUsing(function ($owner, $id, $folder, $name) use (&$storedPath): array {
            $storedPath = $folder.'/'.$name.'.jpg';
            Storage::disk('public')->put($storedPath, 'cover bytes');
            return ['success' => true, 'extension' => 'jpg'];
        });

    $repository = Mockery::mock(RecipeRepositoryInterface::class);
    $repository->shouldReceive('update')->once()->andReturn(false);
    $repository->shouldNotReceive('syncCategories');
    $repository->shouldNotReceive('syncProducts');

    $service = new RecipeService(
        $repository,
        Mockery::mock(RecipeStepRepositoryInterface::class),
        Mockery::mock(SeoRepositoryInterface::class),
        new RichTextMediaService($files),
        new RecipeStructureService(Mockery::mock(RecipeStructureRepositoryInterface::class)),
    );

    expect(fn () => $service->update($recipe, $owner, ['cover' => $uploadId]))
        ->toThrow(RuntimeException::class, 'Рецепт не сохранён.');

    expect($storedPath)->toBeString();
    Storage::disk('public')->assertMissing($storedPath);
});
