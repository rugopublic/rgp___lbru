<?php

declare(strict_types=1);

namespace App\Services\Admin\User;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class AdminUserService
{

    /**
     * Initializes the admin user service instance and wires its required dependencies.
     *
     * @param UserRepositoryInterface $users
     * @return void
     */
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {
    }

    /**
     * Creates the domain resource and applies the required business orchestration.
     *
     * @param array $data
     * @return User
     */
    public function create(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            $groupIds = array_values(array_map('intval', $data['group_ids'] ?? []));
            unset($data['group_ids']);

            $name = (string) ($data['name'] ?? '');
            unset($data['name']);

            $data['password'] = Hash::make((string) $data['password']);
            $data['email_verified_at'] = now();

            $user = $this->users->create($data);
            $this->users->updateAccount($user, ['name' => $name]);
            $this->users->syncGroups($user, $groupIds);

            return $user;
        });
    }

    /**
     * Updates the domain resource and applies the required business orchestration.
     *
     * @param User $user
     * @param array $data
     * @return void
     */
    public function update(User $user, array $data): void
    {
        DB::transaction(function () use ($user, $data): void {
            $groupIds = array_key_exists('group_ids', $data)
                ? array_values(array_map('intval', $data['group_ids'] ?? []))
                : null;
            unset($data['group_ids']);

            $name = $data['name'] ?? null;
            unset($data['name']);

            if (array_key_exists('password', $data)) {
                if ((string) $data['password'] === '') {
                    unset($data['password']);
                } else {
                    $data['password'] = Hash::make((string) $data['password']);
                }
            }

            if ($data !== []) {
                $this->users->update($user, $data);
            }

            if ($name !== null) {
                $this->users->updateAccount($user, ['name' => $name]);
            }

            if ($groupIds !== null) {
                $this->users->syncGroups($user, $groupIds);
            }
        });
    }
}
