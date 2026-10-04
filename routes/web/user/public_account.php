<?php
use Illuminate\Support\Facades\Route;use App\Http\Controllers\Public\AccountController;
Route::get('/profile',[AccountController::class,'dashboard'])->name('profile');Route::get('/saved',[AccountController::class,'saved'])->name('saved');Route::get('/purchases',[AccountController::class,'purchases'])->name('purchases');Route::get('/comments',[AccountController::class,'comments'])->name('comments');
