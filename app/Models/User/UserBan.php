<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBan extends Model
{
    protected $fillable = [
        'user_id',
        'issued_by',
        'reason',
        'expires_at',
        'revoked_at',
        'revoked_by',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
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
     * Returns the issuer relationship.
     * @return BelongsTo
     */
    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    /**
     * Returns the revoker relationship.
     * @return BelongsTo
     */
    public function revoker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }

    /**
     * Проверяет, активен ли бан (не отозван и не истёк).
     */
    public function isActive(): bool
    {
        return is_null($this->revoked_at) && 
               (is_null($this->expires_at) || $this->expires_at->isFuture());
    }
}