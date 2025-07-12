<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QrCodeController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// QR Code API Routes
Route::prefix('qr-codes')->group(function () {
    Route::post('/generate', [QrCodeController::class, 'generate']);
    Route::get('/stats', [QrCodeController::class, 'stats']);
    Route::get('/health', [QrCodeController::class, 'health']);
});