<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Recipe;
use App\Models\RecipeReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecipeReviewController extends Controller
{
    // Menampilkan seluruh review pada satu resep
    public function index(
        Request $request,
        Recipe $recipe
    ): JsonResponse {
        $reviews = $recipe->reviews()
            ->with('user:id,name')
            ->latest()
            ->get();

        return response()->json([
            'data' => $reviews,
            'summary' => [
                'average' => $reviews->count() > 0
                    ? round((float) $reviews->avg('rating'), 1)
                    : 0,
                'count' => $reviews->count(),
            ],
            'my_review' => $reviews->firstWhere(
                'user_id',
                $request->user()->id
            ),
        ]);
    }

    // Menambahkan atau memperbarui review sendiri
    public function save(
        Request $request,
        Recipe $recipe
    ): JsonResponse {
        $validated = $request->validate([
            'rating' => [
                'required',
                'integer',
                'between:1,5',
            ],
            'comment' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $review = RecipeReview::updateOrCreate(
            [
                'recipe_id' => $recipe->id,
                'user_id' => $request->user()->id,
            ],
            [
                'rating' => $validated['rating'],
                'comment' => $validated['comment'] ?? null,
            ]
        );

        return response()->json([
            'message' => 'Rating dan ulasan berhasil disimpan.',
            'data' => $review,
        ], $review->wasRecentlyCreated ? 201 : 200);
    }

    // Menghapus review milik pengguna sendiri
    public function destroy(
        Request $request,
        Recipe $recipe
    ): JsonResponse {
        $deleted = RecipeReview::where(
            'recipe_id',
            $recipe->id
        )
            ->where('user_id', $request->user()->id)
            ->delete();

        if (!$deleted) {
            return response()->json([
                'message' => 'Ulasan tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'message' => 'Ulasan berhasil dihapus.',
        ]);
    }
}
