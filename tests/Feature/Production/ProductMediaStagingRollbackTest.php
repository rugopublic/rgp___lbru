<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\User;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\SeoRepositoryInterface;
use App\Services\Admin\Content\ContentMediaService;
use App\Services\Admin\Content\ProductService;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

it('cleans an already-placed product file when the next media placement fails', function (): void {
    Storage::fake('local');
    config(['files.product_disk' => 'local']);

    $owner = new User();
    $owner->setAttribute('id', 41);

    $product = new Product();
    $product->setAttribute('public_id', 'product-staging-test');
    $storedPath = 'products/product-staging-test/first-file.pdf';

    $media = Mockery::mock(ContentMediaService::class);
    $media->shouldReceive('placeUpload')
        ->once()
        ->with($owner, $product, '11111111-1111-4111-8111-111111111111', 'file')
        ->andReturnUsing(function () use ($storedPath): string {
            Storage::disk('local')->put($storedPath, 'file bytes');
            return $storedPath;
        });
    $media->shouldReceive('diskForSlot')->once()->with('file')->andReturn('local');
    $media->shouldReceive('placeUpload')
        ->once()
        ->with($owner, $product, '22222222-2222-4222-8222-222222222222', 'cover')
        ->andThrow(new RuntimeException('Cover placement failed.'));

    $service = new ProductService(
        Mockery::mock(ProductRepositoryInterface::class),
        Mockery::mock(SeoRepositoryInterface::class),
        $media,
    );

    expect(fn () => $service->update($product, $owner, [
        'file' => '11111111-1111-4111-8111-111111111111',
        'cover' => '22222222-2222-4222-8222-222222222222',
    ]))->toThrow(RuntimeException::class, 'Cover placement failed.');

    Storage::disk('local')->assertMissing($storedPath);
});
