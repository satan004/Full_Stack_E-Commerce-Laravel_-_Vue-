<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'slug' => 'electronics',
                'name' => 'Electronics',
                'description' => 'Everyday devices, audio gear, and work essentials.',
            ],
            [
                'slug' => 'fashion',
                'name' => 'Fashion',
                'description' => 'Wearable basics with clean style and durable materials.',
            ],
            [
                'slug' => 'home-living',
                'name' => 'Home Living',
                'description' => 'Practical upgrades for kitchens, desks, and daily routines.',
            ],
            [
                'slug' => 'sports',
                'name' => 'Sports & Outdoors',
                'description' => 'Gear for fitness, outdoor adventures, and active lifestyles.',
            ],
            [
                'slug' => 'books',
                'name' => 'Books & Media',
                'description' => 'Books, magazines, and digital content for learning and entertainment.',
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}