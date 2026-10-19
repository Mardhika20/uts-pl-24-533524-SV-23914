<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@recipebox.com'],
            [
                'name' => 'Admin RecipeBox',
                'password' => Hash::make('Admin12345'),
                'role' => 'admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'user@recipebox.com'],
            [
                'name' => 'User RecipeBox',
                'password' => Hash::make('User12345'),
                'role' => 'user',
            ]
        );
    }
}