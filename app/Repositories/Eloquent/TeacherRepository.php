<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Course\Teacher;
use App\Repositories\Contracts\TeacherRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
final class TeacherRepository extends Repository implements TeacherRepositoryInterface
{

    /**
     * Queries and paginates for admin for the requested context.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginateForAdmin(array $filters=[],int $perPage=20):LengthAwarePaginator{$q=Teacher::withTrashed();$this->applyTextFilter($q,$filters['search']??null,['title','motto','about']);match($filters['deleted']??''){ 'only'=>$q->onlyTrashed(),'all'=>null,default=>$q->withoutTrashed()};return $q->orderBy('title')->paginate($perPage)->withQueryString();}

    /**
     * Returns lightweight selector options from the repository.
     *
     * @param ?string $query
     * @param ?string $id
     * @param int $limit
     * @return Collection
     */
    public function options(?string $query=null,?string $id=null,int $limit=20):Collection{return $this->optionCollection(Teacher::class,$query,$id,$limit,'title');}

    /**
     * Queries for edit and returns the matching persistence model.
     *
     * @param string $publicId
     * @return Model
     */
    public function findForEdit(string $publicId):Model{return Teacher::withTrashed()->withCount('courses')->where('public_id',$publicId)->firstOrFail();}

    /**
     * Persists a new record through the repository model.
     *
     * @param array $attributes
     * @return Model
     */
    public function create(array $attributes):Model{return Teacher::create($attributes);}

    /**
     * Persists changes to the existing model through the repository.
     *
     * @param Model $teacher
     * @param array $attributes
     * @return bool
     */
    public function update(Model $teacher,array $attributes):bool{return $teacher->fill($attributes)->save();}

    /**
     * Soft-deletes the supplied model through the repository.
     *
     * @param Model $teacher
     * @return bool
     */
    public function delete(Model $teacher):bool{return (bool)$teacher->delete();}

    /**
     * Restores the supplied soft-deleted model through the repository.
     *
     * @param Model $teacher
     * @return bool
     */
    public function restore(Model $teacher):bool{return (bool)$teacher->restore();}
}
