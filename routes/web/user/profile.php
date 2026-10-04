<?php

use App\Http\Controllers\User\UserAccountController;

Route::middleware(['no.account', 'not.suspended'])->group(function () {
    Route::get('/welcome', [UserAccountController::class, 'welcome'])->name('welcome');
});

Route::middleware(['has.account', 'not.suspended'])->group(function () {
    Route::get('/created', [UserAccountController::class, 'created'])->name('created');

    Route::get('/edit', [UserAccountController::class, 'edit'])->name('edit');

    Route::put('/', [UserAccountController::class, 'update'])->name('update');
    Route::get('/', [UserAccountController::class, 'index'])->name('index');
});

Route::post('/', [UserAccountController::class, 'store'])->name('store');
