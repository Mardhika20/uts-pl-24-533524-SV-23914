<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AdminAnalyticsController extends Controller
{
    public function index(): JsonResponse
    {
        // Menghitung statistik utama
        $summary = [
            'total_recipes' => DB::table('recipes')->count(),

            'total_categories' => DB::table('categories')->count(),

            'total_users' => DB::table('users')->count(),

            'total_favorites' => DB::table('favorites')->count(),

            'total_reviews' => DB::table('recipe_reviews')->count(),

            'average_rating' => round(
                (float) (DB::table('recipe_reviews')->avg('rating') ?? 0),
                1
            ),
        ];

        // Menghitung favorit setiap resep
        $favoritesByRecipe = DB::table('favorites')
            ->select('recipe_id')
            ->selectRaw('COUNT(*) as favorites_count')
            ->groupBy('recipe_id');

        // Menghitung review dan rating setiap resep
        $reviewsByRecipe = DB::table('recipe_reviews')
            ->select('recipe_id')
            ->selectRaw('COUNT(*) as reviews_count')
            ->selectRaw('ROUND(AVG(rating), 1) as average_rating')
            ->groupBy('recipe_id');

        // Mengambil 5 resep paling populer
        $topRecipes = DB::table('recipes as r')
            ->leftJoinSub($favoritesByRecipe, 'f', function ($join) {
                $join->on('f.recipe_id', '=', 'r.id');
            })
            ->leftJoinSub($reviewsByRecipe, 'rv', function ($join) {
                $join->on('rv.recipe_id', '=', 'r.id');
            })
            ->select('r.id', 'r.title')
            ->selectRaw('COALESCE(f.favorites_count, 0) as favorites_count')
            ->selectRaw('COALESCE(rv.reviews_count, 0) as reviews_count')
            ->selectRaw('COALESCE(rv.average_rating, 0) as average_rating')
            ->orderByDesc('favorites_count')
            ->orderByDesc('reviews_count')
            ->orderByDesc('average_rating')
            ->orderBy('r.title')
            ->limit(5)
            ->get();

        // Jumlah resep berdasarkan kategori
        $categories = DB::table('categories as c')
            ->leftJoin('recipes as r', 'r.category_id', '=', 'c.id')
            ->select('c.id', 'c.name')
            ->selectRaw('COUNT(r.id) as recipe_count')
            ->groupBy('c.id', 'c.name')
            ->orderByDesc('recipe_count')
            ->orderBy('c.name')
            ->get();

        // Jumlah review berdasarkan bintang
        $ratingsByValue = DB::table('recipe_reviews')
            ->select('rating')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating');

        $ratingDistribution = collect([5, 4, 3, 2, 1])
            ->map(fn ($rating) => [
                'rating' => $rating,
                'total' => (int) ($ratingsByValue[$rating] ?? 0),
            ])
            ->values();

        // Mengembalikan data dalam format JSON
        return response()->json([
            'message' => 'Statistik admin berhasil diambil.',
            'data' => [
                'summary' => $summary,
                'top_recipes' => $topRecipes,
                'categories' => $categories,
                'rating_distribution' => $ratingDistribution,
            ],
        ]);
    }
}
