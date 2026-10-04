<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecipeCategory extends Model
{
    use SoftDeletes;
    protected $fillable = ['public_id','name','slug','description','parent_id','image','is_active'];

    /**
     * Handles casts for the recipe category.
     * @return array
     */
    protected function casts(): array { return ['is_active'=>'boolean']; }

    /**
     * Handles booted for the recipe category.
     * @return void
     */
    protected static function booted(): void { static::creating(fn (self $model) => $model->public_id ??= random_public_id()); }

    /**
     * Returns route key name.
     * @return string
     */
    public function getRouteKeyName(): string { return 'public_id'; }

    /**
     * Returns the recipes relationship.
     * @return BelongsToMany
     */
    public function recipes(): BelongsToMany { return $this->belongsToMany(Recipe::class, 'recipe_category', 'category_id', 'recipe_id'); }
}
