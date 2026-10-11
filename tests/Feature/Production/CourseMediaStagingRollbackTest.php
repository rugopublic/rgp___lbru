<?php

declare(strict_types=1);

use App\Models\Course;
use App\Models\User;
use App\Repositories\Contracts\CourseRepositoryInterface;
use App\Repositories\Contracts\SeoRepositoryInterface;
use App\Services\Admin\Content\ContentMediaService;
use App\Services\Admin\Content\RichTextMediaService;
use App\Services\Admin\Course\CourseService;
use App\Services\Admin\Generic\FileService;
use Illuminate\Support\Facades\Storage;

it('cleans course inline media staged in one field when a later field fails', function (): void {
    Storage::fake('public');

    $owner = new User();
    $owner->setAttribute('id', 43);

    $course = new Course();
    $course->setAttribute('public_id', 'course-staging-test');

    $uploadId = '44444444-4444-4444-8444-444444444444';
    $placedPath = null;

    $files = Mockery::mock(FileService::class);
    $files->shouldReceive('ready')->once()->with($owner, $uploadId)->andReturn(true);
    $files->shouldReceive('extension')->once()->with($owner, $uploadId, true)->andReturn('jpg');
    $files->shouldReceive('mimeType')->once()->with($owner, $uploadId)->andReturn('image/jpeg');
    $files->shouldReceive('size')->once()->with($owner, $uploadId)->andReturn(128);
    $files->shouldReceive('place')
        ->once()
        ->with($owner, $uploadId, 'courses/course-staging-test/body', Mockery::type('string'), true, 'public')
        ->andReturnUsing(function ($owner, $id, $folder, $name) use (&$placedPath): array {
            $placedPath = $folder.'/'.$name.'.jpg';
            Storage::disk('public')->put($placedPath, 'image bytes');

            return ['success' => true, 'extension' => 'jpg'];
        });

    $service = new CourseService(
        Mockery::mock(CourseRepositoryInterface::class),
        Mockery::mock(SeoRepositoryInterface::class),
        new ContentMediaService($files),
        new RichTextMediaService($files),
    );

    expect(fn () => $service->update($course, $owner, collect([
        'pitch' => '<p><img data-upload-id="'.$uploadId.'"></p>',
        'objective' => '<p><img data-upload-id="not-a-uuid"></p>',
    ])))->toThrow(RuntimeException::class, 'Некорректный идентификатор inline-изображения.');

    expect($placedPath)->toBeString();
    Storage::disk('public')->assertMissing($placedPath);
});
