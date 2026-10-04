<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

test('creating a user resolves PrepareCreation through the container', function (): void {
    $user = User::factory()->create();

    expect($user->public_id)->toBeString()->not->toBeEmpty();
});
