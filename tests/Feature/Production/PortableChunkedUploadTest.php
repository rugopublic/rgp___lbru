<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\Admin\Generic\FileService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('merges chunked uploads through filesystem streams and preserves chunk order', function (): void {
    Storage::fake('local');
    config([
        'filesystems.default' => 'local',
        'files.temp_folder' => 'temp',
    ]);

    $owner = new User();
    $owner->setAttribute('id', 7391);
    $uploadId = 'f56f7a10-8c42-4d47-91e9-96a09fb8d9ca';

    $files = app(FileService::class);

    $first = $files->upload(
        UploadedFile::fake()->createWithContent('lesson.pdf', 'first-'),
        $owner,
        $uploadId,
        1,
        2,
    );

    expect($first)->toBe(['partial' => true, 'success' => true]);

    $second = $files->upload(
        UploadedFile::fake()->createWithContent('lesson.pdf', 'second'),
        $owner,
        $uploadId,
        2,
        2,
    );

    expect($second)->toBe(['partial' => false, 'success' => true]);

    $disk = Storage::disk('local');
    $filesAfterMerge = $disk->allFiles("temp/{$owner->id}");

    expect($filesAfterMerge)->toHaveCount(1);
    expect($disk->get($filesAfterMerge[0]))->toBe('first-second');
});

it('does not merge chunk sets with inconsistent original file names', function (): void {
    Storage::fake('local');
    config([
        'filesystems.default' => 'local',
        'files.temp_folder' => 'temp',
    ]);

    $owner = new User();
    $owner->setAttribute('id', 7392);
    $uploadId = '1c1866fa-1b92-4c33-8e8c-86ce3ea65a02';

    $files = app(FileService::class);

    $files->upload(
        UploadedFile::fake()->createWithContent('lesson.pdf', 'first-'),
        $owner,
        $uploadId,
        1,
        2,
    );

    $second = $files->upload(
        UploadedFile::fake()->createWithContent('different.png', 'second'),
        $owner,
        $uploadId,
        2,
        2,
    );

    expect($second)->toBe(['partial' => true, 'success' => true]);
    expect($files->ready($owner, $uploadId))->toBeFalse();
});
