<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPassword\SendPasswordResetRequest;
use App\Http\Requests\Auth\ForgotPassword\SendResetLinkRequest;
use App\Services\Auth\AuthPasswordResetService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Class ForgotPasswordController
 *
 * Handles the complete password–reset flow for guest users:
 *  - displaying the email input form;
 *  - accepting the reset request and sending a tokenized link;
 *  - showing the new-password form when the token is valid;
 *  - processing the final password update.
 *
 * All business logic (token creation, validation, expiration,
 * consumption, and password update) is delegated to dedicated
 * service classes. The controller only provides HTTP endpoints
 * and response handling.
 *
 * @extends Controller
 */
class ForgotPasswordController extends Controller
{
    /**
     * The password reset service for a user.
     *
     * @var AuthPasswordResetService
     */
    private AuthPasswordResetService $service;

    /**
     * Initializes the forgot password controller instance and wires its required dependencies.
     *
     * @param AuthPasswordResetService $service
     * @return void
     */
    public function __construct(AuthPasswordResetService $service)
    {
        $this->service = $service;
    }

    /**
     * Renders the forgot password form to input the user's email.
     * This is necessary for sending the password reset link.
     *
     * @return View
     */
    public function form(): View
    {
        return view('frontend.auth.password.forgot');
    }

    /**
     * Handle the submission of the forgot password form.
     *
     * Validates the email via SendResetLinkRequest and delegates
     * the sending of the password reset link to a service.
     *
     * The response is a confirmation view regardless of whether
     * the email exists (to prevent account enumeration).
     *
     * Any technical issues (SMTP failure, queue error, etc.) are
     * logged for monitoring and debugging purposes, without exposing
     * details to the user.
     *
     * @param SendResetLinkRequest $request
     *
     * @return View|RedirectResponse
     */
    public function send(SendResetLinkRequest $request): View|RedirectResponse
    {
        $email = $request->validated('email');

        try {
            $this->service->send($email);
        } catch (ModelNotFoundException) {
            // Here we do not return anything because we want to display the view
            // faking that everything went okay in that case.
            Log::info('Password reset requested for non-existing user', ['email' => $email]);
        } catch (Exception) {
            // If something else went wrong, it means we redirect back with 'email not sent'.
            Log::error('Failed to send the password reset email.', ['email' => $email]);
            return redirect()->back()->with('error', __('auth.password_reset.send.error'));
        }

        // This view will be rendered in case if the email had been successfully sent.
        return view('frontend.auth.password.confirm');
    }

    /**
     * Display the password reset form for a valid token.
     *
     * Checks whether the provided token exists, is not expired,
     * and has not been used yet. If the token is valid, renders
     * the password reset form where the user can input a new password.
     *
     * In case the token is invalid, expired, or already consumed,
     * an appropriate error view should be rendered to inform the user
     * without leaking sensitive information.
     *
     * @param string $token The password reset token provided in the URL
     *
     * @return View The view for resetting the password
     */
    public function edit(string $token): View
    {
        // If anything is wrong with the token, just render the 'bad link' page for the end user.
        // This will force them to re-request the token.
        if(!$this->service->validate($token)) {
            return view('frontend.auth.password.expired');
        }

        return view('frontend.auth.password.confirmed');
    }

    /**
     * Handle the submission of the password reset form.
     *
     * Validates the token and the new password via SendPasswordResetRequest,
     * then delegates the password update to a dedicated service.
     *
     * If the token is valid and the password update succeeds, renders
     * the confirmation view. If the token is invalid, expired, already used,
     * or any other error occurs, an appropriate error view or redirect should
     * be returned.
     *
     * @param SendPasswordResetRequest $request
     *   The validated request containing:
     *     - 'token': the password reset token
     *     - 'password': the new password
     *
     * @return View|RedirectResponse
     *   Returns the password reset confirmation view on success,
     *   or redirects back with errors on failure.
     */
    public function update(SendPasswordResetRequest $request): View|RedirectResponse
    {
        $token = $request->validated('token');
        $password = $request->validated('p_input');

        // If anything goes wrong on a service layer, just render the 'bad link' page for the end user.
        // This will force them to re-request the token.
        if(!$this->service->reset($token, $password)) {
            Log::info('Password reset attempt failed', ['token' => $token]);
            return view('frontend.auth.password.expired');
        }

        // This view will be rendered in case when the password had been changed successfully.
        return view('frontend.auth.password.reset');
    }
}
