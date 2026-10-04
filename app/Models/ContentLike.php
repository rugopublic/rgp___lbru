<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

final class ContentLike extends Model
{
    protected $table = 'content_likes';

    protected $fillable = [
        'user_id',
        'likeable_type',
        'likeable_id',
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
     * Handles likeable for the content like.
     * @return MorphTo
     */
    public function likeable(): MorphTo
    {
        return $this->morphTo();
    }
}
