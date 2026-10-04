<?php
/*
|--------------------------------------------------------------------------
| Register Flow
|--------------------------------------------------------------------------
|
| These routes implement a secure, multi-step registration cycle for guest users:
|
| 1. GET /register
|    - Displays a form to enter the user's email.
|    - Accessible by guests only.
|    - Does not reveal whether an email is already registered.
|    - Performs email syntax validation and domain rules:
|        • If syntax is invalid → shows a clear error.
|        • If domain is forbidden → shows a clear error.
|
| 2. POST /register
|    - Accepts the email, validated via FormRequest (e.g., RegisterEmailRequest).
|    - If the email is free and valid:
|        • Creates a one-time registration token.
|        • Sends a confirmation email with a link to /register/{token}.
|    - If the email is already taken:
|        • Does NOT send any email.
|        • Responds with a neutral message:
|          "If you entered a valid email, check your inbox. If you already registered, you may restore your password."
|          - Prevents account enumeration.
|    - The response never reveals whether the account exists or whether the email delivery succeeded.
|
| 3. GET /register/{token}
|    - Checks the registration token:
|        • Valid and not expired → displays the password creation form.
|        • Expired → shows "link expired" view.
|        • Already used → shows "link invalid" view.
|        • Not found → shows "link invalid" view.
|
| 4. PUT /register
|    - Processes the submitted token and password (validated via RegisterCompleteRequest).
|    - Checks the token:
|        • Valid and not expired → creates the user, consumes the token, finalizes registration.
|        • Expired, already used, or invalid → shows "link invalid" view.
|    - On success:
|        • Renders 'web.frontend.user.register.complete'.
|        • Provides a button to proceed to the profile page, automatically logging in the user.
|
| Security and UX considerations:
|    - No explicit success/failure messages reveal whether an email is registered.
|    - Email syntax and forbidden domain errors are safe to display.
|    - Users are guided to restore password if they may already have an account.
|    - Provides a friendly UX even if email delivery fails.
|
*/

use App\Http\Controllers\Register\RegisterController;
use Illuminate\Support\Facades\Route;

Route::get('/', [RegisterController::class, 'form'])->name('form');
Route::post('/', [RegisterController::class, 'send'])->name('send');
Route::get('{token}', [RegisterController::class, 'verify'])->name('verify');
Route::put('/', [RegisterController::class, 'finalize'])->name('finalize');
