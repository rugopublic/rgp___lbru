<?php

namespace App\Http\Controllers\User;

use App\Exceptions\User\AccountAlreadyExistsException;
use App\Exceptions\User\AccountNotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserAccountCreateFormRequest;
use App\Http\Requests\User\UserAccountUpdateFormRequest;
use App\Services\User\UserManagementService;
use App\Repositories\Contracts\PublicAccountRepositoryInterface;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Log;

/**
 * Class UserAccountController
 *
 * Handles the routes related to the process of account management
 * of a specific user.
 *
 * @extends Controller
 */
class UserAccountController extends Controller
{
    /**
     * The account management service of a user.
     *
     * @var UserManagementService
     */
    private UserManagementService $manage;

    /**
     * Initializes the user account controller instance and wires its required dependencies.
     *
     * @param UserManagementService $manage
     * @param PublicAccountRepositoryInterface $accounts
     * @return void
     */
    public function __construct(UserManagementService $manage, private readonly PublicAccountRepositoryInterface $accounts) {
        $this->manage = $manage;
    }

    /**
     * Renders the currently authorized user's welcome page.
     * It is used to set up the account if the user doesn't have one yet
     * (for example, just verified the email for registration but haven't
     * yet proceeded to specify account details)
     *
     * @return View
     */
    public function welcome(): View
    {
        $user = Auth::user();

        return view('frontend.user.account.welcome', [
            'user' => $user
        ]);
    }

    /**
     * Renders the currently authorized user's congratulations page
     * right after creating the account.
     *
     * @return View|RedirectResponse
     */
    public function created(): View|RedirectResponse
    {
        // We will only render the view if the session has 'from_welcome' flag.
        // Otherwise, try redirecting to the user account's home page (or for further
        // redirect if the user has no account, but that's not the responsibility of the
        // current method).
        if(!session('from_welcome')) {
            return redirect()->route('user.account.index');
        }

        session()->forget('from_welcome');

        $user = $this->accounts->user((int) Auth::id());

        return view('frontend.user.account.created', [
            'user' => $user
        ]);
    }

    /**
     * Renders the currently authorized user's account home page.
     *
     * @return View
     */
    public function index(): View
    {
        $user = $this->accounts->user((int) Auth::id());

        return view('frontend.user.account.index', [
            'user' => $user,
            'enrollments' => $this->accounts->enrollments((int) $user->id),
        ]);
    }

    /**
     * Renders the currently authorized user's account edit page.
     *
     * @return View
     */
    public function edit(): View
    {
        $user = $this->accounts->user((int) Auth::id());

        return view('frontend.user.account.edit', [
            'user' => $user
        ]);
    }

    /**
     * Receives the data the authorized user with verified email wants to input
     * initially when creating their account and applies the changes if the data
     * is valid.
     *
     * @param UserAccountCreateFormRequest $request
     *
     * @return RedirectResponse
     */
    public function store(UserAccountCreateFormRequest $request): RedirectResponse
    {
        // Retrieve the currently authenticated user of this request.
        $user = Auth::user();

        // Validate the input data (only name so far).
        $name = $request->validated('name');

        // Ask the service to apply the changes.
        try {
            $this->manage->createAccount($user, [
                'name' => $name
            ]);
        } catch(ModelNotFoundException $e) {
            Log::error('User\'s record not found during user account creation', [
                'exception' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', __('user.account.store.errors.no_model'));
        } catch(AccountAlreadyExistsException $e) {
            Log::warning('Attempt to create an account by user already having the account', [
                'user' => $user->id,
                'exception' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', __('user.account.store.errors.has_account'));
        } catch(Exception $e) {
            Log::error('An unexpected exception occurred', [
                'exception' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', __('user.account.store.errors.generic'));
        }

        // Direct the user to 'Account Created' route on success.
        return redirect()->route('user.account.created')
            ->with('from_welcome', true);
    }

    /**
     * Receives the data the authorized user wants to change about their account
     * and applies the changes if the data is valid.
     *
     * @param UserAccountUpdateFormRequest $request
     *
     * @return RedirectResponse
     */
    public function update(UserAccountUpdateFormRequest $request): RedirectResponse
    {
        // Retrieve the currently authenticated user of this request.
        $user = Auth::user();

        // Validate the input data (only name so far).
        $name = $request->validated('name');

        // Ask the service to apply the changes.
        try {
            $this->manage->updateAccountName($user, $name, false);
        } catch(AccountNotFoundException $e) {
            Log::error('Account not found during user update', [
                'user_id' => $user->id,
                'exception' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', __('user.account.edit.errors.no_account'));
        } catch(ModelNotFoundException $e) {
            Log::error('User\'s record not found during user update', [
                'exception' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', __('user.account.edit.errors.no_model'));
        } catch(Exception $e) {
            Log::error('An unexpected exception occurred', [
                'exception' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', __('user.account.edit.errors.generic'));
        }

        // Inform the user of a success.
        return redirect()->route('user.account.index')
            ->with('status', __('user.account.edit.success.generic'));
    }
}
