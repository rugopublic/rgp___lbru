<?php
/*
|--------------------------------------------------------------------------
| Login & Logout Flow
|--------------------------------------------------------------------------
|
| These routes implement a secure and explicit authentication cycle:
|
| 1. GET /auth/login
|    - Displays the login form (email + password).
|    - Accessible by guests only.
|    - If the user is already authenticated:
|        • Redirects to the intended page or user dashboard.
|    - Displays validation errors for:
|        • Invalid email format.
|        • Missing credentials.
|
| 2. POST /auth/login
|    - Accepts credentials validated via FormRequest (e.g., LoginRequest).
|    - Attempts to authenticate the user using email and password.
|    - Possible outcomes:
|        • Invalid credentials → shows a neutral error message.
|        • User not found → same neutral error message.
|        • User inactive / deleted / blocked (if applicable) → access denied message.
|    - Does NOT reveal whether the email exists.
|    - On successful authentication:
|        • Regenerates the session ID.
|        • Redirects to the intended URL or default dashboard.
|
| 3. POST /auth/logout
|    - Logs out the currently authenticated user.
|    - Accessible by authenticated users only.
|    - Performs:
|        • Auth logout.
|        • Session invalidation.
|        • CSRF token regeneration.
|    - Redirects the user to the public home page or login page.
|
| Security and UX considerations:
|    - Login errors never disclose whether an account exists.
|    - Session fixation is prevented via session regeneration.
|    - Logout is CSRF-protected and unavailable to guests.
|    - Designed for future extension (rate limiting, 2FA, audit logs).
|
*/

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'form'])
        ->name('login.form');
    Route::post('login', [LoginController::class, 'attempt'])
        ->name('login.attempt');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'logout'])
        ->name('logout');
});
