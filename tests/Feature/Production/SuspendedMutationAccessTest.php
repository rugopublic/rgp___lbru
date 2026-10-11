<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('blocks temporary file uploads for accounts pending deletion', function (): void {
    $user = User::factory()->create();
    $user->delete();

    $this->actingAs($user)
        ->post(route('upload'))
        ->assertRedirect(route('user.delete.pending'));
});

it('blocks content interactions for accounts pending deletion', function (): void {
    $user = User::factory()->create();
    $user->delete();

    $this->actingAs($user)
        ->post(route('content.like', ['type' => 'article', 'id' => 'missing-article']))
        ->assertRedirect(route('user.delete.pending'));
});
