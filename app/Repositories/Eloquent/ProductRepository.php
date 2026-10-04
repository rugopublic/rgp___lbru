<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
final class ProductRepository extends Repository implements ProductRepositoryInterface
{

    /**
     * Queries and paginates for admin for the requested context.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginateForAdmin(array $filters=[],int $perPage=20):LengthAwarePaginator{$q=Product::with('category')->withTrashed();$this->applyTextFilter($q,$filters['search']??null,['title']);if(isset($filters['visible'])&&$filters['visible']!=='')$q->where('is_visible',(bool)$filters['visible']);match($filters['deleted']??''){ 'only'=>$q->onlyTrashed(),'all'=>null,default=>$q->withoutTrashed()};return $q->latest()->paginate($perPage)->withQueryString();}

    /**
     * Queries for edit and returns the matching persistence model.
     *
     * @param string $publicId
     * @return Model
     */
    public function findForEdit(string $publicId):Model{return Product::withTrashed()->with(['category','articles','recipes','referenceItems','seo'])->where('public_id',$publicId)->firstOrFail();}

    /**
     * Returns lightweight selector options from the repository.
     *
     * @param ?string $query
     * @param ?string $id
     * @param int $limit
     * @return Collection
     */
    public function options(?string $query=null,?string $id=null,int $limit=20):Collection{return $this->optionCollection(Product::class,$query,$id,$limit,'title');}

    /**
     * Persists a new record through the repository model.
     *
     * @param array $attributes
     * @return Model
     */
    public function create(array $attributes):Model{return Product::create($attributes);}

    /**
     * Persists changes to the existing model through the repository.
     *
     * @param Model $product
     * @param array $attributes
     * @return bool
     */
    public function update(Model $product,array $attributes):bool{return $product->fill($attributes)->save();}

    /**
     * Synchronizes articles through the repository relationship.
     *
     * @param Model $product
     * @param array $ids
     * @return void
     */
    public function syncArticles(Model $product,array $ids):void{$sync=[];foreach(array_values(array_map('intval',$ids)) as $i=>$id)$sync[$id]=['sort_order'=>$i];$product->articles()->sync($sync);}

    /**
     * Synchronizes recipes through the repository relationship.
     *
     * @param Model $product
     * @param array $ids
     * @return void
     */
    public function syncRecipes(Model $product,array $ids):void{$sync=[];foreach(array_values(array_map('intval',$ids)) as $i=>$id)$sync[$id]=['sort_order'=>$i];$product->recipes()->sync($sync);}

    /**
     * Synchronizes reference items through the repository relationship.
     *
     * @param Model $product
     * @param array $ids
     * @return void
     */
    public function syncReferenceItems(Model $product,array $ids):void{$sync=[];foreach(array_values(array_map('intval',$ids)) as $i=>$id)$sync[$id]=['sort_order'=>$i];$product->referenceItems()->sync($sync);}

    /**
     * Soft-deletes the supplied model through the repository.
     *
     * @param Model $product
     * @return bool
     */
    public function delete(Model $product):bool{return (bool)$product->delete();}

    /**
     * Restores the supplied soft-deleted model through the repository.
     *
     * @param Model $product
     * @return bool
     */
    public function restore(Model $product):bool{return (bool)$product->restore();}

    /**
     * Queries sellable and returns the matching persistence model.
     *
     * @param string $publicId
     * @return ?Model
     */
    public function findSellable(string $publicId):?Model
    {
        return Product::query()
            ->with([
                'category',
                'articles' => fn ($query) => $query->where('articles.is_visible', true)->whereNull('articles.deleted_at')->with('category'),
                'recipes' => fn ($query) => $query->whereIn('recipes.visibility_status', ['demo', 'published'])->whereNull('recipes.deleted_at')->with('categories'),
                'referenceItems' => fn ($query) => $query->where('ingredients.is_visible', true)->whereNull('ingredients.deleted_at')->with('category'),
            ])
            ->where('public_id',$publicId)
            ->where('is_visible',true)
            ->first();
    }

    /**
     * Queries for access and returns the matching persistence model.
     *
     * @param string $publicId
     * @return ?Model
     */
    public function findForAccess(string $publicId): ?Model
    {
        return Product::query()->withTrashed()->where('public_id',$publicId)->first();
    }

    /**
     * Queries or persists all with files for the product repository repository.
     * @return Collection
     */
    public function allWithFiles(): Collection
    {
        return Product::query()->withTrashed()->whereNotNull('file')->get(['id','public_id','file']);
    }

    /**
     * Queries and paginates sellable for the requested context.
     *
     * @param int $perPage
     * @return mixed
     */
    public function paginateSellable(int $perPage = 12){return Product::query()->with('category')->where('is_visible',true)->orderBy('title')->paginate($perPage)->withQueryString();}
}
