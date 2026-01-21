<?php

use Illuminate\Support\Facades\Route;

// Root path - serve React app
Route::get('/', function () {
    if (file_exists(public_path('index.html'))) {
        return response()->file(public_path('index.html'));
    }
    return view('app');
});

// API info endpoint
Route::get('/api', function () {
    return response()->json([
        'message' => 'IT Security Ticketing System API',
        'version' => '1.0.0',
        'status' => 'running',
        'endpoints' => [
            'auth' => '/api/auth/login',
            'tickets' => '/api/tickets',
            'categories' => '/api/ticket-categories',
        ]
    ]);
});

// Serve React App untuk semua routes kecuali API
// Route ini harus di bawah API routes agar API tetap bisa diakses
Route::get('/{any}', function () {
    if (file_exists(public_path('index.html'))) {
        return response()->file(public_path('index.html'));
    }
    return view('app');
})->where('any', '^(?!api).*$');
