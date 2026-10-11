<?php

declare(strict_types=1);

use App\Models\LegalAcceptance;
use App\Models\Register\RegisterEmailToken;
use App\Models\User;
use App\Repositories\Contracts\LegalAcceptanceRepositoryInterface;
use App\Repositories\Contracts\RegisterEmailTokenRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Register\RegisterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;

uses(RefreshDatabase::class);

it('does not log a submitted registration token when lookup fails', function (): void {
    $rawToken = 'registration-bearer-token-that-must-not-be-logged';

    $tokens = Mockery::mock(RegisterEmailTokenRepositoryInterface::class);
    $tokens->shouldReceive('findByToken')->once()->with($rawToken)->andReturn(null);

    Log::shouldReceive('warning')
        ->once()
        ->with('Registration verification token was not found.');

    $service = new RegisterService(
        Mockery::mock(UserRepositoryInterface::class),
        $tokens,
        Mockery::mock(LegalAcceptanceRepositoryInterface::class),
    );

    expect($service->validate($rawToken))->toBeFalse();
});

it('does not log token material when registration token persistence fails', function (): void {
    $sentinel = 'token-secret-in-exception-message';
    $email = 'signup@example.test';

    $users = Mockery::mock(UserRepositoryInterface::class);
    $users->shouldReceive('findByEmail')->once()->with($email)->andReturn(null);

    $tokens = Mockery::mock(RegisterEmailTokenRepositoryInterface::class);
    $tokens->shouldReceive('deleteByEmail')->once()->with($email);
    $tokens->shouldReceive('create')
        ->once()
        ->andThrow(new RuntimeException('insert failed with '.$sentinel));

    Log::shouldReceive('error')
        ->once()
        ->with('Failed to initialize the registration token', Mockery::on(
            fn (array $context): bool => !array_key_exists('token', $context)
                && !str_contains(json_encode($context) ?: '', $sentinel),
        ));

    $service = new RegisterService(
        $users,
        $tokens,
        Mockery::mock(LegalAcceptanceRepositoryInterface::class),
    );

    expect($service->send($email))->toBeFalse();
});

it('does not create a registration when its token cannot be consumed', function (): void {
    $rawToken = 'single-use-registration-token-secret';
    $emailToken = new RegisterEmailToken([
        'email' => 'registered@example.test',
        'token' => $rawToken,
        'created_at' => now(),
        'agreement_version' => 'terms-v1',
        'privacy_version' => 'privacy-v1',
        'agreement_accepted_at' => now(),
    ]);

    $tokens = Mockery::mock(RegisterEmailTokenRepositoryInterface::class);
    $tokens->shouldReceive('findByTokenForUpdate')
        ->once()
        ->with($rawToken)
        ->andReturn($emailToken);
    $tokens->shouldReceive('delete')->once()->with($emailToken)->andReturn(false);

    $createdUser = new User();
    $createdUser->setAttribute('id', 704);

    $users = Mockery::mock(UserRepositoryInterface::class);
    $users->shouldReceive('create')
        ->once()
        ->andReturn($createdUser);

    $legal = Mockery::mock(LegalAcceptanceRepositoryInterface::class);
    $legal->shouldReceive('create')
        ->once()
        ->andReturn(new LegalAcceptance());

    Log::shouldReceive('error')
        ->once()
        ->with('Failed to finalize registration', Mockery::on(
            fn (array $context): bool => !array_key_exists('token', $context)
                && !str_contains(json_encode($context) ?: '', $rawToken),
        ));

    $service = new RegisterService($users, $tokens, $legal);

    expect($service->finalize($rawToken, 'A-valid-registration-password-123'))->toBeFalse();
});
