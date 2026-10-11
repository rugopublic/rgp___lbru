<?php

declare(strict_types=1);

use App\Http\Controllers\Public\ContentInteractionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'not.suspended'])->prefix('content')->name('content.')->group(function (): void {
    Route::post('/{type}/{id}/like', [ContentInteractionController::class, 'like'])->name('like');
    Route::post('/{type}/{id}/bookmark', [ContentInteractionController::class, 'bookmark'])->name('bookmark');
    Route::post('/{type}/{id}/comment', [ContentInteractionController::class, 'comment'])->name('comment');
});
