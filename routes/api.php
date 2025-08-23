<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\V1\CategoryController;
use App\Http\Controllers\API\V1\TransactionController;
use App\Http\Controllers\API\AuthenticationController;

// --------------- Rutas Públicas v1 --------------- //
Route::prefix('v1')->group(function () {
    
    // Health check
    Route::get('/health', function () {
        return response()->json(['status' => 'ok']);
    });

    // Autenticación
    Route::post('register', [AuthenticationController::class, 'register'])->name('register');
    Route::post('login', [AuthenticationController::class, 'login'])->name('login');

});

// --------------- Rutas Protegidas v1 --------------- //
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    
    // User
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Autenticación
    Route::post('logout', [AuthenticationController::class, 'logOut'])->name('logout');

    // Recursos
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('transactions', TransactionController::class);

});