<?php

namespace App\Models\Register;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Random\RandomException;

class RegisterEmailToken extends Model
{
    use HasFactory;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'email';

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The data type of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['email', 'token', 'created_at', 'agreement_accepted_at', 'agreement_version', 'privacy_version', 'acceptance_ip', 'acceptance_user_agent'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Check if the token has expired.
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        if (!$this->created_at) {
            return true;
        }

        $ttl = (int) config('register.email_token_ttl', 86400);

        return $this->created_at->addSeconds($ttl)->isPast();
    }

    /**
     * Generate a cryptographically secure, one-time token for password reset links.
     *
     * @param int $length Desired token length (default 64 characters)
     *
     * @throws RandomException If the system's CSPRNG fails
     */
    public function generate(int $length = 64): void
    {
        $this->token = bin2hex(random_bytes($length / 2));
    }

    /**
     * Mark this token as used by deleting it.
     */
    public function consume(): void
    {
        $this->delete();
    }
}
