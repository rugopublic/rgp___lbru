<?php

namespace App\Models\Polymorphic;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Seo extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title', 'description'
    ];

    /**
      * Get the parent seoable model.
      *
      * @return MorphTo The model related to this SEO data.
      */
    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }
}
