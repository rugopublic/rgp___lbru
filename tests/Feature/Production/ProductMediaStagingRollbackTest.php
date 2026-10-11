<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\User;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\SeoRepositoryInterface;
use App\Services\Admin\Content\ContentMediaService;
use App\Services\Admin\Content\ProductService;
use App\Services\Admin\Generic\FileService;
use Illuminate\Support\Facades\Storage;

it('cleans an already-placed product file when the next media placement fails', function (): void {
    Storage::fake('local');
    config(['files.product_disk' => 'local']);

    $owner = new User();
    $owner->setAttribute('id', 41);

    $product = new Product();
    $product->setAttribute('public_id', 'product-staging-test');
    $storedPath = null;
    $firstUpload = '11111111-1111-4111-8111-111111111111';
    $secondUpload = '22222222-2222-4222-8222-222222222222';

    $files = Mockery::mock(FileService::class);
    $files->shouldReceive('ready')->once()->with($owner, $firstUpload)->andReturn(true);
    $files->shouldReceive('extension')->once()->with($owner, $firstUpload, true)->andReturn('pdf');
    $files->shouldReceive('mimeType')->once()->with($owner, $firstUpload)->andReturn('application/pdf');
    $files->shouldReceive('size')->once()->with($owner, $firstUpload)->andReturn(128);
    $files->shouldReceive('place')
        ->once()
        ->with($owner, $firstUpload, 'products/product-staging-test', Mockery::type('string'), true, 'local')
        ->andReturnUsing(function ($owner, $uploadId, $folder, $name) use (&$storedPath): array {
            $storedPath = $folder.'/'.$name.'.pdf';
            Storage::disk('local')->put($storedPath, 'file bytes');

            return ['success' => true, 'extension' => 'pdf'];
        });

    // The first upload succeeds. The second one fails before it can be placed.
    $files->shouldReceive('ready')->once()->with($owner, $secondUpload)->andReturn(false);

    $media = new ContentMediaService($files);
    $service = new ProductService(
        Mockery::mock(ProductRepositoryInterface::class),
        Mockery::mock(SeoRepositoryInterface::class),
        $media,
    );

    expect(fn () => $service->update($product, $owner, [
        'file' => $firstUpload,
        'cover' => $secondUpload,
    ]))->toThrow(RuntimeException::class, 'Загрузка ещё не готова к размещению.');

    expect($storedPath)->toBeString();
    Storage::disk('local')->assertMissing($storedPath);
});
