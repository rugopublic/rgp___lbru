<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\ContentMediaObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mews\Purifier\Casts\CleanHtmlInput;

#[ObservedBy([ContentMediaObserver::class])]
class Recipe extends Model
{
    use SoftDeletes;
    protected $fillable = ['public_id','title','slug','description','body','cover','video','author_id','visibility_status','publish_at'];

    /**
     * Handles casts for the recipe.
     * @return array
     */
    protected function casts(): array { return ['body'=>CleanHtmlInput::class,'publish_at'=>'datetime']; }

    /**
     * Handles booted for the recipe.
     * @return void
     */
    protected static function booted(): void { static::creating(function(self $m){$m->public_id ??= random_public_id(); $m->slug ??= unique_slug($m->title,'recipes');}); }

    /**
     * Returns route key name.
     * @return string
     */
    public function getRouteKeyName(): string { return 'public_id'; }

    /**
     * Returns the category relationship.
     * @return BelongsTo
     */
    public function category(): BelongsTo { return $this->belongsTo(RecipeCategory::class,'category_id'); }

    /**
     * Returns the categories relationship.
     * @return BelongsToMany
     */
    public function categories(): BelongsToMany { return $this->belongsToMany(RecipeCategory::class,'recipe_category','recipe_id','category_id'); }

    /**
     * Returns the author relationship.
     * @return BelongsTo
     */
    public function author(): BelongsTo { return $this->belongsTo(User::class,'author_id'); }

    /**
     * Returns the ingredient groups relationship.
     * @return HasMany
     */
    public function ingredientGroups(): HasMany { return $this->hasMany(RecipeIngredientGroup::class)->orderBy('sort_order'); }

    /**
     * Returns the ingredients relationship.
     * @return HasMany
     */
    public function ingredients(): HasMany { return $this->hasMany(RecipeIngredient::class)->orderBy('sort_order'); }

    /**
     * Returns the products relationship.
     * @return BelongsToMany
     */
    public function products(): BelongsToMany { return $this->belongsToMany(Product::class,'recipe_product')->withPivot('sort_order')->orderByPivot('sort_order'); }

    /**
     * Returns the steps relationship.
     * @return HasMany
     */
    public function steps(): HasMany { return $this->hasMany(RecipeStep::class)->orderBy('step_number'); }

    /**
     * Returns the files relationship.
     * @return HasMany
     */
    public function files(): HasMany { return $this->hasMany(RecipeFile::class); }

    /**
     * Handles media folder for the recipe.
     * @return string
     */
    public function mediaFolder(): string { return "recipes/{$this->public_id}"; }

    /**
     * Returns the cover url accessor value.
     * @return ?string
     */
    public function getCoverUrlAttribute(): ?string {
        if (!$this->cover) return null;
        $path = str_contains($this->cover, '/') ? $this->cover : $this->mediaFolder().'/'.$this->cover;
        return \Storage::disk('public')->url($path);
    }

    /**
     * Returns the video url accessor value.
     * @return ?string
     */
    public function getVideoUrlAttribute(): ?string {
        if (!$this->video) return null;
        $path = str_contains($this->video, '/') ? $this->video : $this->mediaFolder().'/'.$this->video;
        return \Storage::disk('public')->url($path);
    }
}
