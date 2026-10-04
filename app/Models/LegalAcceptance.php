<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class LegalAcceptance extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'agreement_version',
        'privacy_version',
        'accepted_at',
        'ip',
        'user_agent',
    ];

    /**
     * Handles casts for the legal acceptance.
     * @return array
     */
    protected function casts(): array
    {
        return ['accepted_at' => 'datetime'];
    }

    /**
     * Returns the user relationship.
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
