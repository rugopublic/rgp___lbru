<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserEmailChangeFormRequest;
use App\Services\User\UserEmailChangeService;
use RuntimeException;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Random\RandomException;
use Throwable;

/**
 * Class UserEmailChangeController
 *
 * Handles the routes related to the process of changing the email address
 * for a specific user.
 *
 * @extends Controller
 */
class UserEmailChangeController extends Controller
{
    /**
     * Receives the email the user desires to change to and initiates
     * the process of changing the email.
     *
     * @param UserEmailChangeFormRequest $request
     * @param UserEmailChangeService $service
     *
     * @return RedirectResponse
     *
     * @throws RandomException
     */
    public function initiate(UserEmailChangeFormRequest $request, UserEmailChangeService $service): RedirectResponse
    {
        // Retrieve the currently authenticated user of this request.
        $user = Auth::user();

        // Validate the input data.
        $email = $request->validated('email');

        // Ask the service to initiate the email change.
        try {
            $service->initiate($user, $email);
        } catch (RuntimeException $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }

        // Inform the user of a success.
        return redirect()->back()
            ->with('status', __('mail.user.email_change.new_sent_successfully'));
    }

    /**
     * Processes the token to verify the new email.
     * The user is determined automatically by the service.
     *
     * @param string $token
     * @param UserEmailChangeService $service
     *
     * @return View
     *
     * @throws RandomException
     */
    public function verify(string $token, UserEmailChangeService $service): View
    {
        try {
            $service->verify($token);
        } catch(RuntimeException $e) {
            return view('frontend.user.email.change.error', [
                'error' => $e->getMessage()
            ]);
        }

        return view('frontend.user.email.change.verify', [
            'message' => __('mail.user.email_change.new_verified_successfully'),
            'nextStep' => __('mail.user.email_change.check_old_email')
        ]);
    }

    /**
     * Processes the token to confirm the old email.
     * The user is determined automatically by the service.
     *
     * @param string $token
     * @param UserEmailChangeService $service
     *
     * @return View
     *
     * @throws RandomException|Throwable
     */
    public function confirm(string $token, UserEmailChangeService $service): View
    {
        try {
            $service->confirm($token);
        } catch(RuntimeException $e) {
            return view('frontend.user.email.change.error', [
                'error' => $e->getMessage()
            ]);
        }

        return view('frontend.user.email.change.confirm', [
            'message' => __('mail.user.email_change.email_changed_successfully')
        ]);
    }

    /**
     * Resends the email the user needs to be resent for the process.
     *
     * @param UserEmailChangeService $service
     *
     * @return RedirectResponse
     *
     * @throws RandomException
     */
    public function resend(UserEmailChangeService $service): RedirectResponse
    {
        // Retrieve the currently authenticated user of this request.
        $user = Auth::user();

        // Ask the service to resend the email for the change.
        try {
            $service->resend($user);
        } catch (RuntimeException $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }

        // Inform the user of a success.
        return redirect()->back()
            ->with('status', __('mail.user.email_change.resent_successfully'));
    }

    /**
     * Cancels the ongoing email change process for the authenticated user.
     *
     * @param UserEmailChangeService $service
     *
     * @return RedirectResponse
     *
     * @throws Throwable
     */
    public function cancel(UserEmailChangeService $service): RedirectResponse
    {
        // Retrieve the currently authenticated user of this request.
        $user = Auth::user();

        // Ask the service to cancel the email change process.
        try {
            $service->cancel($user);
        } catch (RuntimeException $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }

        // Inform the user of a success.
        return redirect()->back()
            ->with('status', __('mail.user.email_change.cancelled_successfully'));
    }
}
