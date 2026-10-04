<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Models\User;
use Illuminate\Support\Collection;

interface PublicAccountRepositoryInterface
{

    /**
     * Queries or persists user for the public account repository interface repository.
     *
     * @param int $userId
     * @return User
     */
    public function user(int $userId): User;

    /**
     * Queries or persists enrollments for the public account repository interface repository.
     *
     * @param int $userId
     * @param int $limit
     * @return Collection
     */
    public function enrollments(int $userId, int $limit = 8): Collection;

    /**
     * Queries or persists purchases for the public account repository interface repository.
     *
     * @param int $userId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function purchases(int $userId, int $perPage = 18): LengthAwarePaginator;

    /**
     * Queries or persists saved for the public account repository interface repository.
     *
     * @param int $userId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function saved(int $userId, int $perPage = 18): LengthAwarePaginator;

    /**
     * Queries or persists comments for the public account repository interface repository.
     *
     * @param int $userId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function comments(int $userId, int $perPage = 18): LengthAwarePaginator;
}
