<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Polymorphic\Seo;
use App\Repositories\Contracts\SeoRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

final class SeoRepository implements SeoRepositoryInterface
{

    /**
     * Queries or persists first or create for the seo repository repository.
     *
     * @param Model $owner
     * @return Seo
     */
    public function firstOrCreate(Model $owner): Seo
    {
        return $owner->seo()->firstOrCreate([]);
    }

    /**
     * Persists changes to the existing model through the repository.
     *
     * @param Seo $seo
     * @param array $attributes
     * @return bool
     */
    public function update(Seo $seo, array $attributes): bool
    {
        return $seo->fill($attributes)->save();
    }
}
