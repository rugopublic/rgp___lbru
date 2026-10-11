<?php

declare(strict_types=1);

namespace App\Services\Admin\Course;

use App\Enums\Material\PublishingStatus;
use App\Models\User;
use App\Repositories\Contracts\CourseRepositoryInterface;
use App\Repositories\Contracts\SeoRepositoryInterface;
use App\Services\Admin\Content\ContentMediaService;
use App\Services\Admin\Content\RichTextMediaService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

final class CourseService
{

    /**
     * Initializes the course service instance and wires its required dependencies.
     *
     * @param CourseRepositoryInterface $repository
     * @param SeoRepositoryInterface $seo
     * @param ContentMediaService $media
     * @param RichTextMediaService $richText
     * @return void
     */
    public function __construct(
        private readonly CourseRepositoryInterface $repository,
        private readonly SeoRepositoryInterface $seo,
        private readonly ContentMediaService $media,
        private readonly RichTextMediaService $richText,
    ) {
    }

    /**
     * Creates the domain resource and applies the required business orchestration.
     *
     * @param array $attributes
     * @return Model
     */
    public function create(array $attributes): Model
    {
        return $this->repository->create($attributes);
    }

    /**
     * Updates the domain resource and applies the required business orchestration.
     *
     * @param Model $course
     * @param User $owner
     * @param Collection $incoming
     * @return bool
     */
    public function update(Model $course, User $owner, Collection $incoming): bool
    {
        $data = $incoming->all();
        $placed = [];

        try {
            foreach (['pitch', 'objective', 'skills'] as $field) {
                if (!array_key_exists($field, $data)) {
                    continue;
                }

                $resolved = $this->richText->resolvePendingImages($owner, $course, $data[$field]);
                $data[$field] = $resolved['html'];
                $placed = array_merge($placed, $resolved['paths']);
            }

            if (array_key_exists('cover', $data)) {
                $this->placeCover($course, $owner, $data, $placed);
            }

            $ok = DB::transaction(function () use ($course, $data): bool {
                $seoData = array_intersect_key($data, array_flip(['meta_title', 'meta_description']));
                unset($data['meta_title'], $data['meta_description']);

                if (isset($data['publish_status']) && $data['publish_status'] !== PublishingStatus::Scheduled->value) {
                    $data['publish_date'] = null;
                }

                $saved = $this->repository->update($course, $data);
                if (!$saved) return false;

                if ($seoData !== []) {
                    $seoModel = $this->seo->firstOrCreate($course);
                    $this->seo->update($seoModel, [
                        'title' => $seoData['meta_title'] ?? null,
                        'description' => $seoData['meta_description'] ?? null,
                    ]);
                }

                return true;
            });

            if (!$ok) throw new RuntimeException('Курс не сохранён.');
            return true;
        } catch (\Throwable $e) {
            foreach ($placed as $path) Storage::disk('public')->delete($path);
            throw $e;
        }
    }

    /**
     * Places cover into permanent storage using the media lifecycle rules.
     *
     * @param Model $course
     * @param User $owner
     * @param array $data
     * @param array $placed
     * @return void
     */
    private function placeCover(Model $course, User $owner, array &$data, array &$placed): void
    {
        $remove = filter_var($data['cover_remove'] ?? false, FILTER_VALIDATE_BOOLEAN);
        if ($remove) {
            $data['cover'] = null;
            unset($data['cover_remove']);
            return;
        }

        if (empty($data['cover'])) return;

        $path = $this->media->placeUpload($owner, $course, (string) $data['cover'], 'cover');
        $placed[] = $path;
        $data['cover'] = basename($path);
        unset($data['cover_remove']);
    }
}
