<?php

namespace App\Models\User;

use App\Models\User;
use DomainException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Class UserCookieSettings
 *
 * This model manages the user's cookies preferences.
 * This is important for strict compliance with certain
 * laws and regulations such as EU's GDPR, US's CCPA, or
 * Russia's 152-FZ.
 *
 * @property int $user_id
 * @property Carbon|null $required_consent_at
 * @property Carbon|null $functional_consent_at
 * @property Carbon|null $marketing_consent_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class UserCookieSettings extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_cookie_settings';

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
    protected $fillable = [
        'user_id',
        'required_consent_at',
        'functional_consent_at',
        'marketing_consent_at'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'required_consent_at' => 'datetime',
        'functional_consent_at' => 'datetime',
        'marketing_consent_at' => 'datetime',
    ];

    /**
     * Get the user that was using the email.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check whether this user gave consent for required cookies.
     *
     * @return bool
     */
    public function requiredOk(): bool
    {
        return $this->required_consent_at !== null;
    }

    /**
     * Check whether this user gave consent for functional cookies.
     *
     * @return bool
     */
    public function functionalOk(): bool
    {
        return $this->functional_consent_at !== null;
    }

    /**
     * Check whether this user gave consent for marketing cookies.
     *
     * @return bool
     */
    public function marketingOk(): bool
    {
        return $this->marketing_consent_at !== null;
    }

    /**
     * Record the user's consent for required cookies.
     *
     * @return self
     */
    public function giveRequiredConsent(): self
    {
        $this->required_consent_at = Carbon::now();

        return $this;
    }

    /**
     * Record the user's consent for functional cookies.
     *
     * @return self
     */
    public function giveFunctionalConsent(): self
    {
        $this->functional_consent_at = Carbon::now();

        return $this;
    }

    /**
     * Record the user's consent for marketing cookies.
     *
     * @return self
     */
    public function giveMarketingConsent(): self
    {
        $this->marketing_consent_at = Carbon::now();

        return $this;
    }

    /**
     * Revokes the user's consent for required cookies.
     *
     * @return void
     *
     * @throws DomainException
     */
    public function revokeRequiredConsent(): void
    {
        // As per the current laws and regulations, the user consent for
        // the required cookies cannot be revoked.
        throw new DomainException(
            'Required cookie consent cannot be revoked by domain rules.'
        );

        // $this->required_consent_at = null;

        // return $this;
    }

    /**
     * Revokes the user's consent for functional cookies.
     *
     * @return self
     */
    public function revokeFunctionalConsent(): self
    {
        $this->functional_consent_at = null;

        return $this;
    }

    /**
     * Revokes the user's consent for marketing cookies.
     *
     * @return self
     */
    public function revokeMarketingConsent(): self
    {
        $this->marketing_consent_at = null;

        return $this;
    }
}
