<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketCategoryController;
use App\Http\Middleware\JWTAuth;
use App\Http\Middleware\CheckPermission;

// Public routes
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);

// Protected routes
Route::middleware([JWTAuth::class])->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Tickets
    Route::get('/tickets', [TicketController::class, 'index']);
    Route::post('/tickets', [TicketController::class, 'store']);
    Route::get('/tickets/{id}', [TicketController::class, 'show']);
    Route::put('/tickets/{id}', [TicketController::class, 'update']);
    Route::post('/tickets/{id}/resolve', [TicketController::class, 'resolve']);
    Route::post('/tickets/{id}/comments', [TicketController::class, 'addComment']);

    // Categories
    Route::get('/ticket-categories', [TicketCategoryController::class, 'index']);
    Route::get('/ticket-categories/{id}/subcategories', [TicketCategoryController::class, 'getSubcategories']);
});
