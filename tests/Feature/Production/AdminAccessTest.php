<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('denies the administration area to an administrator whose account is pending deletion', function (): void {
    $admin = User::factory()->create(['is_admin' => true]);
    $admin->delete();

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertRedirect(route('user.delete.pending'));
});

it('continues to deny the administration area to non-administrators', function (): void {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});
