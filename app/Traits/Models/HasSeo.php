<?php

namespace App\Traits\Models;

use App\Models\Polymorphic\Seo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasSeo
{
    /**
      * Get the model instance's SEO data.
      *
      * @return MorphOne The SEO data for this instance of a model.
      */
    public function seo(): MorphOne
    {
        return $this->morphOne(Seo::class, 'seoable');
    }
}
