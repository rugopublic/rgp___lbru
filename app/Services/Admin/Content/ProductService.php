<?php

declare(strict_types=1);

namespace App\Services\Admin\Content;

use App\Models\User;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\SeoRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

final class ProductService
{

    /**
     * Initializes the product service instance and wires its required dependencies.
     *
     * @param ProductRepositoryInterface $repository
     * @param SeoRepositoryInterface $seo
     * @param ContentMediaService $media
     * @return void
     */
    public function __construct(private readonly ProductRepositoryInterface $repository, private readonly SeoRepositoryInterface $seo, private readonly ContentMediaService $media)
    {
    }

    /**
     * Creates the domain resource and applies the required business orchestration.
     *
     * @param array $attributes
     * @return Model
     */
    public function create(array $attributes): Model { return $this->repository->create($attributes); }

    /**
     * Updates the domain resource and applies the required business orchestration.
     *
     * @param Model $product
     * @param User $owner
     * @param array $data
     * @return bool
     */
    public function update(Model $product, User $owner, array $data): bool
    {
        $placed=[];
        try {
            foreach (['file','cover'] as $field) {
                $this->placeSingle($product, $owner, $data, $field, $field, $placed);
            }

            return DB::transaction(function() use($product,$data):bool{
                $seoData=array_intersect_key($data,array_flip(['meta_title','meta_description']));
                $articleIds = $data['article_ids'] ?? null;
                $recipeIds = $data['recipe_ids'] ?? null;
                $referenceIds = $data['reference_item_ids'] ?? null;
                unset($data['meta_title'],$data['meta_description'],$data['file_remove'],$data['cover_remove'],$data['article_ids'],$data['recipe_ids'],$data['reference_item_ids']);
                $result=$this->repository->update($product,$data);
                if ($articleIds !== null) $this->repository->syncArticles($product, $articleIds);
                if ($recipeIds !== null) $this->repository->syncRecipes($product, $recipeIds);
                if ($referenceIds !== null) $this->repository->syncReferenceItems($product, $referenceIds);
                if ($seoData!==[]) $this->seo->update($this->seo->firstOrCreate($product),['title'=>$seoData['meta_title']??null,'description'=>$seoData['meta_description']??null]);
                return $result;
            });
        } catch(\Throwable $e){
            foreach($placed as [$path, $disk]) Storage::disk($disk)->delete($path);
            throw $e;
        }
    }

    /**
     * Places single into permanent storage using the media lifecycle rules.
     *
     * @param Model $entity
     * @param User $owner
     * @param array $data
     * @param string $field
     * @param string $slot
     * @param array $placed
     * @return void
     */
    private function placeSingle(Model $entity, User $owner, array &$data,string $field,string $slot,array &$placed):void{if(($data[$field.'_remove']??false)===true||($data[$field.'_remove']??null)==='1'){ $data[$field]=null; unset($data[$field.'_remove']); return;}if(empty($data[$field]))return;$path=$this->media->placeUpload($owner,$entity,(string)$data[$field],$slot);$placed[]=[$path, $this->media->diskForSlot($slot)];$data[$field]=basename($path);}
}
