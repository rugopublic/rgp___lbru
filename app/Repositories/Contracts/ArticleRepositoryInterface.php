<?php
declare(strict_types=1);
namespace App\Repositories\Contracts;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
interface ArticleRepositoryInterface
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
     * Persists a new record through the repository model.
     *
     * @param array $attributes
     * @return Model
     */
    public function create(array $attributes): Model;

    /**
     * Persists changes to the existing model through the repository.
     *
     * @param Model $article
     * @param array $attributes
     * @return bool
     */
    public function update(Model $article, array $attributes): bool;

    /**
     * Synchronizes products through the repository relationship.
     *
     * @param Model $article
     * @param array $ids
     * @return void
     */
    public function syncProducts(Model $article, array $ids): void;

    /**
     * Soft-deletes the supplied model through the repository.
     *
     * @param Model $article
     * @return bool
     */
    public function delete(Model $article): bool;

    /**
     * Restores the supplied soft-deleted model through the repository.
     *
     * @param Model $article
     * @return bool
     */
    public function restore(Model $article): bool;

    /**
     * Queries published and returns the matching persistence model.
     *
     * @param string $publicId
     * @return ?Model
     */
    public function findPublished(string $publicId): ?Model;

    /**
     * Queries or persists due for publication for the article repository interface repository.
     *
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function dueForPublication(int $limit = 50): \Illuminate\Support\Collection;

    /**
     * Queries or persists publish if due for the article repository interface repository.
     *
     * @param int $id
     * @return bool
     */
    public function publishIfDue(int $id): bool;

    /**
     * Queries or persists scheduled between for the article repository interface repository.
     *
     * @param mixed $from
     * @param mixed $to
     * @return mixed
     */
    public function scheduledBetween($from, $to);

    /**
     * Queries and paginates published for the requested context.
     *
     * @param int $perPage
     * @return mixed
     */
    public function paginatePublished(int $perPage = 12);

    /**
     * Queries or persists search published for the article repository interface repository.
     *
     * @param string $query
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function searchPublished(string $query, int $limit = 8): \Illuminate\Support\Collection;

    /**
     * Queries or persists recently modified for the article repository interface repository.
     *
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function recentlyModified(int $limit = 10): \Illuminate\Support\Collection;

    /**
     * Queries or persists draft count for the article repository interface repository.
     * @return int
     */
    public function draftCount(): int;
}

