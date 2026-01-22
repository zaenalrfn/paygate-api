<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('paypal')->group(function () {
    Route::post('/create', [\App\Http\Controllers\PayPalController::class, 'create']);
    Route::post('/capture', [\App\Http\Controllers\PayPalController::class, 'capture']);
    Route::get('/success', [\App\Http\Controllers\PayPalController::class, 'success']);
    Route::any('/cancel', [\App\Http\Controllers\PayPalController::class, 'cancel']);
});
