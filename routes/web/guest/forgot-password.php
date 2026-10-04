<?php
/*
|--------------------------------------------------------------------------
| Forgot Password Flow
|--------------------------------------------------------------------------
|
| These routes implement the full password reset cycle for guest users:
|
| 1. GET /auth/forgot
|    - Displays the form to enter the user's email.
|    - Accessible by guests without restrictions.
|
| 2. POST /auth/forgot
|    - Accepts the email, validated via FormRequest (SendResetLinkRequest).
|    - If the user exists, generates a one-time token, marks previous tokens
|      for this email as used, and sends an email with a link to:
|      /auth/forgot/{token}
|    - If the email does not exist, behaves the same way (neutral response)
|      to prevent account enumeration.
|    - Renders a confirmation view informing that the reset link has been sent.
|
| 3. GET /auth/forgot/{token}
|    - Checks the state of the token:
|        • valid and not expired → displays the form to set a new password;
|        • expired → displays an error view ("link has expired");
|        • already used → displays an error view ("link is invalid");
|        • not found → displays an error view ("link is invalid").
|
| 4. PUT /auth/forgot
|    - Processes the submitted password reset form (SendPasswordResetRequest).
|    - Validates the new password.
|    - Checks the token:
|        • valid and not expired → consumes the token and updates the user's password;
|        • expired, used, or invalid → shows an error view.
|    - On successful password reset, renders the view
|      'web.frontend.auth.password.reset', which provides a button
|      to proceed to the login page, avoiding user confusion.
|
| Completion:
|    - The user can click the button in the reset confirmation view
|      to go to GET /auth/login and sign in with the new password.
|
*/

use App\Http\Controllers\Auth\ForgotPasswordController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ForgotPasswordController::class, 'form'])
    ->name('form');

Route::post('/', [ForgotPasswordController::class, 'send'])
    ->name('send');

Route::get('/{token}', [ForgotPasswordController::class, 'edit'])
    ->name('edit');

Route::put('/', [ForgotPasswordController::class, 'update'])
    ->name('update');
