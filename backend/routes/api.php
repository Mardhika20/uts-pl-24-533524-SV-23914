<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RecipeController;

// Login dapat diakses tanpa token
Route::post('/login', [AuthController::class, 'login']);

// Seluruh route berikut membutuhkan token Sanctum
Route::middleware('auth:sanctum')->group(function () {

    // Autentikasi pengguna
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/me', function (Request $request) {
        return response()->json($request->user());
    });

    // Admin dan User dapat melihat resep
    Route::get('/recipes', [RecipeController::class, 'index']);
    Route::get('/recipes/{recipe}', [RecipeController::class, 'show']);

    // Hanya Admin yang dapat mengelola resep
    Route::middleware('admin')->group(function () {
        Route::post('/recipes', [RecipeController::class, 'store']);
        Route::put('/recipes/{recipe}', [RecipeController::class, 'update']);
        Route::delete('/recipes/{recipe}', [RecipeController::class, 'destroy']);
    });
});
