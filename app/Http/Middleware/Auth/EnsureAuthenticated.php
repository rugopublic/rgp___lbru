<?php

namespace App\Http\Middleware\Auth;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {


        if (!Auth::check()) {
            if ($request->expectsJson()) {
                abort(401);
            }

            return redirect()->route('auth.login.form');
        }

        return $next($request);
    }
}
