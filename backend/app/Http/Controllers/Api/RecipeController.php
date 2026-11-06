<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Recipe;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RecipeController extends Controller
{
    public function index(): JsonResponse
    {
        $recipes = Recipe::with('category')
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Daftar resep berhasil diambil.',
            'data' => $recipes,
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'ingredients' => ['required', 'string'],
            'instructions' => ['required', 'string'],
            'cooking_time' => ['required', 'integer', 'min:1'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'image_url' => ['nullable', 'string', 'max:2048'],
            'difficulty' => ['nullable', 'in:Mudah,Sedang,Sulit'],
            'servings' => ['nullable', 'integer', 'min:1'],
        ]);

        unset($validated['image']);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('recipes', 'public');
            $validated['image_url'] = '/storage/' . $path;
        }

        $recipe = Recipe::create($validated);

        return response()->json([
            'message' => 'Resep berhasil ditambahkan.',
            'data' => $recipe->load('category'),
        ], 201);
    }

    public function show(Recipe $recipe): JsonResponse
    {
        return response()->json([
            'message' => 'Detail resep berhasil diambil.',
            'data' => $recipe->load('category'),
        ], 200);
    }

    public function update(Request $request, Recipe $recipe): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string'],
            'ingredients' => ['sometimes', 'required', 'string'],
            'instructions' => ['sometimes', 'required', 'string'],
            'cooking_time' => ['sometimes', 'required', 'integer', 'min:1'],
            'category_id' => ['sometimes', 'required', 'integer', 'exists:categories,id'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'image_url' => ['sometimes', 'nullable', 'string', 'max:2048'],
            'difficulty' => ['sometimes', 'nullable', 'in:Mudah,Sedang,Sulit'],
            'servings' => ['sometimes', 'nullable', 'integer', 'min:1'],
        ]);

        unset($validated['image']);

        if ($request->hasFile('image')) {
            $oldImage = $recipe->image_url;

            $path = $request->file('image')->store('recipes', 'public');
            $validated['image_url'] = '/storage/' . $path;

            if ($oldImage && str_starts_with($oldImage, '/storage/')) {
                Storage::disk('public')->delete(
                    substr($oldImage, strlen('/storage/'))
                );
            }
        }

        $recipe->update($validated);

        return response()->json([
            'message' => 'Resep berhasil diperbarui.',
            'data' => $recipe->fresh()->load('category'),
        ], 200);
    }

    public function destroy(Recipe $recipe): JsonResponse
    {
        $recipe->delete();

        return response()->json([
            'message' => 'Resep berhasil dihapus.',
        ], 200);
    }
}
