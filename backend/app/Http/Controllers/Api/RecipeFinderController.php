<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Recipe;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecipeFinderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ingredients' => ['required', 'array', 'min:1', 'max:15'],
            'ingredients.*' => ['required', 'string', 'min:2', 'max:60'],
        ]);

        $ownedIngredients = collect($validated['ingredients'])
            ->map(fn ($value) => mb_strtolower(
                trim(preg_replace('/\s+/u', ' ', $value)),
                'UTF-8'
            ))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $results = Recipe::with('category')
            ->get()
            ->map(function (Recipe $recipe) use ($ownedIngredients) {
                $ingredients = $this->parseIngredients(
                    $recipe->ingredients
                );

                if (count($ingredients) === 0) {
                    return null;
                }

                $matched = [];
                $missing = [];

                foreach ($ingredients as $ingredient) {
                    $isAvailable = false;

                    foreach ($ownedIngredients as $owned) {
                        if ($this->ingredientMatches($ingredient, $owned)) {
                            $isAvailable = true;
                            break;
                        }
                    }

                    if ($isAvailable) {
                        $matched[] = $ingredient;
                    } else {
                        $missing[] = $ingredient;
                    }
                }

                if (count($matched) === 0) {
                    return null;
                }

                return [
                    'recipe' => $recipe,
                    'match_percentage' => (int) round(
                        100 * count($matched) / count($ingredients)
                    ),
                    'matched_ingredients' => $matched,
                    'missing_ingredients' => $missing,
                    'matched_count' => count($matched),
                    'total_ingredients' => count($ingredients),
                ];
            })
            ->filter()
            ->sort(function ($a, $b) {
                return ($b['match_percentage'] <=> $a['match_percentage'])
                    ?: (
                        count($a['missing_ingredients'])
                        <=> count($b['missing_ingredients'])
                    );
            })
            ->values();

        return response()->json([
            'message' => 'Hasil pencarian berdasarkan bahan berhasil diambil.',
            'total' => $results->count(),
            'data' => $results,
        ]);
    }

    private function parseIngredients(?string $raw): array
    {
        if ($raw === null || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $parts = array_map(static function ($item) {
                if (is_array($item)) {
                    return $item['text']
                        ?? $item['name']
                        ?? $item['description']
                        ?? '';
                }

                return is_string($item) ? $item : '';
            }, $decoded);
        } else {
            $parts = preg_split('/[\r\n;,]+/u', $raw) ?: [];
        }

        $parts = array_map(static function ($item) {
            $item = trim((string) $item);

            return trim(preg_replace(
                '/^(?:[-•]|\d+[.)])\s*/u',
                '',
                $item
            ));
        }, $parts);

        return array_values(array_filter(
            $parts,
            static fn ($item) => $item !== ''
        ));
    }

    private function ingredientMatches(
        string $recipeIngredient,
        string $owned
    ): bool {
        $recipeIngredient = mb_strtolower(
            $recipeIngredient,
            'UTF-8'
        );

        $needle = preg_quote($owned, '/');

        return preg_match(
            '/(?<![\p{L}\p{N}])'
                . $needle
                . '(?![\p{L}\p{N}])/u',
            $recipeIngredient
        ) === 1;
    }
}
