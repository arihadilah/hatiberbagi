<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Kesehatan', 'slug' => 'kesehatan', 'icon' => '🏥', 'color' => '#FAECE7', 'is_active' => true],
            ['name' => 'Pendidikan', 'slug' => 'pendidikan', 'icon' => '📚', 'color' => '#E6F1FB', 'is_active' => true],
            ['name' => 'Bencana Alam', 'slug' => 'bencana', 'icon' => '🌊', 'color' => '#EAF3DE', 'is_active' => true],
            ['name' => 'Sosial', 'slug' => 'sosial', 'icon' => '🍱', 'color' => '#FAEEDA', 'is_active' => true],
            ['name' => 'Lingkungan', 'slug' => 'lingkungan', 'icon' => '🌱', 'color' => '#E8F4ED', 'is_active' => true],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}