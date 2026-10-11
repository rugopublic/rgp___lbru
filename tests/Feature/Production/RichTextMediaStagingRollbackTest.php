<?php

declare(strict_types=1);

use App\Models\Article;
use App\Models\User;
use App\Services\Admin\Content\RichTextMediaService;
use App\Services\Admin\Generic\FileService;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

it('removes inline images already placed when a later inline image is invalid', function (): void {
    Storage::fake('public');

    $owner = new User();
    $owner->setAttribute('id', 42);

    $article = new Article();
    $article->setAttribute('public_id', 'article-staging-test');

    $firstUpload = '33333333-3333-4333-8333-333333333333';
    $files = Mockery::mock(FileService::class);
    $files->shouldReceive('ready')->once()->with($owner, $firstUpload)->andReturn(true);
    $files->shouldReceive('extension')->once()->with($owner, $firstUpload, true)->andReturn('jpg');
    $files->shouldReceive('mimeType')->once()->with($owner, $firstUpload)->andReturn('image/jpeg');
    $files->shouldReceive('size')->once()->with($owner, $firstUpload)->andReturn(128);
    $files->shouldReceive('place')
        ->once()
        ->with($owner, $firstUpload, 'articles/article-staging-test/body', Mockery::type('string'), true, 'public')
        ->andReturnUsing(function ($owner, $uploadId, $folder, $name): array {
            Storage::disk('public')->put($folder.'/'.$name.'.jpg', 'image bytes');
            return ['success' => true, 'extension' => 'jpg'];
        });

    $service = new RichTextMediaService($files);
    $html = '<p><img data-upload-id="'.$firstUpload.'"><img data-upload-id="not-a-uuid"></p>';

    expect(fn () => $service->resolvePendingImages($owner, $article, $html))
        ->toThrow(RuntimeException::class, 'Некорректный идентификатор inline-изображения.');

    expect(Storage::disk('public')->allFiles('articles/article-staging-test/body'))->toBeEmpty();
});
