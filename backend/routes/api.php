
<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RecipeController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\FavoriteController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    // Autentikasi
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/me', function (Request $request) {
        return response()->json([
            'message' => 'Data pengguna berhasil diambil.',
            'user' => $request->user(),
        ]);
    });

    // Kategori
    Route::get('/categories', [CategoryController::class, 'index']);

    // Menampilkan resep
    Route::get('/recipes', [RecipeController::class, 'index']);
    Route::get('/recipes/{recipe}', [RecipeController::class, 'show']);

    // Favorit pengguna
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites', [FavoriteController::class, 'store']);
    Route::delete('/favorites/{recipe}', [FavoriteController::class, 'destroy']);

    // CRUD resep khusus Admin
    Route::middleware('admin')->group(function () {
        Route::post('/recipes', [RecipeController::class, 'store']);
        Route::put('/recipes/{recipe}', [RecipeController::class, 'update']);
        Route::delete('/recipes/{recipe}', [RecipeController::class, 'destroy']);
    });
});
