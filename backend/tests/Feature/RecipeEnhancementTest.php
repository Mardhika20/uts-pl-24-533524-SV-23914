<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Favorite;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RecipeEnhancementTest extends TestCase
{
    use RefreshDatabase;

    private function createRecipe(): Recipe
    {
        $category = Category::create([
            'name' => 'Makanan Utama',
            'description' => 'Kategori makanan utama',
        ]);

        return Recipe::create([
            'title' => 'Soto Ayam',
            'description' => 'Soto ayam kuah kuning',
            'ingredients' => 'Ayam, kunyit, serai',
            'instructions' => 'Rebus ayam dan masak bumbu',
            'cooking_time' => 45,
            'category_id' => $category->id,
            'image_url' => 'https://example.com/soto-ayam.jpg',
            'difficulty' => 'Mudah',
            'servings' => 4,
        ]);
    }

    public function test_admin_dapat_memperbarui_atribut_tambahan_resep(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $recipe = $this->createRecipe();

        Sanctum::actingAs($admin);

        $response = $this->putJson(
            "/api/recipes/{$recipe->id}",
            [
                'image_url' => 'https://example.com/soto-baru.jpg',
                'difficulty' => 'Sedang',
                'servings' => 5,
            ]
        );

        $response->assertOk()
            ->assertJsonPath('data.difficulty', 'Sedang')
            ->assertJsonPath('data.servings', 5)
            ->assertJsonPath(
                'data.image_url',
                'https://example.com/soto-baru.jpg'
            );

        $this->assertDatabaseHas('recipes', [
            'id' => $recipe->id,
            'difficulty' => 'Sedang',
            'servings' => 5,
        ]);
    }

    public function test_pengguna_login_dapat_melihat_kategori(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        Category::create([
            'name' => 'Makanan Utama',
            'description' => 'Kategori makanan utama',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/categories');

        $response->assertOk()
            ->assertJsonPath('data.0.name', 'Makanan Utama');
    }

    public function test_pengguna_dapat_menambahkan_favorit(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $recipe = $this->createRecipe();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/favorites', [
            'recipe_id' => $recipe->id,
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'recipe_id' => $recipe->id,
        ]);
    }

    public function test_favorit_tidak_dapat_diduplikasi(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $recipe = $this->createRecipe();

        Sanctum::actingAs($user);

        $this->postJson('/api/favorites', [
            'recipe_id' => $recipe->id,
        ])->assertCreated();

        $this->postJson('/api/favorites', [
            'recipe_id' => $recipe->id,
        ])->assertOk();

        $this->assertEquals(
            1,
            Favorite::where('user_id', $user->id)
                ->where('recipe_id', $recipe->id)
                ->count()
        );
    }

    public function test_pengguna_dapat_melihat_favorit_miliknya(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $recipe = $this->createRecipe();

        Favorite::create([
            'user_id' => $user->id,
            'recipe_id' => $recipe->id,
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/favorites')
            ->assertOk()
            ->assertJsonPath('data.0.recipe.title', 'Soto Ayam');
    }

    public function test_pengguna_dapat_menghapus_favorit(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $recipe = $this->createRecipe();

        Favorite::create([
            'user_id' => $user->id,
            'recipe_id' => $recipe->id,
        ]);

        Sanctum::actingAs($user);

        $this->deleteJson("/api/favorites/{$recipe->id}")
            ->assertOk();

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'recipe_id' => $recipe->id,
        ]);
    }

    public function test_pengguna_tidak_dapat_melihat_favorit_pengguna_lain(): void
    {
        $user1 = User::factory()->create([
            'role' => 'user',
        ]);

        $user2 = User::factory()->create([
            'role' => 'user',
        ]);

        $recipe = $this->createRecipe();

        Favorite::create([
            'user_id' => $user1->id,
            'recipe_id' => $recipe->id,
        ]);

        Sanctum::actingAs($user2);

        $this->getJson('/api/favorites')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }
}
