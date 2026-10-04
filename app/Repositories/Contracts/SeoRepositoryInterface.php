<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Polymorphic\Seo;
use Illuminate\Database\Eloquent\Model;

interface SeoRepositoryInterface
{

    /**
     * Queries or persists first or create for the seo repository interface repository.
     *
     * @param Model $owner
     * @return Seo
     */
    public function firstOrCreate(Model $owner): Seo;

    /**
     * Persists changes to the existing model through the repository.
     *
     * @param Seo $seo
     * @param array $attributes
     * @return bool
     */
    public function update(Seo $seo, array $attributes): bool;
}
