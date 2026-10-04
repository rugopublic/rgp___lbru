<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface ProductRepositoryInterface
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
     * @param Model $product
     * @param array $attributes
     * @return bool
     */
    public function update(Model $product, array $attributes): bool;

    /**
     * Synchronizes articles through the repository relationship.
     *
     * @param Model $product
     * @param array $ids
     * @return void
     */
    public function syncArticles(Model $product, array $ids): void;

    /**
     * Synchronizes recipes through the repository relationship.
     *
     * @param Model $product
     * @param array $ids
     * @return void
     */
    public function syncRecipes(Model $product, array $ids): void;

    /**
     * Synchronizes reference items through the repository relationship.
     *
     * @param Model $product
     * @param array $ids
     * @return void
     */
    public function syncReferenceItems(Model $product, array $ids): void;

    /**
     * Soft-deletes the supplied model through the repository.
     *
     * @param Model $product
     * @return bool
     */
    public function delete(Model $product): bool;

    /**
     * Restores the supplied soft-deleted model through the repository.
     *
     * @param Model $product
     * @return bool
     */
    public function restore(Model $product): bool;

    /**
     * Queries sellable and returns the matching persistence model.
     *
     * @param string $publicId
     * @return ?Model
     */
    public function findSellable(string $publicId): ?Model;

    /**
     * Queries for access and returns the matching persistence model.
     *
     * @param string $publicId
     * @return ?Model
     */
    public function findForAccess(string $publicId): ?Model;

    /**
     * Queries or persists all with files for the product repository interface repository.
     * @return Collection
     */
    public function allWithFiles(): Collection;

    /**
     * Queries and paginates sellable for the requested context.
     *
     * @param int $perPage
     * @return mixed
     */
    public function paginateSellable(int $perPage = 12);
}
