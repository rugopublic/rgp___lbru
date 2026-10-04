<?php

declare(strict_types=1);
namespace App\Repositories\Eloquent;
use App\Models\User\UserEmailUpdate;
use App\Repositories\Contracts\UserEmailUpdateRepositoryInterface;
final class UserEmailUpdateRepository implements UserEmailUpdateRepositoryInterface
{

    /**
     * Queries by token and returns the matching persistence model.
     *
     * @param string $token
     * @return ?UserEmailUpdate
     */
    public function findByToken(string $token): ?UserEmailUpdate
    {
        return UserEmailUpdate::query()->with('user')->where('token', $token)->first();
    }

    /**
     * Queries for user and returns the matching persistence model.
     *
     * @param int $userId
     * @return ?UserEmailUpdate
     */
    public function findForUser(int $userId): ?UserEmailUpdate
    {
        return UserEmailUpdate::query()->whereKey($userId)->first();
    }

    /**
     * Persists a new record through the repository model.
     *
     * @param array $attributes
     * @return UserEmailUpdate
     */
    public function create(array $attributes): UserEmailUpdate
    {
        return UserEmailUpdate::create($attributes);
    }

    /**
     * Persists changes to the existing model through the repository.
     *
     * @param UserEmailUpdate $update
     * @param array $attributes
     * @return bool
     */
    public function update(UserEmailUpdate $update, array $attributes): bool
    {
        return $update->fill($attributes)->save();
    }

    /**
     * Soft-deletes the supplied model through the repository.
     *
     * @param UserEmailUpdate $update
     * @return bool
     */
    public function delete(UserEmailUpdate $update): bool
    {
        return (bool) $update->delete();
    }
}
