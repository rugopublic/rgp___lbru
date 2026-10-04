<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Auth\LoginService;
use App\Services\Cookie\CookieConsentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

/**
 * Class LoginController
 *
 * Handles the full authentication flow for visitors:
 * - Allows guests to log in
 * - Allows authenticated users to log out
 *
 * All business logic (the actual authentication) is delegated to
 * dedicated service class. The controller only provides HTTP
 * endpoints and response handling.
 */
class LoginController extends Controller
{
    /**
     * The cookie consent service for visitors.
     *
     * @var LoginService
     */
    private LoginService $service;

    /**
     * The cookie consent service for visitors.
     *
     * @var CookieConsentService
     */
    private CookieConsentService $cookieService;

    /**
     * Initializes the login controller instance and wires its required dependencies.
     *
     * @param LoginService $service
     * @param CookieConsentService $cookieService
     * @return void
     */
    public function __construct(LoginService $service, CookieConsentService $cookieService)
    {
        $this->service = $service;
        $this->cookieService = $cookieService;
    }

    /**
     * Displays the login form page for a guest.
     *
     * @return View
     */
    public function form(): View
    {
        return view('frontend.auth.login');
    }

    /**
     * Attempts to authenticate the user with provided credentials.
     *
     * @param LoginRequest $request
     *
     * @return RedirectResponse
     */
    public function attempt(LoginRequest $request): RedirectResponse
    {
        $cookieSettings = $this->cookieService->readForGuest(Cookie::get('_rq_cookie_consent'));

        // Check if functional cookies are allowed for this user in order to use 'Remember Me' function.
        $functionalCookiesAllowed = $cookieSettings['functional'];

        $email = $request->validated('email');
        $password = $request->validated('password');
        $remember = $functionalCookiesAllowed && $request->validated('remember');

        try {
            $user = $this->service->login($email, $password);

            Auth::login($user, $remember);
            session()->regenerate();
        } catch (Throwable $e) {
            Log::error('Authentication attempt failed:', ['error' => $e]);
            return redirect()->back()->withErrors(__('auth.failed'));
        }

        // On successful login, it's important to synchronize cookie settings.
        $this->cookieService->sync($user, $cookieSettings);

        $redirect = trim((string) $request->validated('redirect'));
        if ($redirect !== '' && $this->isSafeRedirect($redirect)) {
            return redirect()->to($redirect);
        }

        return redirect()->intended(route('user.account.index'));
    }

    /**
     * Handles the is safe redirect HTTP action for the login controller resource.
     *
     * @param string $redirect
     * @return bool
     */
    private function isSafeRedirect(string $redirect): bool
    {
        $candidate = parse_url($redirect);
        $current = parse_url(url('/'));

        if ($candidate === false || $current === false) {
            return false;
        }

        if (!isset($candidate['host'])) {
            return str_starts_with($redirect, '/');
        }

        return isset($current['host'])
            && strcasecmp((string) $candidate['host'], (string) $current['host']) === 0
            && (($candidate['scheme'] ?? $current['scheme'] ?? null) === ($current['scheme'] ?? null));
    }

    /**
     * Attempts to de-authenticate the currently authenticated user.
     *
     * @return RedirectResponse
     */
    public function logout(): RedirectResponse
    {
        try {
            $this->service->logout(auth()->user());

            Auth::logout();
            session()->invalidate();
            session()->regenerateToken();
        } catch (Throwable $e) {
            Log::error('Logout attempt failed:', ['error' => $e]);
            return redirect()->back()->withErrors(__('auth.logout.failed'));
        }

        return redirect()->route('home');
    }
}
