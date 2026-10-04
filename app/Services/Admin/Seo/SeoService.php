<?php

namespace App\Services\Admin\Seo;

use App\Models\Polymorphic\Seo;
use App\Repositories\Contracts\SeoRepositoryInterface;
use Illuminate\Support\Collection;

/**
 * Class SeoService
 *
 * Handles the full lifecycle of SEO field management:
 *  - `update`: Updates the SEO fields of the given instance.
 *
 * This service encapsulates all the logic related to the SEO fields management,
 * keeping controllers thin and focused on HTTP layer responsibilities.
 */
class SeoService
{

    /**
     * Initializes the seo service instance and wires its required dependencies.
     *
     * @param SeoRepositoryInterface $repository
     * @return void
     */
    public function __construct(private readonly SeoRepositoryInterface $repository) {}
    /**
     * Replaces the current information about the given SEO instance with
     * the one provided to this method.
     *
     * @param Seo                       $seo    The SEO instance to be processed.
     * @param Collection<string, mixed> $data   The data to replace the current one with.
     *
     * @return bool Whether the replacement succeeded.
     */
    public function update(Seo $seo, Collection $data): bool
    {
        return $this->repository->update($seo, array_filter([
            'title' => $data->has('meta_title') ? $data->get('meta_title') : null,
            'description' => $data->has('meta_description') ? $data->get('meta_description') : null,
        ], static fn ($value, string $key): bool => $data->has($key === 'title' ? 'meta_title' : 'meta_description')));
    }
}
