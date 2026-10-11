<?php

declare(strict_types=1);

namespace App\Services\Admin\Content;

use App\Models\Recipe;
use App\Models\User;
use App\Repositories\Contracts\RecipeRepositoryInterface;
use App\Repositories\Contracts\RecipeStepRepositoryInterface;
use App\Repositories\Contracts\SeoRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

final class RecipeService
{

    /**
     * Initializes the recipe service instance and wires its required dependencies.
     *
     * @param RecipeRepositoryInterface $repository
     * @param RecipeStepRepositoryInterface $steps
     * @param SeoRepositoryInterface $seo
     * @param ContentMediaService $media
     * @param RichTextMediaService $richText
     * @param RecipeStructureService $structureService
     * @return void
     */
    public function __construct(
        private readonly RecipeRepositoryInterface $repository,
        private readonly RecipeStepRepositoryInterface $steps,
        private readonly SeoRepositoryInterface $seo,
        private readonly ContentMediaService $media,
        private readonly RichTextMediaService $richText,
        private readonly RecipeStructureService $structureService,
    ) {}

    /**
     * Creates the domain resource and applies the required business orchestration.
     *
     * @param array $data
     * @return Model
     */
    public function create(array $data): Model
    {
        return $this->repository->create($data);
    }

    /**
     * Updates the domain resource and applies the required business orchestration.
     *
     * @param Model $recipe
     * @param User $owner
     * @param array $data
     * @return bool
     */
    public function update(Model $recipe, User $owner, array $data): bool
    {
        $placed = [];
        try {
        $resolved = $this->richText->resolvePendingImages($owner, $recipe, $data['body'] ?? null);
        if (array_key_exists('body', $data)) {
            $data['body'] = $resolved['html'];
            $placed = $resolved['paths'];
        }

        $structure = $data['structure'] ?? null;
        $stepData = $data['steps'] ?? null;
        unset($data['structure'], $data['steps']);

        foreach (['cover', 'video'] as $field) {
            $this->placeSingle($recipe, $owner, $data, $field, $field, $placed);
        }

            return DB::transaction(function () use ($recipe, $owner, $data, $structure, $stepData, &$placed): bool {
                $seoData = array_intersect_key($data, array_flip(['meta_title', 'meta_description']));
                $categories = $data['category_ids'] ?? null;
                $products = $data['product_ids'] ?? null;
                unset($data['meta_title'], $data['meta_description'], $data['cover_remove'], $data['video_remove'], $data['category_ids'], $data['product_ids']);

                $ok = $this->repository->update($recipe, $data);
                if (!$ok) {
                    throw new RuntimeException('Рецепт не сохранён.');
                }

                if ($categories !== null) $this->repository->syncCategories($recipe, $categories);
                if ($products !== null) $this->repository->syncProducts($recipe, $products);
                if ($structure !== null) $this->structureService->sync($recipe, $structure['groups'] ?? []);
                if ($stepData !== null) $this->syncSteps($recipe, $owner, is_array($stepData) ? $stepData : [], $placed);
                if ($seoData !== []) {
                    $this->seo->update($this->seo->firstOrCreate($recipe), [
                        'title' => $seoData['meta_title'] ?? null,
                        'description' => $seoData['meta_description'] ?? null,
                    ]);
                }
                return true;
            });
        } catch (\Throwable $e) {
            foreach ($placed as $path) Storage::disk('public')->delete($path);
            throw $e;
        }
    }

    /** @param list<string> $placed */
    private function syncSteps(Recipe $recipe, User $owner, array $steps, array &$placed): void
    {
        $keepIds = [];
        foreach (array_values($steps) as $index => $step) {
            $stepId = (int) ($step['id'] ?? 0);
            $attributes = [
                'step_number' => $index + 1,
                'description' => $step['description'] ?? null,
                'duration_seconds' => $step['duration_seconds'] ?? null,
            ];

            if ($stepId > 0) {
                $model = $this->steps->findForRecipe($recipe, $stepId);
                if (!$model) {
                    throw new \RuntimeException('В структуре рецепта обнаружен чужой шаг.');
                }
            } else {
                $model = $this->steps->create($recipe, $attributes);
            }

            if (!empty($step['image'])) {
                $path = $this->media->placeUpload($owner, $model, (string) $step['image'], 'step_image');
                $placed[] = $path;
                $attributes['image'] = $path;
            } elseif (($step['image_remove'] ?? false) === true || ($step['image_remove'] ?? null) === '1') {
                $attributes['image'] = null;
            }

            $this->steps->update($model, $attributes);
            $keepIds[] = $model->id;
        }
        $this->steps->deleteMissing($recipe, $keepIds);
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
        if (($data[$field.'_remove'] ?? false) === true || ($data[$field.'_remove'] ?? null) === '1') {
            $data[$field] = null;
            unset($data[$field.'_remove']);
            return;
        }
        if (empty($data[$field])) return;
        $path = $this->media->placeUpload($owner, $entity, (string) $data[$field], $slot);
        $placed[] = $path;
        $data[$field] = basename($path);
    }
}
