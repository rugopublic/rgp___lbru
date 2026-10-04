<?php

namespace App\Http\Controllers\User;

use App\Exceptions\User\DeletionAlreadyRequestedException;
use App\Exceptions\User\DeletionNotRequestedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserAccountDeleteFormRequest;
use App\Services\User\UserDeletionService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class UserDeletionController extends Controller
{
    /**
     * The deletion management service of a user.
     *
     * @var UserDeletionService
     */
    private UserDeletionService $deletion;

    /**
     * Initializes the user deletion controller instance and wires its required dependencies.
     *
     * @param UserDeletionService $deletion
     * @return void
     */
    public function __construct(UserDeletionService $deletion) {
        $this->deletion = $deletion;
    }

    /**
     * Renders the deletion request page for the currently authenticated user.
     *
     * @return View
     */
    public function request(): View
    {
        $user = Auth::user();

        return view('frontend.user.delete.request', [
            'user' => $user
        ]);
    }

    /**
     * Confirms the deletion request
     * for the currently authenticated user.
     *
     * @param UserAccountDeleteFormRequest $request
     *
     * @return RedirectResponse
     */
    public function confirm(UserAccountDeleteFormRequest $request): RedirectResponse
    {
        // Retrieve the currently authenticated user of this request.
        $user = Auth::user();

        // Ask the service to apply the changes.
        try {
            $this->deletion->initiate($user);
        } catch(ModelNotFoundException $e) {
            Log::error('User\'s record not found during user account deletion request confirmation', [
                'exception' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', __('user.account.delete.errors.no_model'));
        } catch(DeletionAlreadyRequestedException $e) {
            Log::warning('Attempt to delete an account already scheduled for deletion', [
                'user' => $user->id,
                'exception' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', __('user.account.delete.errors.already_requested'));
        } catch(Throwable $e) {
            Log::error('An unexpected exception occurred', [
                'exception' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', __('user.account.delete.errors.generic'));
        }

        // Direct the user to 'Account Deletion Confirmed' route on success.
        return redirect()->route('user.delete.confirmed')
            ->with('from_deletion_request', true);
    }

    /**
     * Renders the confirmation page of the deletion request
     * for the currently authenticated user.
     *
     * @return View|RedirectResponse
     */
    public function confirmed(): View|RedirectResponse
    {
        if (!session('from_deletion_request')) {
            return redirect()->route('user.delete.request');
        }

        session()->forget('from_deletion_request');

        $user = Auth::user();

        return view('frontend.user.delete.confirmed', [
            'user' => $user
        ]);
    }

    /**
     * Renders the deletion pending page
     * for the currently authenticated user.
     *
     * @return View
     */
    public function pending(): View
    {
        $user = Auth::user()->loadMissing('account');

        $graceDays = (int) config('user.delete_grace_days', 30);
        $daysDeleted = $user->deleted_at->diffInDays(now());
        $daysRemaining = (int) max(0, $graceDays - $daysDeleted);

        $finalDeletionDate = $user->deleted_at->addDays($graceDays);

        return view('frontend.user.delete.pending', [
            'user' => $user,
            'days_remaining' => $daysRemaining,
            'final_deletion_date' => $finalDeletionDate,
            'grace_period_days' => $graceDays,
        ]);
    }

    /**
     * Cancels the deletion request
     * for the currently authenticated user.
     *
     * @return RedirectResponse
     */
    public function cancel(): RedirectResponse
    {
        // Retrieve the currently authenticated user of this request.
        $user = Auth::user();

        // Ask the service to apply the changes.
        try {
            $this->deletion->restore($user);
        } catch(ModelNotFoundException $e) {
            Log::error('User\'s record not found during user account deletion request confirmation', [
                'exception' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', __('user.account.delete.errors.no_model'));
        } catch(DeletionNotRequestedException $e) {
            Log::warning('Attempt to cancel deletion of an account that is not scheduled for deletion', [
                'user' => $user->id,
                'exception' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', __('user.account.delete.errors.not_requested'));
        } catch(Exception $e) {
            Log::error('An unexpected exception occurred', [
                'exception' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', __('user.account.delete.errors.generic'));
        }

        // Direct the user to 'Account Deletion Cancel' route on success.
        return redirect()->route('user.delete.cancelled')
            ->with('from_deletion_cancel', true);
    }

    /**
     * Renders the confirmation page of the deletion request
     * for the currently authenticated user.
     *
     * @return View|RedirectResponse
     */
    public function cancelled(): View|RedirectResponse
    {
        if (!session('from_deletion_cancel')) {
            return redirect()->route('user.account.index');
        }

        session()->forget('from_deletion_cancel');

        $user = Auth::user();

        return view('frontend.user.delete.cancelled', [
            'user' => $user
        ]);
    }
}
