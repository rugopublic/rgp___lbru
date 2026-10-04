<?php

namespace App\Services\Cookie;

use App\Models\User;
use App\Models\User\UserCookieSettings;
use App\Repositories\Contracts\UserCookieSettingsRepositoryInterface;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Class CookieConsentService
 *
 * Handles the cookie consent options for a visitor:
 *  - `read`: Compiles an array of the current cookie settings for a visitor.
 *  - `update`: Updates the visitor's cookie settings with the provided ones.
 *
 * This service encapsulates all the logic related to the cookie consent settings,
 * keeping controllers thin and focused on HTTP layer responsibilities.
 */
class CookieConsentService
{

    /**
     * Initializes the cookie consent service instance and wires its required dependencies.
     *
     * @param UserCookieSettingsRepositoryInterface $settings
     * @return void
     */
    public function __construct(private readonly UserCookieSettingsRepositoryInterface $settings) {}
    /**
     * A default value for 'required cookies' consent.
     *
     * @var bool|null
     */
    public const DEFAULT_REQUIRED   = true;

    /**
     * A default value for 'functional cookies' consent.
     *
     * @var bool|null
     */
    public const DEFAULT_FUNCTIONAL = null;

    /**
     * A default value for 'marketing cookies' consent.
     *
     * @var bool|null
     */
    public const DEFAULT_MARKETING  = null;

    /**
     * Initializes cookie settings relationship for a given user if it doesn't exist.
     *
     * @param User $user
     *
     * @return void
     */
    public function initializeUserCookieSettings(User $user): void
    {
        // Do nothing if cookie settings have already been set up.
        if($this->settings->existsForUser((int) $user->id)) {
            return;
        }

        // Initialize cookie settings for a given user.
        try {
            $this->settings->create([
                'user_id' => $user->id,
                'required_consent_at' => Carbon::now(),
                'functional_consent_at' => null,
                'marketing_consent_at' => null,
            ]);
        } catch(Throwable $e) {
            Log::error("Failed to create cookie policy relationship for user $user->id: {$e->getMessage()}");
        }
    }

    /**
     * Compiles an array of the current cookie preferences for a guest.
     * The service expects a cookie from Cookie::get or other sources.
     *
     * @param string|null $cookie
     *
     * @return array
     */
    public function readForGuest(?string $cookie): array
    {
        if($cookie === null)
            Log::info('Cookie consent not found for guest.');

        $consents = is_string($cookie)
            ? json_decode($cookie, true)
            : null;

        return [
            'required'      => $consents['required']    ?? self::DEFAULT_REQUIRED,
            'functional'    => $consents['functional']  ?? self::DEFAULT_FUNCTIONAL,
            'marketing'     => $consents['marketing']   ?? self::DEFAULT_MARKETING,
        ];
    }

    /**
     * Compiles an array of the current cookie preferences for a given user.
     *
     * @param User $user
     *
     * @return array
     */
    public function readForUser(User $user): array
    {
        $consents = $this->settings->findForUser((int) $user->id);

        return [
            'required'      => $consents?->requiredOk()      ?? self::DEFAULT_REQUIRED,
            'functional'    => $consents?->functionalOk()    ?? self::DEFAULT_FUNCTIONAL,
            'marketing'     => $consents?->marketingOk()     ?? self::DEFAULT_MARKETING,
        ];
    }

    /**
     * Prepares the cookie consent changes for insertion into the cookie
     * or into the database.
     *
     * @param array $settings
     *
     * @return array
     *
     * @throws DomainException
     */
    protected function prepareForUpdate(array $settings): array
    {
        // As per the current laws and regulations, the guest consent for
        // the required cookies cannot be revoked.
        if( array_key_exists('required', $settings) && !($settings['required']) ) {
            throw new DomainException(
                'Required cookie consent cannot be revoked by domain rules.'
            );
        }

        return [
            'required'   => array_key_exists('required', $settings)
                ? (bool) $settings['required']
                : self::DEFAULT_REQUIRED,
            'functional' => array_key_exists('functional', $settings)
                ? (bool) $settings['functional']
                : self::DEFAULT_FUNCTIONAL,
            'marketing'  => array_key_exists('marketing', $settings)
                ? (bool) $settings['marketing']
                : self::DEFAULT_MARKETING,
        ];
    }

    /**
     * Prepares the cookie consent changes to be inserted into the cookie for guests.
     * The insertion itself is in the responsibility of the controller.
     *
     * @param array $settings
     *
     * @return array
     */
    public function updateForGuest(array $settings): array
    {
        return $this->prepareForUpdate($settings);
    }

    /**
     * Applies the given cookie consent changes for the given user.
     *
     * @param User $user
     * @param array $settings
     *
     * @return array
     */
    public function updateForUser(User $user, array $settings): array
    {
        $this->initializeUserCookieSettings($user);

        $consents = $this->prepareForUpdate($settings);

        $settings = $this->settings->findForUser((int) $user->id);
        if (!$settings) {
            $this->initializeUserCookieSettings($user);
            $settings = $this->settings->findForUser((int) $user->id);
        }

        if (!$settings) {
            throw new \RuntimeException('Не удалось инициализировать настройки cookies пользователя.');
        }

        $now = Carbon::now();
        $attributes = [
            'required_consent_at' => $consents['required'] ? ($settings->required_consent_at ?? $now) : $settings->required_consent_at,
            'functional_consent_at' => $consents['functional'] ? ($settings->functional_consent_at ?? $now) : null,
            'marketing_consent_at' => $consents['marketing'] ? ($settings->marketing_consent_at ?? $now) : null,
        ];

        $this->settings->update($settings, $attributes);

        return $consents;
    }

    /**
     * Synchronizes the cookie settings for a given user.
     * This is a helper to differentiate the context of updating
     * cookies for a user and syncing the cookie settings from
     * guest cookie to user table.
     *
     * @param User $user
     * @param array $settings
     *
     * @return void
     */
    public function sync(User $user, array $settings): void
    {
        $this->updateForUser($user, $settings);
    }
}
