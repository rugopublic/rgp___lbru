<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface ContentInteractionRepositoryInterface
{

    /**
     * Queries or persists context for the content interaction repository interface repository.
     *
     * @param Model $model
     * @param ?int $userId
     * @return array
     */
    public function context(Model $model, ?int $userId = null): array;

    /**
     * Queries or persists published comments for the content interaction repository interface repository.
     *
     * @param Model $model
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function publishedComments(Model $model, int $perPage = 20): LengthAwarePaginator;

    /**
     * Queries or persists toggle like for the content interaction repository interface repository.
     *
     * @param Model $model
     * @param int $userId
     * @return bool
     */
    public function toggleLike(Model $model, int $userId): bool;

    /**
     * Queries or persists toggle bookmark for the content interaction repository interface repository.
     *
     * @param Model $model
     * @param int $userId
     * @return bool
     */
    public function toggleBookmark(Model $model, int $userId): bool;

    /**
     * Queries or persists add comment for the content interaction repository interface repository.
     *
     * @param Model $model
     * @param int $userId
     * @param string $body
     * @return Model
     */
    public function addComment(Model $model, int $userId, string $body): Model;

    /**
     * Queries or persists saved by user for the content interaction repository interface repository.
     *
     * @param int $userId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function savedByUser(int $userId, int $perPage = 18): LengthAwarePaginator;

    /**
     * Queries or persists comments by user for the content interaction repository interface repository.
     *
     * @param int $userId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function commentsByUser(int $userId, int $perPage = 18): LengthAwarePaginator;
}
