<?php
declare(strict_types=1);
namespace App\Repositories\Eloquent;
use App\Models\Recipe;
use App\Repositories\Contracts\RecipeRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
final class RecipeRepository extends Repository implements RecipeRepositoryInterface
{

    /**
     * Queries and paginates for admin for the requested context.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginateForAdmin(array $filters=[],int $perPage=20):LengthAwarePaginator{$q=Recipe::with(['categories','author'])->withTrashed();$this->applyTextFilter($q,$filters['search']??null,['title']);if(!empty($filters['status']))$q->where('visibility_status',$filters['status']);match($filters['deleted']??''){ 'only'=>$q->onlyTrashed(),'all'=>null,default=>$q->withoutTrashed()};return $q->latest()->paginate($perPage)->withQueryString();}

    /**
     * Queries for edit and returns the matching persistence model.
     *
     * @param string $publicId
     * @return Model
     */
    public function findForEdit(string $publicId):Model{return Recipe::withTrashed()->with(['categories','author','ingredientGroups.ingredients.referenceItem','products','steps','seo'])->where('public_id',$publicId)->firstOrFail();}

    /**
     * Persists a new record through the repository model.
     *
     * @param array $attributes
     * @return Model
     */
    public function create(array $attributes):Model{return Recipe::create($attributes);}

    /**
     * Persists changes to the existing model through the repository.
     *
     * @param Model $recipe
     * @param array $attributes
     * @return bool
     */
    public function update(Model $recipe,array $attributes):bool{return $recipe->fill($attributes)->save();}

    /**
     * Synchronizes categories through the repository relationship.
     *
     * @param Model $recipe
     * @param array $ids
     * @return void
     */
    public function syncCategories(Model $recipe,array $ids):void{$recipe->categories()->sync(array_values(array_unique(array_map('intval',$ids))));}

    /**
     * Synchronizes products through the repository relationship.
     *
     * @param Model $recipe
     * @param array $ids
     * @return void
     */
    public function syncProducts(Model $recipe,array $ids):void{$sync=[];foreach(array_values(array_unique(array_map('intval',$ids))) as $i=>$id)$sync[$id]=['sort_order'=>$i];$recipe->products()->sync($sync);}

    /**
     * Soft-deletes the supplied model through the repository.
     *
     * @param Model $recipe
     * @return bool
     */
    public function delete(Model $recipe):bool{return (bool)$recipe->delete();}

    /**
     * Restores the supplied soft-deleted model through the repository.
     *
     * @param Model $recipe
     * @return bool
     */
    public function restore(Model $recipe):bool{return (bool)$recipe->restore();}

    /**
     * Queries published and returns the matching persistence model.
     *
     * @param string $publicId
     * @return ?Model
     */
    public function findPublished(string $publicId):?Model{return Recipe::with(['categories','author','ingredientGroups.ingredients.referenceItem','products'=>fn($q)=>$q->where('products.is_visible',true)->whereNull('products.deleted_at'),'steps'])->where('public_id',$publicId)->where('visibility_status','published')->where(function($q){$q->whereNull('publish_at')->orWhere('publish_at','<=',now());})->first();}

    /**
     * Queries or persists due for publication for the recipe repository repository.
     *
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function dueForPublication(int $limit = 50): \Illuminate\Support\Collection { return Recipe::query()->whereIn('visibility_status',['draft','demo'])->whereNotNull('publish_at')->where('publish_at','<=',now())->orderBy('id')->limit($limit)->get(['id']); }

    /**
     * Queries or persists publish if due for the recipe repository repository.
     *
     * @param int $id
     * @return bool
     */
    public function publishIfDue(int $id): bool { return Recipe::query()->whereKey($id)->whereIn('visibility_status',['draft','demo'])->whereNotNull('publish_at')->where('publish_at','<=',now())->update(['visibility_status'=>'published','updated_at'=>now()]) === 1; }

    /**
     * Queries or persists scheduled between for the recipe repository repository.
     *
     * @param mixed $from
     * @param mixed $to
     * @return mixed
     */
    public function scheduledBetween($from,$to){return Recipe::query()->where('visibility_status','!=','published')->whereNotNull('publish_at')->whereBetween('publish_at',[$from,$to])->orderBy('publish_at')->get();}

    /**
     * Queries or persists recently modified for the recipe repository repository.
     *
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function recentlyModified(int $limit = 10): \Illuminate\Support\Collection { return Recipe::query()->withTrashed()->with('categories')->latest('updated_at')->limit($limit)->get(['id','public_id','title','updated_at']); }

    /**
     * Queries or persists draft count for the recipe repository repository.
     * @return int
     */
    public function draftCount(): int { return (int) Recipe::query()->where('visibility_status','draft')->count(); }

    /**
     * Queries or persists search published for the recipe repository repository.
     *
     * @param string $query
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function searchPublished(string $query, int $limit = 8): \Illuminate\Support\Collection { $q=trim($query); if($q==='') return collect(); return Recipe::query()->with('categories')->where('visibility_status','published')->where(fn($x)=>$x->where('title','like','%'.$q.'%')->orWhere('description','like','%'.$q.'%'))->latest('publish_at')->limit($limit)->get(); }

    /**
     * Queries and paginates published for the requested context.
     *
     * @param int $perPage
     * @return mixed
     */
    public function paginatePublished(int $perPage = 12){return Recipe::query()->with(['categories','author','ingredientGroups.ingredients.referenceItem','products'=>fn($q)=>$q->where('products.is_visible',true)->whereNull('products.deleted_at')])->where('visibility_status','published')->where(fn($q)=>$q->whereNull('publish_at')->orWhere('publish_at','<=',now()))->latest('publish_at')->latest('created_at')->paginate($perPage)->withQueryString();}
}
