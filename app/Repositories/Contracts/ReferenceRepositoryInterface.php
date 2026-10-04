<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface ReferenceRepositoryInterface
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
     * Queries for edit and returns the matching persistence model.
     *
     * @param string $publicId
     * @return Model
     */
    public function findForEdit(string $publicId): Model;

    /**
     * Returns lightweight selector options from the repository.
     *
     * @param ?string $query
     * @param ?string $id
     * @param int $limit
     * @return Collection
     */
    public function options(?string $query = null, ?string $id = null, int $limit = 20): Collection;

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
     * @param Model $item
     * @param array $attributes
     * @return bool
     */
    public function update(Model $item, array $attributes): bool;

    /**
     * Synchronizes products through the repository relationship.
     *
     * @param Model $item
     * @param array $ids
     * @return void
     */
    public function syncProducts(Model $item, array $ids): void;

    /**
     * Soft-deletes the supplied model through the repository.
     *
     * @param Model $item
     * @return bool
     */
    public function delete(Model $item): bool;

    /**
     * Restores the supplied soft-deleted model through the repository.
     *
     * @param Model $item
     * @return bool
     */
    public function restore(Model $item): bool;

    /**
     * Queries visible and returns the matching persistence model.
     *
     * @param string $publicId
     * @return ?Model
     */
    public function findVisible(string $publicId): ?Model;

    /**
     * Queries and paginates visible for the requested context.
     *
     * @param int $perPage
     * @return mixed
     */
    public function paginateVisible(int $perPage = 12);

    /**
     * Queries or persists search visible for the reference repository interface repository.
     *
     * @param string $query
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function searchVisible(string $query, int $limit = 8): \Illuminate\Support\Collection;
}
