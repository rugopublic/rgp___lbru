<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReferenceCategory extends Model
{
    use SoftDeletes;
    protected $table = 'ingredient_categories';
    protected $fillable = ['public_id','name','slug','description','parent_id','image','is_active'];

    /**
     * Handles casts for the reference category.
     * @return array
     */
    protected function casts(): array { return ['is_active'=>'boolean']; }

    /**
     * Handles booted for the reference category.
     * @return void
     */
    protected static function booted(): void { static::creating(fn (self $model) => $model->public_id ??= random_public_id()); }

    /**
     * Returns route key name.
     * @return string
     */
    public function getRouteKeyName(): string { return 'public_id'; }

    /**
     * Returns the items relationship.
     * @return HasMany
     */
    public function items(): HasMany { return $this->hasMany(ReferenceItem::class, 'category_id'); }
}
