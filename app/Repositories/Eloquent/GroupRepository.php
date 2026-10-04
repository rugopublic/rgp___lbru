<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Group;
use App\Repositories\Contracts\GroupRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
final class GroupRepository extends Repository implements GroupRepositoryInterface
{

    /**
     * Queries and paginates for admin for the requested context.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginateForAdmin(array $filters=[],int $perPage=20):LengthAwarePaginator{$q=Group::withCount('users')->withTrashed();$this->applyTextFilter($q,$filters['search']??null,['name']);match($filters['deleted']??''){ 'only'=>$q->onlyTrashed(),'all'=>null,default=>$q->withoutTrashed()};return $q->orderBy('name')->paginate($perPage)->withQueryString();}

    /**
     * Returns lightweight selector options from the repository.
     *
     * @param ?string $query
     * @param int $limit
     * @return Collection
     */
    public function options(?string $query=null,int $limit=20):Collection{return $this->optionCollection(Group::class,$query,null,$limit,'name');}

    /**
     * Persists a new record through the repository model.
     *
     * @param array $attributes
     * @return Model
     */
    public function create(array $attributes):Model{return Group::create($attributes);}

    /**
     * Persists changes to the existing model through the repository.
     *
     * @param Model $group
     * @param array $attributes
     * @return bool
     */
    public function update(Model $group,array $attributes):bool{return $group->fill($attributes)->save();}

    /**
     * Soft-deletes the supplied model through the repository.
     *
     * @param Model $group
     * @return bool
     */
    public function delete(Model $group):bool{return (bool)$group->delete();}

    /**
     * Restores the supplied soft-deleted model through the repository.
     *
     * @param Model $group
     * @return bool
     */
    public function restore(Model $group):bool{return (bool)$group->restore();}

    /**
     * Queries by public id and returns the matching persistence model.
     *
     * @param string $publicId
     * @param bool $withTrashed
     * @return ?Model
     */
    public function findByPublicId(string $publicId,bool $withTrashed=true):?Model{$q=Group::query();if($withTrashed)$q->withTrashed();return $q->where("public_id",$publicId)->first();}
}
