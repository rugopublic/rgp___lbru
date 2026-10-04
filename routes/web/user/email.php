<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\UserEmailChangeController;

Route::middleware(['has.account', 'not.suspended'])->group(function () {
    Route::get('verify/{token}', [UserEmailChangeController::class, 'verify'])
        ->name('change.verify');

    Route::get('confirm/{token}', [UserEmailChangeController::class, 'confirm'])
        ->name('change.confirm');

    Route::middleware('throttle:5,1')->group(function () {
        Route::post('change', [UserEmailChangeController::class, 'initiate'])
            ->name('change.initiate');

        Route::post('resend', [UserEmailChangeController::class, 'resend'])
            ->name('change.resend');

        Route::post('cancel', [UserEmailChangeController::class, 'cancel'])
            ->name('change.cancel');
    });
});
