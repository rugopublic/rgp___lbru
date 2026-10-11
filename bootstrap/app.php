<?php

use App\Http\Middleware\User\EnsureUserHasAccount;
use App\Http\Middleware\User\EnsureUserHasNoAccount;
use App\Http\Middleware\User\EnsureUserIsAdmin;
use App\Http\Middleware\User\EnsureUserIsNotSuspended;
use App\Http\Middleware\User\EnsureUserIsSuspended;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')->group(function () {
                require base_path('routes/web/public/catalog.php');
                require base_path('routes/web/public/payments.php');
                require base_path('routes/web/public/interactions.php');
                Route::prefix('auth')
                    ->name('auth.')
                    ->group(function () {
                        require base_path('routes/web/auth/auth.php');
                    });

                Route::prefix('cookies')
                    ->name('cookies.')
                    ->group(function () {
                        require base_path('routes/web/public/cookies.php');
                    });

                Route::middleware('guest')->group(function () {
                    require base_path('routes/web/guest/session.php');
                });

                Route::middleware('guest')
                    ->prefix('auth/forgot')
                    ->name('auth.forgot.')
                    ->group(function () {
                        require base_path('routes/web/guest/forgot-password.php');
                    });

                Route::middleware('guest')
                    ->prefix('register')
                    ->name('register.')
                    ->group(function () {
                        require base_path('routes/web/guest/register.php');
                    });

                Route::middleware(['auth', 'verified'])
                    ->name('user.')
                    ->group(function () {
                        require base_path('routes/web/user/session.php');
                        require base_path('routes/web/user/dashboard.php');
                        require base_path('routes/web/user/public_account.php');

                        Route::prefix('delete')
                            ->name('delete.')
                            ->group(function () {
                                require base_path('routes/web/user/deletion.php');
                            });

                        Route::prefix('account')
                            ->name('account.')
                            ->group(function () {
                                require base_path('routes/web/user/profile.php');
                            });

                        Route::prefix('email')
                            ->name('email.')
                            ->group(function () {
                                require base_path('routes/web/user/email.php');
                            });

                        Route::prefix('password')
                            ->name('password.')
                            ->group(function () {
                                require base_path('routes/web/user/password.php');
                            });
                    });

                Route::middleware(['auth', 'not.suspended', 'admin'])->prefix('admin')->name('admin.')->group(function () {
                    require base_path('routes/web/admin/main.php');
                });

                Route::middleware(['auth'])->group(function () {
                    require base_path('routes/web/files.php');
                });
            });
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->preventRequestForgery(except: ['payments/yookassa/webhook']);

        $middleware->alias([
            'has.account' => EnsureUserHasAccount::class,
            'no.account' => EnsureUserHasNoAccount::class,
            'suspended' => EnsureUserIsSuspended::class,
            'not.suspended' => EnsureUserIsNotSuspended::class,
            'admin' => EnsureUserIsAdmin::class,
        ]);

        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
