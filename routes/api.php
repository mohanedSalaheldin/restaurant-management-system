<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MenuApiController;
use App\Http\Controllers\Api\OrderApiController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::get('/menu/full', [MenuApiController::class, 'fullMenu']);
Route::get('/menu/items', [MenuApiController::class, 'items']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Same roles as the web order routes; kitchen staff use the KDS only.
    Route::middleware('role:admin,waiter,cashier')->group(function () {
        Route::get('/orders', [OrderApiController::class, 'index']);
        Route::post('/orders', [OrderApiController::class, 'store']);
        Route::get('/orders/{order}', [OrderApiController::class, 'show']);
    });
});
