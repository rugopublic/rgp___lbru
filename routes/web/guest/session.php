<?php

use App\Http\Controllers\Auth\AuthGuestController;
use Illuminate\Support\Facades\Route;

Route::get('/auth', [AuthGuestController::class, 'index'])->name('auth.index');
