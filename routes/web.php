<?php

use Illuminate\Support\Facades\Route;

// Redirect halaman utama ke Swagger UI Documentation
Route::get('/', function () {
    return redirect('/api/documentation');
});

// Dukungan redirect jika pengguna mengetik /api-docs
Route::get('/api-docs', function () {
    return redirect('/api/documentation');
});
