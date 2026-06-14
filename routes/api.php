<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;

/*
|--------------------------------------------------------------------------
| API Routes - Order Service (Intan)
|--------------------------------------------------------------------------
*/

// Kelompok 1: Endpoint membaca data (GET) tetap dijaga menggunakan API Key (NIM Anda)
Route::middleware('api.key')->prefix('v1')->group(function () {
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
});

// Kelompok 2: Endpoint memproses transaksi (POST) dijaga menggunakan Federated SSO JWT
Route::middleware('jwt.sso')->prefix('v1')->group(function () {
    Route::post('/orders', [OrderController::class, 'store']);
});

// Endpoint Publik untuk Reset Cache Database agar mempermudah Pengujian
Route::get('/v1/reset', function () {
    \App\Services\DatabaseService::seed();
    return response()->json([
        'status' => 'success',
        'message' => 'Database cache reset successfully. All orders are now PENDING.'
    ]);
});