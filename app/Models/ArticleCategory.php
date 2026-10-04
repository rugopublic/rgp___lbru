<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ArticleCategory extends Model
{
    use SoftDeletes;
    protected $fillable = ['public_id','name','slug','description','parent_id','image','is_active'];

    /**
     * Handles casts for the article category.
     * @return array
     */
    protected function casts(): array { return ['is_active'=>'boolean']; }

    /**
     * Handles booted for the article category.
     * @return void
     */
    protected static function booted(): void { static::creating(fn (self $model) => $model->public_id ??= random_public_id()); }

    /**
     * Returns route key name.
     * @return string
     */
    public function getRouteKeyName(): string { return 'public_id'; }

    /**
     * Returns the articles relationship.
     * @return HasMany
     */
    public function articles(): HasMany { return $this->hasMany(Article::class, 'category_id'); }
}
