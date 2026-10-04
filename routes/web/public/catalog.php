<?php

use App\Http\Controllers\Public\CatalogController;
use Illuminate\Support\Facades\Route;
Route::get('/',[CatalogController::class,'home'])->name('home');
Route::get('/catalog',[CatalogController::class,'index'])->name('catalog');
Route::get('/search',[CatalogController::class,'search'])->name('search');
Route::get('/articles', [CatalogController::class,'articles'])->name('articles.index');
Route::get('/recipes', [CatalogController::class,'recipes'])->name('recipes.index');
Route::get('/ingredients', [CatalogController::class,'ingredients'])->name('ingredients.index');
Route::get('/courses',[CatalogController::class,'courses'])->name('courses.index');
Route::get('/courses/{course}',[CatalogController::class,'course'])->name('courses.show');
Route::middleware(['auth','verified','not.suspended'])->get('/courses/{course}/learn',[\App\Http\Controllers\Public\CourseLearnController::class,'show'])->name('courses.learn');
Route::get('/products',[CatalogController::class,'products'])->name('products.index');
Route::get('/products/{product}',[CatalogController::class,'product'])->name('products.show');
Route::middleware(['auth','verified','not.suspended'])->get('/products/{product}/download',[\App\Http\Controllers\Public\ProductFileController::class,'download'])->name('products.download');
Route::get('/articles/{article}',[CatalogController::class,'article'])->name('articles.show');
Route::get('/recipes/{recipe}',[CatalogController::class,'recipe'])->name('recipes.show');
Route::get('/ingredients/{ingredient}',[CatalogController::class,'ingredient'])->name('ingredients.show');

Route::get('/about', [StaticPageController::class, 'about'])->name('about');
Route::get('/eula', [StaticPageController::class, 'eula'])->name('eula');
Route::get('/pp', [StaticPageController::class, 'privacy'])->name('pp');
Route::get('/help', [StaticPageController::class, 'help'])->name('help.index');
Route::get('/help/payments', [StaticPageController::class, 'payments'])->name('help.payments');
Route::get('/help/refunds', [StaticPageController::class, 'refunds'])->name('help.refunds');
