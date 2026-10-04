<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Public\PaymentController;

Route::post('/payments/yookassa/webhook',[PaymentController::class,'webhook'])->name('payments.webhook');
Route::middleware(['auth','verified','not.suspended'])->group(function(){
 Route::get('/courses/{course}/buy',[PaymentController::class,'checkoutCourse'])->name('courses.buy');
 Route::get('/products/{product}/buy',[PaymentController::class,'checkoutProduct'])->name('products.buy');
 Route::post('/checkout/{type}/{id}',[PaymentController::class,'pay'])->name('payments.pay');
 Route::get('/checkout/result/{intent}',[PaymentController::class,'result'])->name('payments.result');
});
