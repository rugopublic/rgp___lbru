<?php

declare(strict_types=1);

namespace App\Services\Admin\Content;

use App\Repositories\Contracts\ArticleRepositoryInterface;
use App\Repositories\Contracts\SeoRepositoryInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

final class ArticleService
{

    /**
     * Initializes the article service instance and wires its required dependencies.
     *
     * @param ArticleRepositoryInterface $repository
     * @param SeoRepositoryInterface $seo
     * @param ContentMediaService $media
     * @param RichTextMediaService $richText
     * @return void
     */
    public function __construct(private readonly ArticleRepositoryInterface $repository, private readonly SeoRepositoryInterface $seo, private readonly ContentMediaService $media, private readonly RichTextMediaService $richText)
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
     * @param Model $article
     * @param User $owner
     * @param array $data
     * @return bool
     */
    public function update(Model $article, User $owner, array $data): bool
    {
        $placed = [];
        if (array_key_exists('body',$data)) {
            $resolved = $this->richText->resolvePendingImages($owner,$article,$data['body']);
            $data['body'] = $resolved['html']; $placed = $resolved['paths'];
        }
        $this->placeSingle($article,$owner,$data,'cover','cover',$placed);
        try {
            $ok = DB::transaction(function () use ($article,$data): bool {
                $seoData = array_intersect_key($data, array_flip(['meta_title','meta_description']));
                unset($data['meta_title'],$data['meta_description'],$data['cover_remove']);$productIds=$data['product_ids']??null;unset($data['product_ids']);
                $saved = $this->repository->update($article,$data);
                if ($productIds !== null) $this->repository->syncProducts($article, $productIds);
                if ($seoData !== []) $this->seo->update($this->seo->firstOrCreate($article),['title'=>$seoData['meta_title']??null,'description'=>$seoData['meta_description']??null]);
                return $saved;
            });
            if (!$ok) throw new RuntimeException('Статья не сохранена.');
            return true;
        } catch (\Throwable $e) {
            foreach ($placed as $path) Storage::disk('public')->delete($path);
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
    private function placeSingle(Model $entity, User $owner, array &$data, string $field, string $slot, array &$placed): void
    {
        if (($data[$field.'_remove'] ?? false) === true || ($data[$field.'_remove'] ?? null) === '1') { $data[$field]=null; unset($data[$field.'_remove']); return; }
        if (empty($data[$field])) return;
        $placedPath = $this->media->placeUpload($owner,$entity,(string)$data[$field],$slot);
        $placed[]=$placedPath; $data[$field]=basename($placedPath);
    }
}
