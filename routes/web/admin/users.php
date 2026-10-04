<?php
use Illuminate\Support\Facades\Route;
Route::resource('groups', \App\Http\Controllers\Admin\GroupController::class)->except(['show','destroy']);
Route::delete('groups/{group}', [\App\Http\Controllers\Admin\GroupController::class,'destroy'])->name('groups.destroy');
Route::post('groups/{group}/restore', [\App\Http\Controllers\Admin\GroupController::class,'restore'])->name('groups.restore');
Route::get('groups/options', [\App\Http\Controllers\Admin\GroupController::class,'options'])->name('groups.options');
Route::resource('users', \App\Http\Controllers\Admin\UserController::class)->except(['show','destroy']);
Route::delete('users/{user}', [\App\Http\Controllers\Admin\UserController::class,'destroy'])->name('users.destroy');
Route::post('users/{user}/restore', [\App\Http\Controllers\Admin\UserController::class,'restore'])->name('users.restore');

Route::get('users/options', [\App\Http\Controllers\Admin\UserController::class,'options'])->name('users.options');
