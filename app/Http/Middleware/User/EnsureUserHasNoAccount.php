<?php

namespace App\Http\Middleware\User;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repositories\Contracts\UserRepositoryInterface;

class EnsureUserHasNoAccount
{

    /**
     * Initializes the ensure user has no account instance and wires its required dependencies.
     *
     * @param UserRepositoryInterface $users
     * @return void
     */
    public function __construct(private readonly UserRepositoryInterface $users) {}
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('auth.login.form');
        }

        if ($this->users->accountExists($user)) {
            return redirect()->route('user.account.index');
        }

        return $next($request);
    }
}
