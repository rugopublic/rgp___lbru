<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\PublicAccountRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final class AccountController extends Controller
{

    /**
     * Initializes the account controller instance and wires its required dependencies.
     *
     * @param PublicAccountRepositoryInterface $accounts
     * @return void
     */
    public function __construct(
        private readonly PublicAccountRepositoryInterface $accounts,
    ) {}

    /**
     * Handles the dashboard HTTP action for the account controller resource.
     * @return RedirectResponse
     */
    public function dashboard(): RedirectResponse
    {
        return redirect()->route('user.account.index');
    }

    /**
     * Handles the saved HTTP action for the account controller resource.
     * @return View
     */
    public function saved(): View
    {
        $userId = (int) Auth::id();
        return view('frontend.user.saved', ['items' => $this->accounts->saved($userId)]);
    }

    /**
     * Handles the comments HTTP action for the account controller resource.
     * @return View
     */
    public function comments(): View
    {
        $userId = (int) Auth::id();
        return view('frontend.user.comments', ['items' => $this->accounts->comments($userId)]);
    }

    /**
     * Handles the purchases HTTP action for the account controller resource.
     * @return View
     */
    public function purchases(): View
    {
        return view('frontend.user.purchases', [
            'purchases' => $this->accounts->purchases((int) Auth::id()),
        ]);
    }
}
