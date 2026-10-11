<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\User\UserCookieSettings;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('creating a user resolves PrepareCreation through the container', function (): void {
    Storage::fake('private');

    $user = User::factory()->create();

    expect($user->public_id)->toBeString()->not->toBeEmpty();
    expect(UserCookieSettings::query()->where('user_id', $user->id)->exists())->toBeTrue();
    Storage::disk('private')->assertDirectoryExists($user->folder());
});
