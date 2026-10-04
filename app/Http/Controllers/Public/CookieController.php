<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\UpdateCookieConsentRequest;
use App\Services\Cookie\CookieConsentService;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

/**
 * Class CookieController
 *
 * Handles the cookies management for visitors:
 * - Displays the cookies consent settings page for modifications.
 * - Updates the cookies consent settings for a given visitor.
 *
 * All business logic (cookies settings update) is delegated to
 * dedicated service classes. The controller only provides HTTP
 * endpoints and response handling.
 */
class CookieController extends Controller
{
    /**
     * The cookie consent service for visitors.
     *
     * @var CookieConsentService
     */
    private CookieConsentService $service;

    /**
     * Initializes the cookie controller instance and wires its required dependencies.
     *
     * @param CookieConsentService $service
     * @return void
     */
    public function __construct(CookieConsentService $service)
    {
        $this->service = $service;
    }

    /**
     * Renders the cookie settings page for a visitor.
     *
     * @return View
     */
    public function settings(): View
    {
        $settings = (auth()->check())
            ? $this->service->readForUser(auth()->user())
            : $this->service->readForGuest(Cookie::get('_rq_cookie_consent'));

        return view('frontend.cookies.settings', [
            'settings' => $settings
        ]);
    }

    /**
     * Updates the visitor's cookie consent settings.
     *
     * Validates the incoming request via UpdateCookieConsentRequest, normalizes
     * the values, and delegates the update to the CookieConsentService.
     *
     * For authenticated users, updates are saved in the database.
     * For guests, the normalized settings are stored in an encrypted cookie.
     *
     * @param UpdateCookieConsentRequest $request
     *
     * @return View
     */
    public function update(UpdateCookieConsentRequest $request): View
    {
        $settings = $request->safe()->only(['required', 'functional', 'marketing']);

        try {
            $consents = (auth()->check())
                ? $this->service->updateForUser(auth()->user(), $settings)
                : $this->service->updateForGuest($settings);

            Cookie::queue(
                '_rq_cookie_consent',
                json_encode($consents),
                525600, // minutes → 1 year
                '/',     // path
                null,    // domain (default current)
                true,    // secure (HTTPS only)
                true,    // httpOnly
                false,   // raw
                'Strict' // SameSite
            );
        } catch(Throwable $e) {
            Log::error('An unexpected error occurred during cookie update.', ['error' => $e]);
            return view('frontend.cookies.error');
        }

        // We'll set the cookie whether this is a guest or a user.
        return view('frontend.cookies.updated', [
            'settings' => $consents
        ]);
    }
}
