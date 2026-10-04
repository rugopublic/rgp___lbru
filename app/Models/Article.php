<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Polymorphic\Seo;
use App\Observers\ContentMediaObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mews\Purifier\Casts\CleanHtmlInput;

#[ObservedBy([ContentMediaObserver::class])]
class Article extends Model
{
    use SoftDeletes;
    protected $fillable = ['public_id','title','slug','description','body','cover','category_id','author_id','is_visible','publish_at'];

    /**
     * Handles casts for the article.
     * @return array
     */
    protected function casts(): array { return ['body'=>CleanHtmlInput::class,'is_visible'=>'boolean','publish_at'=>'datetime']; }

    /**
     * Handles booted for the article.
     * @return void
     */
    protected static function booted(): void { static::creating(function(self $m){$m->public_id ??= random_public_id(); $m->slug ??= unique_slug($m->title,'articles');}); }

    /**
     * Returns route key name.
     * @return string
     */
    public function getRouteKeyName(): string { return 'public_id'; }

    /**
     * Returns the category relationship.
     * @return BelongsTo
     */
    public function category(): BelongsTo { return $this->belongsTo(ArticleCategory::class,'category_id'); }

    /**
     * Returns the author relationship.
     * @return BelongsTo
     */
    public function author(): BelongsTo { return $this->belongsTo(User::class,'author_id'); }

    /**
     * Returns the products relationship.
     * @return BelongsToMany
     */
    public function products(): BelongsToMany { return $this->belongsToMany(Product::class,'article_product')->withPivot('sort_order')->orderByPivot('sort_order'); }

    /**
     * Returns the seo relationship.
     * @return MorphOne
     */
    public function seo(): MorphOne { return $this->morphOne(Seo::class,'seoable'); }

    /**
     * Handles media folder for the article.
     * @return string
     */
    public function mediaFolder(): string { return "articles/{$this->public_id}"; }

    /**
     * Returns the cover url accessor value.
     * @return ?string
     */
    public function getCoverUrlAttribute(): ?string {
        if (!$this->cover) return null;
        $path = str_contains($this->cover, '/') ? $this->cover : $this->mediaFolder().'/'.$this->cover;
        return \Storage::disk('public')->url($path);
    }
}
