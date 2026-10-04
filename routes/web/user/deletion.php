<?php

use App\Http\Controllers\User\UserDeletionController;

Route::middleware('not.suspended')->group(function () {
    Route::get('/request', [UserDeletionController::class, 'request'])
        ->name('request');

    Route::post('/confirm', [UserDeletionController::class, 'confirm'])
        ->name('confirm');

    Route::get('/cancelled', [UserDeletionController::class, 'cancelled'])
        ->name('cancelled');
});

Route::middleware('suspended')->group(function () {
    Route::get('/confirmed', [UserDeletionController::class, 'confirmed'])
        ->name('confirmed');

    Route::get('/pending', [UserDeletionController::class, 'pending'])
        ->name('pending');

    Route::post('/cancel', [UserDeletionController::class, 'cancel'])
        ->name('cancel');
});
