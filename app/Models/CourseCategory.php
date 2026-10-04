<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourseCategory extends Model
{
    use SoftDeletes;

    protected $fillable = ['public_id','name','slug','description','parent_id','image','is_active'];

    /**
     * Handles casts for the course category.
     * @return array
     */
    protected function casts(): array { return ['is_active'=>'boolean']; }

    /**
     * Handles booted for the course category.
     * @return void
     */
    protected static function booted(): void { static::creating(fn (self $category) => $category->public_id ??= random_public_id()); }

    /**
     * Returns route key name.
     * @return string
     */
    public function getRouteKeyName(): string { return 'public_id'; }

    /**
     * Returns the parent relationship.
     * @return BelongsTo
     */
    public function parent(): BelongsTo { return $this->belongsTo(self::class, 'parent_id'); }

    /**
     * Returns the children relationship.
     * @return HasMany
     */
    public function children(): HasMany { return $this->hasMany(self::class, 'parent_id')->orderBy('name'); }

    /**
     * Returns the courses relationship.
     * @return HasMany
     */
    public function courses(): HasMany { return $this->hasMany(Course::class, 'category_id'); }
}
