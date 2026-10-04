<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface TeacherRepositoryInterface
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
     * @param ?string $id
     * @param int $limit
     * @return Collection
     */
    public function options(?string $query = null, ?string $id = null, int $limit = 20): Collection;

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
     * @param Model $teacher
     * @param array $attributes
     * @return bool
     */
    public function update(Model $teacher, array $attributes): bool;

    /**
     * Soft-deletes the supplied model through the repository.
     *
     * @param Model $teacher
     * @return bool
     */
    public function delete(Model $teacher): bool;

    /**
     * Restores the supplied soft-deleted model through the repository.
     *
     * @param Model $teacher
     * @return bool
     */
    public function restore(Model $teacher): bool;
}
