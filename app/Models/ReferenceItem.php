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
class ReferenceItem extends Model
{
    use SoftDeletes;
    protected $table = 'ingredients';
    protected $fillable = ['public_id','title','description','body','cover','category_id','protein','fat','carbs','kcal','default_grammage','storage_conditions','selection_notes','is_visible'];

    /**
     * Handles casts for the reference item.
     * @return array
     */
    protected function casts(): array { return ['body'=>CleanHtmlInput::class,'protein'=>'decimal:2','fat'=>'decimal:2','carbs'=>'decimal:2','kcal'=>'decimal:2','default_grammage'=>'decimal:2','is_visible'=>'boolean']; }

    /**
     * Handles booted for the reference item.
     * @return void
     */
    protected static function booted(): void { static::creating(fn(self $m) => $m->public_id ??= random_public_id()); }

    /**
     * Returns route key name.
     * @return string
     */
    public function getRouteKeyName(): string { return 'public_id'; }

    /**
     * Returns the category relationship.
     * @return BelongsTo
     */
    public function category(): BelongsTo { return $this->belongsTo(ReferenceCategory::class,'category_id'); }

    /**
     * Returns the products relationship.
     * @return BelongsToMany
     */
    public function products(): BelongsToMany { return $this->belongsToMany(Product::class,'reference_item_product','reference_item_id','product_id')->withPivot('sort_order')->orderByPivot('sort_order'); }

    /**
     * Returns the ingredients relationship.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ingredients(): \Illuminate\Database\Eloquent\Relations\HasMany { return $this->hasMany(RecipeIngredient::class,'reference_item_id'); }

    /**
     * Returns the seo relationship.
     * @return MorphOne
     */
    public function seo(): MorphOne { return $this->morphOne(Seo::class,'seoable'); }

    /**
     * Handles media folder for the reference item.
     * @return string
     */
    public function mediaFolder(): string { return "reference/{$this->public_id}"; }

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
