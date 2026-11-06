
<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RecipeController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\RecipeReviewController;
use App\Http\Controllers\Api\RecipeFinderController;
use App\Http\Controllers\Api\AdminAnalyticsController;


// ==========================================
// AUTENTIKASI PUBLIK
// ==========================================

Route::post('/login', [AuthController::class, 'login']);


// ==========================================
// ROUTE PENGGUNA YANG SUDAH LOGIN
// ==========================================

Route::middleware('auth:sanctum')->group(function () {

    // AUTENTIKASI
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/me', function (Request $request) {
        return response()->json([
            'message' => 'Data pengguna berhasil diambil.',
            'user' => $request->user(),
        ]);
    });


    // ======================================
    // KATEGORI
    // ======================================

    Route::get(
        '/categories',
        [CategoryController::class, 'index']
    );


    // ======================================
    // DAFTAR RESEP
    // ======================================

    Route::get(
        '/recipes',
        [RecipeController::class, 'index']
    );


    // ======================================
    // SMART RECIPE FINDER
    // ======================================

    Route::get(
        '/recipes/find-by-ingredients',
        [RecipeFinderController::class, 'index']
    );


    // ======================================
    // DETAIL RESEP
    // ======================================

    Route::get(
        '/recipes/{recipe}',
        [RecipeController::class, 'show']
    );


    // ======================================
    // RATING DAN REVIEW
    // ======================================

    Route::get(
        '/recipes/{recipe}/reviews',
        [RecipeReviewController::class, 'index']
    );

    Route::put(
        '/recipes/{recipe}/my-review',
        [RecipeReviewController::class, 'save']
    );

    Route::delete(
        '/recipes/{recipe}/my-review',
        [RecipeReviewController::class, 'destroy']
    );


    // ======================================
    // FAVORIT PENGGUNA
    // ======================================

    Route::get(
        '/favorites',
        [FavoriteController::class, 'index']
    );

    Route::post(
        '/favorites',
        [FavoriteController::class, 'store']
    );

    Route::delete(
        '/favorites/{recipe}',
        [FavoriteController::class, 'destroy']
    );


    // ======================================
    // FITUR KHUSUS ADMIN
    // ======================================

    Route::middleware('admin')->group(function () {

        // DASHBOARD ANALYTICS ADMIN
        Route::get(
            '/admin/analytics',
            [AdminAnalyticsController::class, 'index']
        );

        // TAMBAH RESEP
        Route::post(
            '/recipes',
            [RecipeController::class, 'store']
        );

        // EDIT RESEP
        Route::put(
            '/recipes/{recipe}',
            [RecipeController::class, 'update']
        );

        // HAPUS RESEP
        Route::delete(
            '/recipes/{recipe}',
            [RecipeController::class, 'destroy']
        );

    });

});
