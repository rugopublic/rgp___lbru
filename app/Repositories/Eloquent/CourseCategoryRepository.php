<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\CourseCategory;
use App\Repositories\Contracts\CourseCategoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
final class CourseCategoryRepository extends Repository implements CourseCategoryRepositoryInterface
{

    /**
     * Queries and paginates for admin for the requested context.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginateForAdmin(array $filters = [], int $perPage = 20): LengthAwarePaginator { $q=CourseCategory::withCount('courses')->withTrashed(); $this->applyTextFilter($q,$filters['search']??null,['name']); match($filters['deleted']??''){ 'only'=>$q->onlyTrashed(),'all'=>null,default=>$q->withoutTrashed()}; return $q->orderBy('name')->paginate($perPage)->withQueryString(); }

    /**
     * Returns lightweight selector options from the repository.
     *
     * @param ?string $query
     * @param ?string $id
     * @param int $limit
     * @return Collection
     */
    public function options(?string $query=null,?string $id=null,int $limit=20):Collection { return $this->optionCollection(CourseCategory::class,$query,$id,$limit,'name'); }

    /**
     * Persists a new record through the repository model.
     *
     * @param array $attributes
     * @return Model
     */
    public function create(array $attributes):Model{return CourseCategory::create($attributes);}

    /**
     * Persists changes to the existing model through the repository.
     *
     * @param Model $category
     * @param array $attributes
     * @return bool
     */
    public function update(Model $category,array $attributes):bool{return $category->fill($attributes)->save();}

    /**
     * Soft-deletes the supplied model through the repository.
     *
     * @param Model $category
     * @return bool
     */
    public function delete(Model $category):bool{return (bool)$category->delete();}

    /**
     * Restores the supplied soft-deleted model through the repository.
     *
     * @param Model $category
     * @return bool
     */
    public function restore(Model $category):bool{return (bool)$category->restore();}

    /**
     * Queries the requested record and returns the matching persistence model.
     *
     * @param string $publicId
     * @param bool $withTrashed
     * @return ?Model
     */
    public function find(string $publicId,bool $withTrashed=false):?Model{$q=CourseCategory::query();if($withTrashed)$q->withTrashed();return $q->where('public_id',$publicId)->first();}
}
