<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\CourseCategoryController;

Route::prefix('courses')->name('courses.')->group(function () {
    // Course Categories (должны идти до courses/{course}, иначе "categories" будет воспринят как {course})
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/', [CourseCategoryController::class, 'index'])->name('index');
        Route::get('/options', [CourseCategoryController::class, 'options'])->name('options');
        Route::get('/create', [CourseCategoryController::class, 'create'])->name('create');
        Route::post('/', [CourseCategoryController::class, 'store'])->name('store');
                Route::put('/{category}', [CourseCategoryController::class, 'update'])->name('update');
        Route::delete('/{category}', [CourseCategoryController::class, 'destroy'])->name('destroy');
        Route::get('/{category}/edit', [CourseCategoryController::class, 'edit'])->name('edit');
    });
});

// Courses
Route::resource('courses', CourseController::class);
Route::post('courses/{course}/restore', [CourseController::class, 'restore'])->name('courses.restore');
