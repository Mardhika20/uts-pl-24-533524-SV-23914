<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Recipe;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    // Menampilkan favorit milik pengguna yang login
    public function index(Request $request): JsonResponse
    {
        $favorites = Favorite::with('recipe.category')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Daftar favorit berhasil diambil.',
            'data' => $favorites,
        ], 200);
    }

    // Menambahkan resep ke favorit
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'recipe_id' => ['required', 'integer', 'exists:recipes,id'],
        ]);

        $favorite = Favorite::firstOrCreate([
            'user_id' => $request->user()->id,
            'recipe_id' => $validated['recipe_id'],
        ]);

        if (!$favorite->wasRecentlyCreated) {
            return response()->json([
                'message' => 'Resep sudah ada dalam daftar favorit.',
                'data' => $favorite->load('recipe.category'),
            ], 200);
        }

        return response()->json([
            'message' => 'Resep berhasil ditambahkan ke favorit.',
            'data' => $favorite->load('recipe.category'),
        ], 201);
    }

    // Menghapus resep dari favorit berdasarkan ID resep
    public function destroy(
        Request $request,
        Recipe $recipe
    ): JsonResponse {
        $deleted = Favorite::where('user_id', $request->user()->id)
            ->where('recipe_id', $recipe->id)
            ->delete();

        if (!$deleted) {
            return response()->json([
                'message' => 'Resep tidak ditemukan dalam daftar favorit.',
            ], 404);
        }

        return response()->json([
            'message' => 'Resep berhasil dihapus dari favorit.',
        ], 200);
    }
}

