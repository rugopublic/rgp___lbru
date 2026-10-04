<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\ReferenceItem;
use App\Repositories\Contracts\ReferenceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
final class ReferenceRepository extends Repository implements ReferenceRepositoryInterface
{

    /**
     * Queries and paginates for admin for the requested context.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginateForAdmin(array $filters=[],int $perPage=20):LengthAwarePaginator{$q=ReferenceItem::with('category')->withTrashed();$this->applyTextFilter($q,$filters['search']??null,['title']);if(isset($filters['visible'])&&$filters['visible']!=='')$q->where('is_visible',(bool)$filters['visible']);match($filters['deleted']??''){ 'only'=>$q->onlyTrashed(),'all'=>null,default=>$q->withoutTrashed()};return $q->orderBy('title')->paginate($perPage)->withQueryString();}

    /**
     * Queries for edit and returns the matching persistence model.
     *
     * @param string $publicId
     * @return Model
     */
    public function findForEdit(string $publicId):Model{return ReferenceItem::withTrashed()->with(['category','products','seo'])->where('public_id',$publicId)->firstOrFail();}

    /**
     * Returns lightweight selector options from the repository.
     *
     * @param ?string $query
     * @param ?string $id
     * @param int $limit
     * @return Collection
     */
    public function options(?string $query=null,?string $id=null,int $limit=20):Collection{return $this->optionCollection(ReferenceItem::class,$query,$id,$limit,'title');}

    /**
     * Persists a new record through the repository model.
     *
     * @param array $attributes
     * @return Model
     */
    public function create(array $attributes):Model{return ReferenceItem::create($attributes);}

    /**
     * Persists changes to the existing model through the repository.
     *
     * @param Model $item
     * @param array $attributes
     * @return bool
     */
    public function update(Model $item,array $attributes):bool{return $item->fill($attributes)->save();}

    /**
     * Synchronizes products through the repository relationship.
     *
     * @param Model $item
     * @param array $ids
     * @return void
     */
    public function syncProducts(Model $item,array $ids):void{$sync=[];foreach(array_values(array_map('intval',$ids)) as $i=>$id)$sync[$id]=['sort_order'=>$i];$item->products()->sync($sync);}

    /**
     * Soft-deletes the supplied model through the repository.
     *
     * @param Model $item
     * @return bool
     */
    public function delete(Model $item):bool{return (bool)$item->delete();}

    /**
     * Restores the supplied soft-deleted model through the repository.
     *
     * @param Model $item
     * @return bool
     */
    public function restore(Model $item):bool{return (bool)$item->restore();}

    /**
     * Queries visible and returns the matching persistence model.
     *
     * @param string $publicId
     * @return ?Model
     */
    public function findVisible(string $publicId):?Model{return ReferenceItem::with(['category','products'=>fn($q)=>$q->where('products.is_visible',true)->whereNull('products.deleted_at')])->where('public_id',$publicId)->where('is_visible',true)->first();}

    /**
     * Queries or persists search visible for the reference repository repository.
     *
     * @param string $query
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function searchVisible(string $query, int $limit = 8): \Illuminate\Support\Collection { $q=trim($query); if($q==='') return collect(); return ReferenceItem::query()->with('category')->where('is_visible',true)->where(fn($x)=>$x->where('title','like','%'.$q.'%')->orWhere('description','like','%'.$q.'%'))->orderBy('title')->limit($limit)->get(); }

    /**
     * Queries and paginates visible for the requested context.
     *
     * @param int $perPage
     * @return mixed
     */
    public function paginateVisible(int $perPage = 12){return ReferenceItem::query()->with('category')->where('is_visible',true)->orderBy('title')->paginate($perPage)->withQueryString();}
}
