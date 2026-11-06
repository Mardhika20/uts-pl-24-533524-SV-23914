<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Makanan Utama', 'description' => 'Berbagai hidangan utama.'],
            ['name' => 'Sarapan', 'description' => 'Menu makanan untuk sarapan.'],
            ['name' => 'Camilan', 'description' => 'Berbagai makanan ringan.'],
            ['name' => 'Dessert', 'description' => 'Hidangan penutup dan makanan manis.'],
            ['name' => 'Minuman', 'description' => 'Berbagai jenis minuman.'],
            ['name' => 'Kue dan Roti', 'description' => 'Resep kue dan roti.'],
            ['name' => 'Sup dan Berkuah', 'description' => 'Hidangan sup dan makanan berkuah.'],
            ['name' => 'Lauk Pauk', 'description' => 'Berbagai lauk pendamping makanan.'],
            ['name' => 'Makanan Tradisional', 'description' => 'Hidangan khas tradisional.'],
            ['name' => 'Makanan Sehat', 'description' => 'Menu makanan bergizi dan sehat.'],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->updateOrInsert(
                ['name' => $category['name']],
                [
                    'description' => $category['description'],
                    'updated_at' => now(),
                ]
            );
        }
    }
}

