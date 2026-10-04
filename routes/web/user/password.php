<?php

use App\Http\Controllers\User\UserPasswordChangeController;

Route::middleware(['has.account', 'not.suspended'])->group(function () {
    Route::middleware('throttle:5,1')->group(function () {
        Route::put('change', [UserPasswordChangeController::class, 'change'])
            ->name('change');
    });
});
