<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

final class ContentComment extends Model
{
    protected $table = 'content_comments';

    protected $fillable = [
        'user_id',
        'commentable_type',
        'commentable_id',
        'body',
        'status',
    ];

    /**
     * Handles casts for the content comment.
     * @return array
     */
    protected function casts(): array
    {
        return ['status' => 'string'];
    }

    /**
     * Returns the user relationship.
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Handles commentable for the content comment.
     * @return MorphTo
     */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }
}
