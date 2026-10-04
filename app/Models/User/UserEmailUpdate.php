<?php

namespace App\Models\User;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Random\RandomException;

/**
 * A model for a user's email update status.
 *
 * @package App\Models\User
 *
 * @property int $user_id Primary key. An ID of a user that updates the email.
 * @property string $email A new email that will be set after the update completes.
 * @property string $token A token to verify a new email.
 * @property Carbon|null $expires_at Expiration timestamp for the token.
 * @property Carbon|null $verified_at A timestamp when a new email got verified.
 * @property Carbon|null $created_at A timestamp when this row got added to the table.
 * @property Carbon|null $updated_at A timestamp when this row got last update in the table.
 */
class UserEmailUpdate extends Model
{
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'user_id';

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['email', 'token', 'expires_at', 'verified_at'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    /**
     * Get the user that updates the email.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check whether the new email has been verified.
     *
     * @return bool
     */
    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    /**
     * Check whether the email verification token has expired.
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /**
     * Mark the email as verified if the token has not expired.
     *
     * @param bool $leaveDirty Whether to save the model after this change. Defaults to false.
     *
     * @return bool
     */
    public function verify(bool $leaveDirty = false): bool
    {
        if ($this->isExpired() || $this->isVerified()) {
            return false;
        }

        $this->verified_at = now();

        if(!$leaveDirty) {
            // Persistence is owned by UserEmailUpdateRepository.
        }

        return true;
    }

    /**
     * Generate a random token for email verification.
     *
     * @param int $length
     *
     * @return string
     *
     * @throws RandomException
     */
    protected static function generateToken(int $length = 64): string
    {
        return bin2hex(random_bytes($length / 2));
    }

    /**
     * Get or generate a token for email verification link.
     *
     * @param bool $renew Whether to prolong current (if true) or generate new (if false) token. Defaults to false.
     * @param bool $force Whether to re-generate the token even if the current one didn't yet expire. Defaults to false.
     * @param bool $leaveDirty Whether to save the model after this change. Defaults to true.
     *
     * @return string
     *
     * @throws RandomException
     */
    public function makeToken(
        bool $renew = false,
        bool $force = false,
        bool $leaveDirty = true
    ): string
    {
        if (!$force && !$renew && $this->token && !$this->isExpired()) {
            return $this->token;
        }

        if(!$renew || $this->isExpired()) {
            $this->token = self::generateToken();
        }

        $this->expires_at = now()->addSeconds((int) config('user.email_token_ttl'));

        if(!$leaveDirty) {
            // Persistence is owned by UserEmailUpdateRepository.
        }

        return $this->token;
    }
}
