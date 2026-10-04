<?php
declare(strict_types=1);
namespace App\Repositories\Eloquent;
use App\Models\Article;
use App\Repositories\Contracts\ArticleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
final class ArticleRepository extends Repository implements ArticleRepositoryInterface
{

    /**
     * Queries and paginates for admin for the requested context.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginateForAdmin(array $filters=[],int $perPage=20):LengthAwarePaginator{$q=Article::with(['category','author'])->withTrashed();$this->applyTextFilter($q,$filters['search']??null,['title']);if(isset($filters['visible'])&&$filters['visible']!=='')$q->where('is_visible',(bool)$filters['visible']);match($filters['schedule']??''){ 'scheduled'=>$q->whereNotNull('publish_at')->where('publish_at','>',now()), 'due'=>$q->whereNotNull('publish_at')->where('publish_at','<=',now()), default=>null};match($filters['deleted']??''){ 'only'=>$q->onlyTrashed(),'all'=>null,default=>$q->withoutTrashed()};return $q->latest()->paginate($perPage)->withQueryString();}

    /**
     * Queries for edit and returns the matching persistence model.
     *
     * @param string $publicId
     * @return Model
     */
    public function findForEdit(string $publicId):Model{return Article::withTrashed()->with(['category','author','products','seo'])->where('public_id',$publicId)->firstOrFail();}

    /**
     * Persists a new record through the repository model.
     *
     * @param array $attributes
     * @return Model
     */
    public function create(array $attributes):Model{return Article::create($attributes);}

    /**
     * Persists changes to the existing model through the repository.
     *
     * @param Model $article
     * @param array $attributes
     * @return bool
     */
    public function update(Model $article,array $attributes):bool{return $article->fill($attributes)->save();}

    /**
     * Synchronizes products through the repository relationship.
     *
     * @param Model $article
     * @param array $ids
     * @return void
     */
    public function syncProducts(Model $article,array $ids):void{$sync=[];foreach(array_values(array_unique(array_map('intval',$ids))) as $i=>$id)$sync[$id]=['sort_order'=>$i];$article->products()->sync($sync);}

    /**
     * Soft-deletes the supplied model through the repository.
     *
     * @param Model $article
     * @return bool
     */
    public function delete(Model $article):bool{return (bool)$article->delete();}

    /**
     * Restores the supplied soft-deleted model through the repository.
     *
     * @param Model $article
     * @return bool
     */
    public function restore(Model $article):bool{return (bool)$article->restore();}

    /**
     * Queries published and returns the matching persistence model.
     *
     * @param string $publicId
     * @return ?Model
     */
    public function findPublished(string $publicId):?Model{return Article::with(['category','author'=>fn($q)=>$q,'products'=>fn($q)=>$q->where('products.is_visible',true)->whereNull('products.deleted_at'),'seo'])->where('public_id',$publicId)->where('is_visible',true)->where(function($q){$q->whereNull('publish_at')->orWhere('publish_at','<=',now());})->first();}

    /**
     * Queries or persists due for publication for the article repository repository.
     *
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function dueForPublication(int $limit = 50): \Illuminate\Support\Collection { return Article::query()->where('is_visible',false)->whereNotNull('publish_at')->where('publish_at','<=',now())->orderBy('id')->limit($limit)->get(['id']); }

    /**
     * Queries or persists publish if due for the article repository repository.
     *
     * @param int $id
     * @return bool
     */
    public function publishIfDue(int $id): bool { return Article::query()->whereKey($id)->where('is_visible',false)->whereNotNull('publish_at')->where('publish_at','<=',now())->update(['is_visible'=>true,'updated_at'=>now()]) === 1; }

    /**
     * Queries or persists scheduled between for the article repository repository.
     *
     * @param mixed $from
     * @param mixed $to
     * @return mixed
     */
    public function scheduledBetween($from,$to){return Article::query()->with(['category'])->where('is_visible',false)->whereNotNull('publish_at')->whereBetween('publish_at',[$from,$to])->orderBy('publish_at')->get();}

    /**
     * Queries or persists recently modified for the article repository repository.
     *
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function recentlyModified(int $limit = 10): \Illuminate\Support\Collection { return Article::query()->withTrashed()->with('category')->latest('updated_at')->limit($limit)->get(['id','public_id','title','updated_at']); }

    /**
     * Queries or persists draft count for the article repository repository.
     * @return int
     */
    public function draftCount(): int { return (int) Article::query()->where('is_visible',false)->whereNull('publish_at')->count(); }

    /**
     * Queries or persists search published for the article repository repository.
     *
     * @param string $query
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function searchPublished(string $query, int $limit = 8): \Illuminate\Support\Collection { $q=trim($query); if($q==='') return collect(); return Article::query()->with('category')->where('is_visible',true)->where(fn($x)=>$x->where('title','like','%'.$q.'%')->orWhere('description','like','%'.$q.'%'))->latest('publish_at')->limit($limit)->get(); }

    /**
     * Queries and paginates published for the requested context.
     *
     * @param int $perPage
     * @return mixed
     */
    public function paginatePublished(int $perPage = 12){return Article::query()->with(['category','author','products'=>fn($q)=>$q->where('products.is_visible',true)->whereNull('products.deleted_at')])->where('is_visible',true)->where(fn($q)=>$q->whereNull('publish_at')->orWhere('publish_at','<=',now()))->latest('publish_at')->latest('created_at')->paginate($perPage)->withQueryString();}
}
