<?php

namespace App\Http\Controllers\Register;

use App\Http\Controllers\Controller;
use App\Http\Requests\Register\SendRegisterLinkRequest;
use App\Http\Requests\Register\SetPasswordRequest;
use App\Services\Register\RegisterService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Class RegisterController
 *
 * Handles the complete registration flow for guest users:
 *  - displaying the email input form;
 *  - accepting the registration request and sending a tokenized link;
 *  - showing the enter-password form when the token is valid;
 *  - finalizing the registration by assigning the password.
 *
 * All business logic (token creation, validation, expiration,
 * consumption, and password update) is delegated to dedicated
 * service classes. The controller only provides HTTP endpoints
 * and response handling.
 *
 * @extends Controller
 */
class RegisterController extends Controller
{
    /**
     * The register service for a guest.
     *
     * @var RegisterService
     */
    private RegisterService $service;

    /**
     * Initializes the register controller instance and wires its required dependencies.
     *
     * @param RegisterService $service
     * @return void
     */
    public function __construct(RegisterService $service)
    {
        $this->service = $service;
    }

    /**
     * Renders the registration form to input the guest's email.
     * This is necessary for sending the email validation link.
     *
     * @return View
     */
    public function form(): View
    {
        return view('frontend.register.form');
    }

    /**
     * Handle the submission of the registration form.
     *
     * Validates the email via SendRegisterLinkRequest and delegates
     * the sending of the verification link to a service.
     *
     * The response is a confirmation view regardless of whether
     * the email exists (to prevent account enumeration).
     *
     * Any technical issues (SMTP failure, queue error, etc.) are
     * logged for monitoring and debugging purposes, without exposing
     * details to the user.
     *
     * @param SendRegisterLinkRequest $request
     *
     * @return View|RedirectResponse
     */
    public function send(SendRegisterLinkRequest $request): View|RedirectResponse
    {
        $email = $request->validated('email');

        try {
             $this->service->send($email, [
                'agreement' => true,
                'personal_data_consent' => true,
                'accepted_at' => now(),
                'agreement_version' => config('legal.agreement_version'),
                'privacy_version' => config('legal.privacy_version'),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        } catch (Exception) {
            // If something else went wrong, it means we redirect back with 'email not sent'.
            Log::error('Failed to send the verification link to the specified email.', ['email' => $email]);
            return redirect()->back()->with('error', __('register.send.error'));
        }

        // This view will be rendered in case if the email had been successfully sent.
        return view('frontend.register.confirm');
    }

    /**
     * Display the password input form for a valid token.
     *
     * Checks whether the provided token exists, is not expired,
     * and has not been used yet. If the token is valid, renders
     * the password input form where the registering user can input
     * a password.
     *
     * In case the token is invalid, expired, or already consumed,
     * an appropriate error view should be rendered to inform the user
     * without leaking sensitive information.
     *
     * @param string $token The email verification token provided in the URL
     *
     * @return View The view for resetting the password
     */
    public function verify(string $token): View
    {
        // If anything is wrong with the token, just render the 'bad link' page for the end user.
        // This will force them to re-request the token.
         if(!$this->service->validate($token)) {
            return view('frontend.register.expired');
         }

        return view('frontend.register.confirmed');
    }

    /**
     * Handle the submission of the password setup form.
     *
     * Validates the token and the new password via SetPasswordRequest,
     * then delegates the password setup to a dedicated service.
     *
     * If the token is valid and the password setup succeeds, renders
     * the confirmation view. If the token is invalid, expired, already used,
     * or any other error occurs, an appropriate error view or redirect should
     * be returned.
     *
     * @param SetPasswordRequest $request
     *   The validated request containing:
     *     - 'token': the email verification token
     *     - 'password': the desired password
     *
     * @return View|RedirectResponse
     *   Returns the registration confirmation view on success,
     *   or redirects back with errors on failure.
     */
    public function finalize(SetPasswordRequest $request): View|RedirectResponse
    {
        $token = $request->validated('token');
        $password = $request->validated('p_input');

        // If anything goes wrong on a service layer, just render the 'bad link' page for the end user.
        // This will force them to re-request the token.
         if(!$this->service->finalize($token, $password)) {
             Log::info('Registration attempt failed', ['token' => $token]);
             return view('frontend.register.expired');
         }

        // This view will be rendered in case when the password had been set successfully.
        return view('frontend.register.complete');
    }
}
