<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Recipe;
use Illuminate\Http\Request;

class RecipeController extends Controller
{
    // Menampilkan seluruh resep
    public function index()
    {
        $recipes = Recipe::with('category')->get();

        return response()->json([
            'message' => 'Daftar resep berhasil ditampilkan',
            'data' => $recipes
        ], 200);
    }

    // Menambahkan resep baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'ingredients' => 'required|string',
            'instructions' => 'required|string',
            'cooking_time' => 'required|integer|min:1',
            'category_id' => 'required|exists:categories,id',
        ]);

        $recipe = Recipe::create($validated);

        return response()->json([
            'message' => 'Resep berhasil ditambahkan',
            'data' => $recipe->load('category')
        ], 201);
    }

    // Menampilkan detail resep
    public function show(Recipe $recipe)
    {
        return response()->json([
            'message' => 'Detail resep berhasil ditampilkan',
            'data' => $recipe->load('category')
        ], 200);
    }

    // Memperbarui resep
    public function update(Request $request, Recipe $recipe)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'ingredients' => 'sometimes|required|string',
            'instructions' => 'sometimes|required|string',
            'cooking_time' => 'sometimes|required|integer|min:1',
            'category_id' => 'sometimes|required|exists:categories,id',
        ]);

        $recipe->update($validated);

        return response()->json([
            'message' => 'Resep berhasil diperbarui',
            'data' => $recipe->load('category')
        ], 200);
    }

    // Menghapus resep
    public function destroy(Recipe $recipe)
    {
        $recipe->delete();

        return response()->json([
            'message' => 'Resep berhasil dihapus'
        ], 200);
    }
}