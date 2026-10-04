<?php

use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
require __DIR__.'/courses.php';
require __DIR__.'/content.php';
require __DIR__.'/users.php';
require __DIR__.'/operations.php';
