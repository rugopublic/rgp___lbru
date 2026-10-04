<?php

declare(strict_types=1);
namespace App\Repositories\Contracts;
use App\Models\User\UserEmailUpdate;
use Illuminate\Support\Collection;
interface UserEmailUpdateRepositoryInterface
{

    /**
     * Queries by token and returns the matching persistence model.
     *
     * @param string $token
     * @return ?UserEmailUpdate
     */
    public function findByToken(string $token): ?UserEmailUpdate;

    /**
     * Queries for user and returns the matching persistence model.
     *
     * @param int $userId
     * @return ?UserEmailUpdate
     */
    public function findForUser(int $userId): ?UserEmailUpdate;

    /**
     * Persists a new record through the repository model.
     *
     * @param array $attributes
     * @return UserEmailUpdate
     */
    public function create(array $attributes): UserEmailUpdate;

    /**
     * Persists changes to the existing model through the repository.
     *
     * @param UserEmailUpdate $update
     * @param array $attributes
     * @return bool
     */
    public function update(UserEmailUpdate $update, array $attributes): bool;

    /**
     * Soft-deletes the supplied model through the repository.
     *
     * @param UserEmailUpdate $update
     * @return bool
     */
    public function delete(UserEmailUpdate $update): bool;
}
