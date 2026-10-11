<?php

declare(strict_types=1);

use App\Models\User;
use App\Notifications\User\Delete\GraceImpending;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

it('links the impending-deletion email to the real pending page', function (): void {
    expect(Route::has('user.delete.pending'))->toBeTrue();
    expect(Route::has('user.restore'))->toBeFalse();

    $user = User::factory()->make(['email' => 'deletion-notice@example.test']);
    $mail = (new GraceImpending(3))->toMail($user);

    expect($mail->actionUrl)->toBe(route('user.delete.pending'));
});
