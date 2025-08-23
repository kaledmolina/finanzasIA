<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\V1\CategoryController;
use App\Http\Controllers\API\V1\TransactionController;
use App\Http\Controllers\API\AuthenticationController;


Route::group(['namespace' => 'App\Http\Controllers\API'], function () {
    // --------------- Register and Login ----------------//
    Route::post('register', 'AuthenticationController@register')->name('register');
    Route::post('login', 'AuthenticationController@login')->name('login');

    Route::get('/health', function () {
        return response()->json(['status' => 'ok']);
    });
});
// --- Rutas Protegidas v1 ---
// Todas las rutas dentro de este grupo requieren un token de autenticación válido.
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('logout', 'AuthenticationController@logOut')->name('logout');

    // Rutas para Categorías y Transacciones
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('transactions', TransactionController::class);
});