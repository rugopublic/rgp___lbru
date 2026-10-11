<?php

declare(strict_types=1);

namespace App\Services\Admin\Content;

use App\Models\User;
use App\Repositories\Contracts\ReferenceRepositoryInterface;
use App\Repositories\Contracts\SeoRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class ReferenceService
{

    /**
     * Initializes the reference service instance and wires its required dependencies.
     *
     * @param ReferenceRepositoryInterface $repository
     * @param SeoRepositoryInterface $seo
     * @param ContentMediaService $media
     * @param RichTextMediaService $richText
     * @return void
     */
    public function __construct(private readonly ReferenceRepositoryInterface $repository, private readonly SeoRepositoryInterface $seo, private readonly ContentMediaService $media, private readonly RichTextMediaService $richText) {}

    /**
     * Creates the domain resource and applies the required business orchestration.
     *
     * @param array $attributes
     * @return Model
     */
    public function create(array $attributes): Model{return $this->repository->create($attributes);}

    /**
     * Updates the domain resource and applies the required business orchestration.
     *
     * @param Model $item
     * @param User $owner
     * @param array $data
     * @return bool
     */
    public function update(Model $item, User $owner, array $data): bool
    {
        $placed = [];

        try {
            $resolved = $this->richText->resolvePendingImages($owner, $item, $data['body'] ?? null);
            if (array_key_exists('body', $data)) {
                $data['body'] = $resolved['html'];
                $placed = $resolved['paths'];
            }

            if (array_key_exists('cover', $data)) {
                $this->placeSingle($item, $owner, $data, 'cover', 'cover', $placed);
            }

            return DB::transaction(function () use ($item, $data): bool {
                $seoData = array_intersect_key($data, array_flip(['meta_title', 'meta_description']));
                unset($data['meta_title'], $data['meta_description'], $data['cover_remove']);
                $productIds = $data['product_ids'] ?? null;
                unset($data['product_ids']);

                $saved = $this->repository->update($item, $data);
                if ($productIds !== null) {
                    $this->repository->syncProducts($item, $productIds);
                }

                if ($seoData !== []) {
                    $this->seo->update($this->seo->firstOrCreate($item), [
                        'title' => $seoData['meta_title'] ?? null,
                        'description' => $seoData['meta_description'] ?? null,
                    ]);
                }

                return $saved;
            });
        } catch (\Throwable $e) {
            foreach ($placed as $path) {
                Storage::disk('public')->delete($path);
            }

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
    private function placeSingle(Model $entity,User $owner,array &$data,string $field,string $slot,array &$placed):void{if(($data[$field.'_remove']??false)===true||($data[$field.'_remove']??null)==='1'){$data[$field]=null;unset($data[$field.'_remove']);return;}if(empty($data[$field]))return;$path=$this->media->placeUpload($owner,$entity,(string)$data[$field],$slot);$placed[]=$path;$data[$field]=basename($path);}
}
