<?php
/*
|--------------------------------------------------------------------------
| Cookie Consent Management
|--------------------------------------------------------------------------
|
| These routes implement explicit cookies consent handling for guest and
| authenticated users. The system distinguishes between required,
| functional, and marketing cookies, and ensures consent is stored
| server-side and reflected in a "_rq_cookie_consent" cookies.
|
| 1. GET /cookies
|    - Returns the current consent state of the visitor.
|    - Accessible by all users (guest or authenticated).
|    - Returns JSON structure or a banner payload for frontend JS.
|    - Consent fields:
|        • required (always true)
|        • functional (true/false/null)
|        • marketing (true/false/null)
|
| 2. POST /cookies
|    - Accepts consent preferences from the user.
|    - Accessible by all users (guest or authenticated).
|    - Request payload example:
|        {
|          "functional": true,
|          "marketing": false
|        }
|    - Updates the "_rq_cookie_consent" cookies.
|    - For authenticated users, optionally syncs with
|      user_cookie_settings in database.
|    - Response: 200 OK with updated consent JSON.
|
| Security and UX considerations:
|    - Consent cookies is required-cookies, HttpOnly=false for JS reading.
|    - Consent versioning can be implemented for re-consent flows.
|    - POST requests should be CSRF-protected.
|
*/

use App\Http\Controllers\Public\CookieController;
use Illuminate\Support\Facades\Route;

// Get current consent state
Route::get('/', [CookieController::class, 'settings'])
    ->name('settings');

// Set/update consent
Route::post('/', [CookieController::class, 'update'])
    ->name('update');
