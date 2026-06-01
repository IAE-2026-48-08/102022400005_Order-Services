<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Di sinilah rute REST API untuk Order Service (Intan) didaftarkan.
| Seluruh rute dilindungi oleh ApiKeyMiddleware ('api.key') untuk memvalidasi
| header X-IAE-KEY.
|
*/

Route::middleware('api.key')->prefix('v1')->group(function () {
    // 1. Order Service (Intan) - Hanya 3 Endpoint Sesuai Spesifikasi Tugas Individu
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::post('/orders', [OrderController::class, 'store']);
});
