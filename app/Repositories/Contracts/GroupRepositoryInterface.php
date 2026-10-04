<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface GroupRepositoryInterface
{

    /**
     * Queries and paginates for admin for the requested context.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginateForAdmin(array $filters = [], int $perPage = 20): LengthAwarePaginator;

    /**
     * Returns lightweight selector options from the repository.
     *
     * @param ?string $query
     * @param int $limit
     * @return Collection
     */
    public function options(?string $query = null, int $limit = 20): Collection;

    /**
     * Persists a new record through the repository model.
     *
     * @param array $attributes
     * @return Model
     */
    public function create(array $attributes): Model;

    /**
     * Persists changes to the existing model through the repository.
     *
     * @param Model $group
     * @param array $attributes
     * @return bool
     */
    public function update(Model $group, array $attributes): bool;

    /**
     * Soft-deletes the supplied model through the repository.
     *
     * @param Model $group
     * @return bool
     */
    public function delete(Model $group): bool;

    /**
     * Restores the supplied soft-deleted model through the repository.
     *
     * @param Model $group
     * @return bool
     */
    public function restore(Model $group): bool;

    /**
     * Queries by public id and returns the matching persistence model.
     *
     * @param string $publicId
     * @param bool $withTrashed
     * @return ?Model
     */
    public function findByPublicId(string $publicId, bool $withTrashed = true): ?Model;
}
