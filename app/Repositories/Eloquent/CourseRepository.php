<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Course;
use App\Repositories\Contracts\CourseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

final class CourseRepository extends Repository implements CourseRepositoryInterface
{

    /**
     * Queries and paginates for admin for the requested context.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginateForAdmin(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $q = Course::query()->with('category')->withCount(['modules','lessons','teachers'])->withTrashed();
        $this->applyTextFilter($q, $filters['search'] ?? null, ['title']);
        if (!empty($filters['category'])) $q->where('category_id', $filters['category']);
        if (!empty($filters['status'])) $q->where('publish_status', $filters['status']);
        match ($filters['deleted'] ?? '') { 'only' => $q->onlyTrashed(), 'all' => null, default => $q->withoutTrashed() };
        $sort = in_array($filters['sort'] ?? 'created_at', ['created_at','title','price','publish_date'], true) ? $filters['sort'] : 'created_at';
        $direction = ($filters['direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        return $q->orderBy($sort, $direction)->paginate($perPage)->withQueryString();
    }

    /**
     * Queries for edit and returns the matching persistence model.
     *
     * @param string $publicId
     * @return Model
     */
    public function findForEdit(string $publicId): Model
    {
        return Course::withTrashed()->with(['category','teachers','modules.lessons.steps','modules.lessons.quizzes.questions.options','seo'])->where('public_id',$publicId)->firstOrFail();
    }

    /**
     * Persists a new record through the repository model.
     *
     * @param array $attributes
     * @return Model
     */
    public function create(array $attributes): Model { return Course::create($attributes); }

    /**
     * Persists changes to the existing model through the repository.
     *
     * @param Model $course
     * @param array $attributes
     * @return bool
     */
    public function update(Model $course, array $attributes): bool { return $course->fill($attributes)->save(); }

    /**
     * Soft-deletes the supplied model through the repository.
     *
     * @param Model $course
     * @return bool
     */
    public function delete(Model $course): bool { return (bool) $course->delete(); }

    /**
     * Restores the supplied soft-deleted model through the repository.
     *
     * @param Model $course
     * @return bool
     */
    public function restore(Model $course): bool { return (bool) $course->restore(); }

    /**
     * Queries published and returns the matching persistence model.
     *
     * @param string $publicId
     * @return ?Model
     */
    public function findPublished(string $publicId): ?Model { return Course::with(['category','teachers','modules.lessons.steps','seo'])->where('public_id',$publicId)->where('publish_status','published')->where(function($q){$q->whereNull('publish_date')->orWhere('publish_date','<=',now());})->first(); }

    /**
     * Finds course content for an existing enrollment, including unpublished and
     * soft-deleted courses. The caller must enforce active enrollment before rendering.
     *
     * @param string $publicId
     * @return ?Model
     */
    public function findForAccess(string $publicId): ?Model
    {
        return Course::withTrashed()
            ->with(['category', 'teachers', 'modules.lessons.steps', 'seo'])
            ->where('public_id', $publicId)
            ->first();
    }

    /**
     * Synchronizes teachers through the repository relationship.
     *
     * @param Model $course
     * @param array $teacherIds
     * @return void
     */
    public function syncTeachers(Model $course, array $teacherIds): void
    {
        $sync = []; foreach (array_values(array_unique(array_map('intval',$teacherIds))) as $order => $id) $sync[$id] = ['sort_order' => $order];
        $course->teachers()->sync($sync);
    }

    /**
     * Queries or persists due for publication for the course repository repository.
     *
     * @param int $limit
     * @return Collection
     */
    public function dueForPublication(int $limit = 50): Collection { return Course::query()->where('publish_status','scheduled')->whereNotNull('publish_date')->where('publish_date','<=',now())->orderBy('id')->limit($limit)->get(['id']); }

    /**
     * Queries or persists publish if due for the course repository repository.
     *
     * @param int $id
     * @return bool
     */
    public function publishIfDue(int $id): bool { return Course::query()->whereKey($id)->where('publish_status','scheduled')->whereNotNull('publish_date')->where('publish_date','<=',now())->update(['publish_status'=>'published','updated_at'=>now()]) === 1; }

    /**
     * Queries or persists scheduled between for the course repository repository.
     *
     * @param mixed $from
     * @param mixed $to
     * @return mixed
     */
    public function scheduledBetween($from,$to) { return Course::query()->where('publish_status','scheduled')->whereNotNull('publish_date')->whereBetween('publish_date',[$from,$to])->orderBy('publish_date')->get(); }

    /**
     * Queries or persists recently modified for the course repository repository.
     *
     * @param int $limit
     * @return Collection
     */
    public function recentlyModified(int $limit = 10): Collection { return Course::query()->withTrashed()->with('category')->latest('updated_at')->limit($limit)->get(['id','public_id','title','updated_at']); }

    /**
     * Queries or persists draft count for the course repository repository.
     * @return int
     */
    public function draftCount(): int { return (int) Course::query()->where('publish_status','draft')->count(); }

    /**
     * Queries and paginates published for the requested context.
     *
     * @param int $perPage
     * @return mixed
     */
    public function paginatePublished(int $perPage = 12){return Course::query()->with(['category','teachers','modules.lessons'])->where('publish_status','published')->where(fn($q)=>$q->whereNull('publish_date')->orWhere('publish_date','<=',now()))->latest('publish_date')->latest('created_at')->paginate($perPage)->withQueryString();}

    /**
     * Queries or persists lesson options for the course repository repository.
     *
     * @param ?string $query
     * @param ?string $id
     * @param int $limit
     * @return Collection
     */
    public function lessonOptions(?string $query = null, ?string $id = null, int $limit = 20): Collection
    {
        return $this->optionCollection(\App\Models\CourseLesson::class,$query,$id,$limit,'title','id');
    }
}
