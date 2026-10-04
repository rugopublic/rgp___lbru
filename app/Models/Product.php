<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Polymorphic\Seo;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Observers\ContentMediaObserver;

#[ObservedBy([ContentMediaObserver::class])]
class Product extends Model
{
    use SoftDeletes;
    protected $fillable = ['public_id','title','description','category_id','file','cover','price','is_visible'];

    /**
     * Handles casts for the product.
     * @return array
     */
    protected function casts(): array { return ['price'=>'decimal:2','is_visible'=>'boolean']; }

    /**
     * Handles booted for the product.
     * @return void
     */
    protected static function booted(): void { static::creating(fn(self $product) => $product->public_id ??= random_public_id()); }

    /**
     * Returns route key name.
     * @return string
     */
    public function getRouteKeyName(): string { return 'public_id'; }

    /**
     * Returns the category relationship.
     * @return BelongsTo
     */
    public function category(): BelongsTo { return $this->belongsTo(ProductCategory::class,'category_id'); }

    /**
     * Returns the purchases relationship.
     * @return HasMany
     */
    public function purchases(): HasMany { return $this->hasMany(ProductPurchase::class); }

    /**
     * Returns the articles relationship.
     * @return BelongsToMany
     */
    public function articles(): BelongsToMany { return $this->belongsToMany(Article::class, 'article_product')->withPivot('sort_order')->orderByPivot('sort_order'); }

    /**
     * Returns the recipes relationship.
     * @return BelongsToMany
     */
    public function recipes(): BelongsToMany { return $this->belongsToMany(Recipe::class, 'recipe_product')->withPivot('sort_order')->orderByPivot('sort_order'); }

    /**
     * Returns the reference items relationship.
     * @return BelongsToMany
     */
    public function referenceItems(): BelongsToMany { return $this->belongsToMany(ReferenceItem::class, 'reference_item_product', 'product_id', 'reference_item_id')->withPivot('sort_order')->orderByPivot('sort_order'); }

    /**
     * Returns the seo relationship.
     * @return MorphOne
     */
    public function seo(): MorphOne { return $this->morphOne(Seo::class, 'seoable'); }

    /**
     * Handles media folder for the product.
     * @return string
     */
    public function mediaFolder(): string { return "products/{$this->public_id}"; }

    /**
     * Returns the file url accessor value.
     * @return ?string
     */
    public function getFileUrlAttribute(): ?string { return null; }

    /**
     * Handles media disk for the product.
     *
     * @param string $field
     * @return string
     */
    public function mediaDisk(string $field): string { return $field === 'file' ? (string) config('files.product_disk', 'local') : 'public'; }

    /**
     * Handles media disks for the product.
     * @return array
     */
    public function mediaDisks(): array { return array_values(array_unique([(string) config('files.product_disk', 'local'), 'public'])); }

    /**
     * Handles file name for the product.
     * @return ?string
     */
    public function fileName(): ?string { return $this->file ? basename($this->file) : null; }

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
