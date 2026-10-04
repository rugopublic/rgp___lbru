<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserPasswordChangeFormRequest;
use App\Services\User\UserPasswordChangeService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Class UserPasswordChangeController
 *
 * Handles the routes related to the process of changing the password
 * for a specific user.
 *
 * All business logic (actual password update) is delegated to the
 * dedicated service class. The controller only provides HTTP endpoints
 * and response handling.
 *
 * @extends Controller
 */
class UserPasswordChangeController extends Controller
{
    /**
     * The password change service of a user.
     *
     * @var UserPasswordChangeService
     */
    private UserPasswordChangeService $service;

    /**
     * Initializes the user password change controller instance and wires its required dependencies.
     *
     * @param UserPasswordChangeService $service
     * @return void
     */
    public function __construct(UserPasswordChangeService $service) {
        $this->service = $service;
    }

    /**
     * Initiates a password change for a given user.
     *
     * @param UserPasswordChangeFormRequest $request
     *
     * @return RedirectResponse
     */
    public function change(UserPasswordChangeFormRequest $request): RedirectResponse
    {
        $user = auth()->user();
        $oldPassword = $request->validated('p_input');
        $newPassword = $request->validated('new_p_input');

        try {
            $this->service->change($user, $oldPassword, $newPassword);
        } catch(AuthenticationException) {
            return redirect()->back()->withErrors(
                ['current' => __('user.account.password.errors.generic')],
                'password_change'
            );
        }

        return redirect()->back()->with('password_status', __('user.account.password.status_updated'));
    }
}
