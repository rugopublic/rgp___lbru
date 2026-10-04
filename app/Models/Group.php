<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Group extends Model
{
    use SoftDeletes;
    protected $fillable = ['public_id', 'name'];

    /**
     * Handles booted for the group.
     * @return void
     */
    protected static function booted(): void { static::creating(fn(self $group) => $group->public_id ??= random_public_id()); }

    /**
     * Returns route key name.
     * @return string
     */
    public function getRouteKeyName(): string { return 'public_id'; }

    /**
     * Returns the users relationship.
     * @return BelongsToMany
     */
    public function users(): BelongsToMany { return $this->belongsToMany(User::class, 'group_user')->withTimestamps(); }
}
