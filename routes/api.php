<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::post('/payment', [PaymentController::class, 'create']);
// Route::post('/webhooks/midtrans', [PaymentController::class, 'webhook']);
// Route::middleware('auth')->group(function () {
//     Route::post('/payment/create', [PaymentController::class, 'createPayment'])->name('payment.create');
//     Route::post('/payment/notification', [PaymentController::class, 'paymentNotification'])->name('payment.notification');
    
//     Route::get('/payment/success', function () { return view('payment.success'); })->name('payment.success');
//     Route::get('/payment/unfinish', function () { return view('payment.unfinish'); })->name('payment.unfinish');
//     Route::get('/payment/error', function () { return view('payment.error'); })->name('payment.error');
// });

// // Tambahkan route baru
// Route::middleware('auth')->group(function () {
//     Route::get('/payment/{booking}', [PaymentController::class, 'showPaymentPage'])->name('payment.show');
//     Route::post('/payment/create', [PaymentController::class, 'createPayment'])->name('payment.create');
//     Route::post('/payment/notification', [PaymentController::class, 'paymentNotification'])->name('payment.notification');
// });

Route::post('/payment/notification', [PaymentController::class, 'paymentNotification'])->name('payment.notification');