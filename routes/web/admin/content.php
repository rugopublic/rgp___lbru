<?php

use Illuminate\Support\Facades\Route;

Route::resource('articles', \App\Http\Controllers\Admin\ArticleController::class)->except(['show','destroy']);
Route::delete('articles/{article}', [\App\Http\Controllers\Admin\ArticleController::class,'destroy'])->name('articles.destroy');
Route::post('articles/{article}/restore', [\App\Http\Controllers\Admin\ArticleController::class,'restore'])->name('articles.restore');
Route::get('articles/options', [\App\Http\Controllers\Admin\ArticleController::class,'options'])->name('articles.options');

Route::resource('products', \App\Http\Controllers\Admin\ProductController::class)->except(['show','destroy']);
Route::delete('products/{product}', [\App\Http\Controllers\Admin\ProductController::class,'destroy'])->name('products.destroy');
Route::post('products/{product}/restore', [\App\Http\Controllers\Admin\ProductController::class,'restore'])->name('products.restore');
Route::get('products/options', [\App\Http\Controllers\Admin\ProductController::class,'options'])->name('products.options');
Route::get('products/{product}/file', [\App\Http\Controllers\Admin\ProductMediaController::class,'file'])->name('products.file');

Route::resource('reference', \App\Http\Controllers\Admin\ReferenceController::class)->parameters(['reference'=>'reference'])->except(['show','destroy']);
Route::delete('reference/{reference}', [\App\Http\Controllers\Admin\ReferenceController::class,'destroy'])->name('reference.destroy');
Route::post('reference/{reference}/restore', [\App\Http\Controllers\Admin\ReferenceController::class,'restore'])->name('reference.restore');
Route::get('reference/options', [\App\Http\Controllers\Admin\ReferenceController::class,'options'])->name('reference.options');

Route::post('recipes/{recipe}/groups', [\App\Http\Controllers\Admin\RecipeController::class,'storeGroup'])->name('recipes.groups.store');
Route::post('recipes/{recipe}/ingredients', [\App\Http\Controllers\Admin\RecipeController::class,'storeIngredient'])->name('recipes.ingredients.store');
Route::resource('recipes', \App\Http\Controllers\Admin\RecipeController::class)->except(['show','destroy']);
Route::delete('recipes/{recipe}', [\App\Http\Controllers\Admin\RecipeController::class,'destroy'])->name('recipes.destroy');
Route::post('recipes/{recipe}/restore', [\App\Http\Controllers\Admin\RecipeController::class,'restore'])->name('recipes.restore');
Route::get('recipes/options', [\App\Http\Controllers\Admin\RecipeController::class,'options'])->name('recipes.options');

Route::resource('teachers', \App\Http\Controllers\Admin\TeacherController::class)->except(['show','destroy']);
Route::delete('teachers/{teacher}', [\App\Http\Controllers\Admin\TeacherController::class,'destroy'])->name('teachers.destroy');
Route::post('teachers/{teacher}/restore', [\App\Http\Controllers\Admin\TeacherController::class,'restore'])->name('teachers.restore');
Route::get('teachers/options', [\App\Http\Controllers\Admin\TeacherController::class,'options'])->name('teachers.options');

Route::get('articles/categories', [\App\Http\Controllers\Admin\ArticleCategoryController::class,'index'])->name('article.categories.index')->defaults('category_type','article');
Route::get('articles/categories/options', [\App\Http\Controllers\Admin\ArticleCategoryController::class,'options'])->name('article.categories.options')->defaults('category_type','article');
Route::get('articles/categories/{category}/edit', [\App\Http\Controllers\Admin\ArticleCategoryController::class,'edit'])->name('article.categories.edit')->defaults('category_type','article');
Route::post('articles/categories', [\App\Http\Controllers\Admin\ArticleCategoryController::class,'store'])->name('article.categories.store')->defaults('category_type','article');
Route::put('articles/categories/{category}', [\App\Http\Controllers\Admin\ArticleCategoryController::class,'update'])->name('article.categories.update')->defaults('category_type','article');
Route::delete('articles/categories/{category}', [\App\Http\Controllers\Admin\ArticleCategoryController::class,'destroy'])->name('article.categories.destroy')->defaults('category_type','article');
Route::post('articles/categories/{category}/restore', [\App\Http\Controllers\Admin\ArticleCategoryController::class,'restore'])->name('article.categories.restore')->defaults('category_type','article');

Route::get('products/categories', [\App\Http\Controllers\Admin\ProductCategoryController::class,'index'])->name('product.categories.index')->defaults('category_type','product');
Route::get('products/categories/options', [\App\Http\Controllers\Admin\ProductCategoryController::class,'options'])->name('product.categories.options')->defaults('category_type','product');
Route::get('products/categories/{category}/edit', [\App\Http\Controllers\Admin\ProductCategoryController::class,'edit'])->name('product.categories.edit')->defaults('category_type','product');
Route::post('products/categories', [\App\Http\Controllers\Admin\ProductCategoryController::class,'store'])->name('product.categories.store')->defaults('category_type','product');
Route::put('products/categories/{category}', [\App\Http\Controllers\Admin\ProductCategoryController::class,'update'])->name('product.categories.update')->defaults('category_type','product');
Route::delete('products/categories/{category}', [\App\Http\Controllers\Admin\ProductCategoryController::class,'destroy'])->name('product.categories.destroy')->defaults('category_type','product');
Route::post('products/categories/{category}/restore', [\App\Http\Controllers\Admin\ProductCategoryController::class,'restore'])->name('product.categories.restore')->defaults('category_type','product');

Route::get('reference/categories', [\App\Http\Controllers\Admin\ReferenceCategoryController::class,'index'])->name('reference.categories.index')->defaults('category_type','reference');
Route::get('reference/categories/options', [\App\Http\Controllers\Admin\ReferenceCategoryController::class,'options'])->name('reference.categories.options')->defaults('category_type','reference');
Route::get('reference/categories/{category}/edit', [\App\Http\Controllers\Admin\ReferenceCategoryController::class,'edit'])->name('reference.categories.edit')->defaults('category_type','reference');
Route::post('reference/categories', [\App\Http\Controllers\Admin\ReferenceCategoryController::class,'store'])->name('reference.categories.store')->defaults('category_type','reference');
Route::put('reference/categories/{category}', [\App\Http\Controllers\Admin\ReferenceCategoryController::class,'update'])->name('reference.categories.update')->defaults('category_type','reference');
Route::delete('reference/categories/{category}', [\App\Http\Controllers\Admin\ReferenceCategoryController::class,'destroy'])->name('reference.categories.destroy')->defaults('category_type','reference');
Route::post('reference/categories/{category}/restore', [\App\Http\Controllers\Admin\ReferenceCategoryController::class,'restore'])->name('reference.categories.restore')->defaults('category_type','reference');

Route::get('recipes/categories', [\App\Http\Controllers\Admin\RecipeCategoryController::class,'index'])->name('recipe.categories.index')->defaults('category_type','recipe');
Route::get('recipes/categories/options', [\App\Http\Controllers\Admin\RecipeCategoryController::class,'options'])->name('recipe.categories.options')->defaults('category_type','recipe');
Route::get('recipes/categories/{category}/edit', [\App\Http\Controllers\Admin\RecipeCategoryController::class,'edit'])->name('recipe.categories.edit')->defaults('category_type','recipe');
Route::post('recipes/categories', [\App\Http\Controllers\Admin\RecipeCategoryController::class,'store'])->name('recipe.categories.store')->defaults('category_type','recipe');
Route::put('recipes/categories/{category}', [\App\Http\Controllers\Admin\RecipeCategoryController::class,'update'])->name('recipe.categories.update')->defaults('category_type','recipe');
Route::delete('recipes/categories/{category}', [\App\Http\Controllers\Admin\RecipeCategoryController::class,'destroy'])->name('recipe.categories.destroy')->defaults('category_type','recipe');
Route::post('recipes/categories/{category}/restore', [\App\Http\Controllers\Admin\RecipeCategoryController::class,'restore'])->name('recipe.categories.restore')->defaults('category_type','recipe');
