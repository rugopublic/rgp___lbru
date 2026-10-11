<?php

declare(strict_types=1);

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Admin\User\AdminUserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;

uses(RefreshDatabase::class);

it('does not write profile or groups when the user record save fails', function (): void {
    $user = new User();
    $user->setAttribute('id', 701);

    $users = Mockery::mock(UserRepositoryInterface::class);
    $users->shouldReceive('update')->once()->with($user, ['email' => 'new@example.test'])->andReturn(false);
    $users->shouldNotReceive('updateAccount');
    $users->shouldNotReceive('syncGroups');

    $service = new AdminUserService($users);

    expect(fn () => $service->update($user, [
        'email' => 'new@example.test',
        'name' => 'New Name',
        'group_ids' => [2],
    ]))->toThrow(RuntimeException::class, 'Пользователь не сохранён.');
});

it('does not sync groups when the profile save fails', function (): void {
    $user = new User();
    $user->setAttribute('id', 702);

    $users = Mockery::mock(UserRepositoryInterface::class);
    $users->shouldReceive('update')->once()->with($user, ['email' => 'new@example.test'])->andReturn(true);
    $users->shouldReceive('updateAccount')->once()->with($user, ['name' => 'New Name'])->andReturn(false);
    $users->shouldNotReceive('syncGroups');

    $service = new AdminUserService($users);

    expect(fn () => $service->update($user, [
        'email' => 'new@example.test',
        'name' => 'New Name',
        'group_ids' => [2],
    ]))->toThrow(RuntimeException::class, 'Профиль пользователя не сохранён.');
});

it('aborts administrator-created accounts if the profile cannot be initialized', function (): void {
    $user = new User();
    $user->setAttribute('id', 703);

    $users = Mockery::mock(UserRepositoryInterface::class);
    $users->shouldReceive('create')->once()->andReturn($user);
    $users->shouldReceive('updateAccount')->once()->with($user, ['name' => 'New Name'])->andReturn(false);
    $users->shouldNotReceive('syncGroups');

    $service = new AdminUserService($users);

    expect(fn () => $service->create([
        'email' => 'created@example.test',
        'password' => 'Safe-test-password-123',
        'name' => 'New Name',
        'group_ids' => [2],
    ]))->toThrow(RuntimeException::class, 'Профиль пользователя не сохранён.');
});
