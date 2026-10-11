<?php

declare(strict_types=1);

use App\Models\Auth\PasswordResetToken;
use App\Models\User;
use App\Services\Auth\AuthPasswordResetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('consumes a password reset token and changes the password atomically', function (): void {
    $user = User::factory()->create();
    $tokenValue = str_repeat('a', 64);

    PasswordResetToken::query()->create([
        'email' => $user->email,
        'token' => $tokenValue,
        'created_at' => now(),
    ]);

    $service = app(AuthPasswordResetService::class);

    expect($service->reset($tokenValue, 'a-new-test-password'))
        ->toBeTrue();

    expect(Hash::check('a-new-test-password', $user->fresh()->password))
        ->toBeTrue();

    expect(PasswordResetToken::query()->where('token', $tokenValue)->exists())
        ->toBeFalse();

    expect($service->reset($tokenValue, 'another-test-password'))
        ->toBeFalse();
});

it('does not consume or change a password through an expired reset token', function (): void {
    $user = User::factory()->create();
    $oldPassword = $user->password;
    $tokenValue = str_repeat('b', 64);

    PasswordResetToken::query()->create([
        'email' => $user->email,
        'token' => $tokenValue,
        'created_at' => now()->subSeconds((int) config('auth.password_reset.token_ttl', 86400) + 5),
    ]);

    expect(app(AuthPasswordResetService::class)->reset($tokenValue, 'a-new-test-password'))
        ->toBeFalse();

    expect($user->fresh()->password)->toBe($oldPassword);
    expect(PasswordResetToken::query()->where('token', $tokenValue)->exists())
        ->toBeTrue();
});
