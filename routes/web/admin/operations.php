<?php
use Illuminate\Support\Facades\Route;
Route::get('calendar',[\App\Http\Controllers\Admin\CalendarController::class,'index'])->name('calendar.index');
Route::get('transactions',[\App\Http\Controllers\Admin\TransactionController::class,'index'])->name('transactions.index');
Route::get('transactions/{transaction}',[\App\Http\Controllers\Admin\TransactionController::class,'show'])->name('transactions.show');
Route::post('courses/{course}/structure',[\App\Http\Controllers\Admin\CourseStructureController::class,'update'])->name('courses.structure.update');
Route::post('courses/{course}/teachers',[\App\Http\Controllers\Admin\CourseStructureController::class,'syncTeachers'])->name('courses.teachers.update');
Route::get('courses/{course}/modules/{module}/lessons/create',[\App\Http\Controllers\Admin\CourseStructureController::class,'createLesson'])->name('courses.lessons.create');
Route::post('courses/{course}/modules',[\App\Http\Controllers\Admin\CourseStructureController::class,'storeModule'])->name('courses.modules.store');
Route::post('courses/{course}/modules/{module}/lessons',[\App\Http\Controllers\Admin\CourseStructureController::class,'storeLesson'])->name('courses.lessons.store');
Route::get('courses/{course}/lessons/{lesson}/edit',[\App\Http\Controllers\Admin\CourseLessonController::class,'edit'])->name('courses.lessons.edit');
Route::put('courses/{course}/lessons/{lesson}',[\App\Http\Controllers\Admin\CourseLessonController::class,'update'])->name('courses.lessons.update');
