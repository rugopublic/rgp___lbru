<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('keeps the default chunk count aligned with the maximum aggregate upload size', function (): void {
    expect(config('uploads.max_chunks'))->toBe(100);
});

it('rejects excessive chunk counts before writing a temporary upload', function (): void {
    Storage::fake('local');
    config([
        'filesystems.default' => 'local',
        'files.temp_folder' => 'temp',
        'uploads.max_chunks' => 32,
    ]);

    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user)
        ->postJson(route('upload'), [
            'file' => UploadedFile::fake()->create('chunk.pdf', 10, 'application/pdf'),
            'chunk_number' => 1,
            'chunks_total' => 33,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('chunks_total');

    expect(Storage::disk('local')->allFiles('temp'))->toBeEmpty();
});

it('accepts a chunk count within the configured limit', function (): void {
    Storage::fake('local');
    config([
        'filesystems.default' => 'local',
        'files.temp_folder' => 'temp',
        'uploads.max_chunks' => 32,
    ]);

    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)
        ->postJson(route('upload'), [
            'file' => UploadedFile::fake()->create('chunk.pdf', 10, 'application/pdf'),
            'chunk_number' => 1,
            'chunks_total' => 32,
        ]);

    $response->assertOk()
        ->assertJsonPath('result.partial', true)
        ->assertJsonPath('result.success', true);

    expect(Storage::disk('local')->allFiles('temp'))->toHaveCount(1);
});
