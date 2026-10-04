<?php
declare(strict_types=1);
namespace App\Repositories\Contracts;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
interface CourseRepositoryInterface
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
     * @param Model $course
     * @param array $attributes
     * @return bool
     */
    public function update(Model $course, array $attributes): bool;

    /**
     * Soft-deletes the supplied model through the repository.
     *
     * @param Model $course
     * @return bool
     */
    public function delete(Model $course): bool;

    /**
     * Restores the supplied soft-deleted model through the repository.
     *
     * @param Model $course
     * @return bool
     */
    public function restore(Model $course): bool;

    /**
     * Queries published and returns the matching persistence model.
     *
     * @param string $publicId
     * @return ?Model
     */
    public function findPublished(string $publicId): ?Model;

    /**
     * Synchronizes teachers through the repository relationship.
     *
     * @param Model $course
     * @param array $teacherIds
     * @return void
     */
    public function syncTeachers(Model $course, array $teacherIds): void;

    /**
     * Queries or persists due for publication for the course repository interface repository.
     *
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function dueForPublication(int $limit = 50): \Illuminate\Support\Collection;

    /**
     * Queries or persists publish if due for the course repository interface repository.
     *
     * @param int $id
     * @return bool
     */
    public function publishIfDue(int $id): bool;

    /**
     * Queries or persists scheduled between for the course repository interface repository.
     *
     * @param mixed $from
     * @param mixed $to
     * @return mixed
     */
    public function scheduledBetween($from, $to);

    /**
     * Queries or persists recently modified for the course repository interface repository.
     *
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function recentlyModified(int $limit = 10): \Illuminate\Support\Collection;

    /**
     * Queries or persists draft count for the course repository interface repository.
     * @return int
     */
    public function draftCount(): int;
}

