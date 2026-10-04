<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

final class ContentBookmark extends Model
{
    protected $table = 'content_bookmarks';

    protected $fillable = [
        'user_id',
        'bookmarkable_type',
        'bookmarkable_id',
    ];

    /**
     * Returns the user relationship.
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Handles bookmarkable for the content bookmark.
     * @return MorphTo
     */
    public function bookmarkable(): MorphTo
    {
        return $this->morphTo();
    }
}
