<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Models\User\UserAccount;

interface UserRepositoryInterface
{

    /**
     * Queries and paginates for admin for the requested context.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginateForAdmin(array $filters = [], int $perPage = 25): LengthAwarePaginator;

    /**
     * Queries for admin and returns the matching persistence model.
     *
     * @param int|string $id
     * @return User
     */
    public function findForAdmin(int|string $id): User;

    /**
     * Queries by id and returns the matching persistence model.
     *
     * @param int $id
     * @return User
     */
    public function findById(int $id): User;

    /**
     * Queries by id for update and returns the matching persistence model.
     *
     * @param int $id
     * @return User
     */
    public function findByIdForUpdate(int $id): User;

    /**
     * Queries by email and returns the matching persistence model.
     *
     * @param string $email
     * @param bool $withTrashed
     * @return ?User
     */
    public function findByEmail(string $email, bool $withTrashed = false): ?User;

    /**
     * Queries or persists public id exists for the user repository interface repository.
     *
     * @param string $publicId
     * @return bool
     */
    public function publicIdExists(string $publicId): bool;

    /**
     * Queries or persists deletion grace impending for the user repository interface repository.
     *
     * @param ?int $daysLeft
     * @param bool $exact
     * @return \Illuminate\Support\Collection
     */
    public function deletionGraceImpending(?int $daysLeft = null, bool $exact = false): \Illuminate\Support\Collection;

    /**
     * Persists a new record through the repository model.
     *
     * @param array $attributes
     * @return User
     */
    public function create(array $attributes): User;

    /**
     * Persists changes to the existing model through the repository.
     *
     * @param User $user
     * @param array $attributes
     * @return bool
     */
    public function update(User $user, array $attributes): bool;

    /**
     * Soft-deletes the supplied model through the repository.
     *
     * @param User $user
     * @return bool
     */
    public function delete(User $user): bool;

    /**
     * Restores the supplied soft-deleted model through the repository.
     *
     * @param User $user
     * @return bool
     */
    public function restore(User $user): bool;

    /**
     * Queries or persists force delete for the user repository interface repository.
     *
     * @param User $user
     * @return bool
     */
    public function forceDelete(User $user): bool;

    /**
     * Synchronizes groups through the repository relationship.
     *
     * @param User $user
     * @param array $groupIds
     * @return void
     */
    public function syncGroups(User $user, array $groupIds): void;

    /**
     * Queries or persists account exists for the user repository interface repository.
     *
     * @param User $user
     * @return bool
     */
    public function accountExists(User $user): bool;

    /**
     * Queries or persists create account for the user repository interface repository.
     *
     * @param User $user
     * @param array $attributes
     * @return UserAccount
     */
    public function createAccount(User $user, array $attributes): UserAccount;

    /**
     * Queries or persists update account for the user repository interface repository.
     *
     * @param User $user
     * @param array $attributes
     * @return bool
     */
    public function updateAccount(User $user, array $attributes): bool;
}
