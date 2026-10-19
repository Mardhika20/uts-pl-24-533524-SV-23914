<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipeApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_berhasil_login(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'password' => bcrypt('Admin12345'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $admin->email,
            'password' => 'Admin12345',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'message',
                     'token',
                     'token_type',
                     'user',
                 ]);
    }

    public function test_admin_berhasil_menambahkan_resep(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $category = Category::create([
            'name' => 'Makanan Utama',
            'description' => 'Kategori makanan utama',
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/recipes', [
                'title' => 'Nasi Goreng',
                'description' => 'Resep nasi goreng sederhana',
                'ingredients' => 'Nasi, telur, kecap',
                'instructions' => 'Tumis semua bahan hingga matang',
                'cooking_time' => 20,
                'category_id' => $category->id,
            ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.title', 'Nasi Goreng');

        $this->assertDatabaseHas('recipes', [
            'title' => 'Nasi Goreng',
            'category_id' => $category->id,
        ]);
    }

    public function test_akses_resep_tanpa_token_ditolak(): void
    {
        $response = $this->getJson('/api/recipes');

        $response->assertStatus(401)
                 ->assertJson([
                     'message' => 'Unauthenticated.',
                 ]);
    }
}
