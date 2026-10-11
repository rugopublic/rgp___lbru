<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\Admin\Generic\FileService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('cleans temporary uploads from the same configured disk and root used for uploads', function (): void {
    config([
        'filesystems.default' => 'local',
        'files.temp_disk' => 's3',
        'files.temp_folder' => 'custom-upload-root',
        'files.temp_path' => 'legacy-wrong-root',
    ]);

    Storage::fake('local');
    config([
        'filesystems.disks.s3' => [
            'driver' => 'local',
            'root' => storage_path('framework/testing/disks/temp-upload-s3'),
            'throw' => false,
            'report' => false,
        ],
    ]);
    Storage::forgetDisk('s3');

    $owner = new User();
    $owner->setAttribute('id', 7391);
    $uploadId = 'f56f7a10-8c42-4d47-91e9-96a09fb8d9ca';

    $uploads = app(FileService::class);
    $result = $uploads->upload(
        UploadedFile::fake()->createWithContent('payload.txt', 'temporary upload contents'),
        $owner,
        $uploadId,
    );

    expect($result)->toBe(['partial' => false, 'success' => true]);

    $files = Storage::disk('s3')->allFiles('custom-upload-root/7391');
    expect($files)->toHaveCount(1);

    $relativePath = $files[0];
    $absolutePath = Storage::disk('s3')->path($relativePath);
    touch($absolutePath, now()->subHours(48)->getTimestamp());

    $this->artisan('clean:temp-uploads', [
        '--dry-run' => true,
        '--hours' => 24,
    ])
        ->expectsOutputToContain('[DRY-RUN] Would delete: '.$relativePath)
        ->assertExitCode(0);
});
