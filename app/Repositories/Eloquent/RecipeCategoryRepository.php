<?php

declare(strict_types=1);
namespace App\Repositories\Eloquent;
use App\Models\RecipeCategory;
use App\Repositories\Contracts\RecipeCategoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
final class RecipeCategoryRepository extends Repository implements RecipeCategoryRepositoryInterface
{

    /**
     * Queries and paginates for admin for the requested context.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginateForAdmin(array $filters=[],int $perPage=20):LengthAwarePaginator{ $q=RecipeCategory::withCount('recipes')->withTrashed(); $this->applyTextFilter($q,$filters['search']??null,['name']); match($filters['deleted']??''){'only'=>$q->onlyTrashed(),'all'=>null,default=>$q->withoutTrashed()}; return $q->orderBy('name')->paginate($perPage)->withQueryString(); }

    /**
     * Returns lightweight selector options from the repository.
     *
     * @param ?string $query
     * @param ?string $id
     * @param int $limit
     * @return Collection
     */
    public function options(?string $query=null,?string $id=null,int $limit=20):Collection{ return $this->optionCollection(RecipeCategory::class,$query,$id,$limit,'name'); }

    /**
     * Persists a new record through the repository model.
     *
     * @param array $attributes
     * @return Model
     */
    public function create(array $attributes):Model{return RecipeCategory::create($attributes);}

    /**
     * Persists changes to the existing model through the repository.
     *
     * @param Model $model
     * @param array $attributes
     * @return bool
     */
    public function update(Model $model,array $attributes):bool{return $model->fill($attributes)->save();}

    /**
     * Soft-deletes the supplied model through the repository.
     *
     * @param Model $model
     * @return bool
     */
    public function delete(Model $model):bool{return (bool)$model->delete();}

    /**
     * Restores the supplied soft-deleted model through the repository.
     *
     * @param Model $model
     * @return bool
     */
    public function restore(Model $model):bool{return (bool)$model->restore();}

    /**
     * Queries the requested record and returns the matching persistence model.
     *
     * @param string $publicId
     * @param bool $withTrashed
     * @return ?Model
     */
    public function find(string $publicId,bool $withTrashed=false):?Model{ $q=RecipeCategory::query(); if($withTrashed)$q->withTrashed(); return $q->where('public_id',$publicId)->first(); }
}
