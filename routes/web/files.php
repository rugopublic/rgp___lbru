<?php

use App\Http\Controllers\Generic\FileUpload\FileUploadController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])
    ->controller(FileUploadController::class)
    ->group(function () {
        Route::post('/upload', 'upload')->name('upload');
    }
);
