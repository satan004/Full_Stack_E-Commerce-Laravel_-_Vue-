<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $electronics = Category::where('slug', 'electronics')->first();
        $fashion = Category::where('slug', 'fashion')->first();
        $home = Category::where('slug', 'home-living')->first();
        $sports = Category::where('slug', 'sports')->first();
        $books = Category::where('slug', 'books')->first();

        $products = [
            [
                'category_id' => $electronics->id,
                'name' => 'Nimbus Wireless Headphones',
                'slug' => 'nimbus-wireless-headphones',
                'description' => 'Noise reducing over-ear headphones with a comfortable fit and long battery life.',
                'price' => 129.00,
                'stock' => 18,
                'image_path' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=900&q=80',
            ],
            [
                'category_id' => $electronics->id,
                'name' => 'Atlas Laptop Pro 14',
                'slug' => 'atlas-laptop-pro-14',
                'description' => 'A slim productivity laptop for study, admin work, and lightweight creative projects.',
                'price' => 899.00,
                'stock' => 7,
                'image_path' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=900&q=80',
            ],
            [
                'category_id' => $electronics->id,
                'name' => 'Smart Watch Elite',
                'slug' => 'smart-watch-elite',
                'description' => 'Fitness tracking smartwatch with heart rate monitor and GPS.',
                'price' => 249.99,
                'stock' => 15,
                'image_path' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=900&q=80',
            ],
            [
                'category_id' => $fashion->id,
                'name' => 'City Runner Sneakers',
                'slug' => 'city-runner-sneakers',
                'description' => 'Lightweight sneakers with a breathable upper and cushioned everyday sole.',
                'price' => 72.50,
                'stock' => 24,
                'image_path' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=900&q=80',
            ],
            [
                'category_id' => $fashion->id,
                'name' => 'Transit Day Backpack',
                'slug' => 'transit-day-backpack',
                'description' => 'A compact backpack with padded laptop storage and water resistant fabric.',
                'price' => 58.00,
                'stock' => 12,
                'image_path' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=900&q=80',
            ],
            [
                'category_id' => $fashion->id,
                'name' => 'Classic Leather Jacket',
                'slug' => 'classic-leather-jacket',
                'description' => 'Premium leather jacket with a timeless design and comfortable fit.',
                'price' => 189.00,
                'stock' => 8,
                'image_path' => 'https://images.unsplash.com/photo-1551028719-00167b16eac5?auto=format&fit=crop&w=900&q=80',
            ],
            [
                'category_id' => $home->id,
                'name' => 'Brewline Coffee Maker',
                'slug' => 'brewline-coffee-maker',
                'description' => 'A simple programmable coffee maker for busy mornings and shared offices.',
                'price' => 84.99,
                'stock' => 9,
                'image_path' => 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=900&q=80',
            ],
            [
                'category_id' => $home->id,
                'name' => 'Luma Desk Lamp',
                'slug' => 'luma-desk-lamp',
                'description' => 'A dimmable LED desk lamp with a compact base and adjustable color temperature.',
                'price' => 39.00,
                'stock' => 31,
                'image_path' => 'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?auto=format&fit=crop&w=900&q=80',
            ],
            [
                'category_id' => $home->id,
                'name' => 'Ergonomic Office Chair',
                'slug' => 'ergonomic-office-chair',
                'description' => 'Adjustable office chair with lumbar support and breathable mesh back.',
                'price' => 299.00,
                'stock' => 5,
                'image_path' => 'https://images.unsplash.com/photo-1592078615290-033ee584e267?auto=format&fit=crop&w=900&q=80',
            ],
            [
                'category_id' => $sports->id,
                'name' => 'Trail Running Shoes',
                'slug' => 'trail-running-shoes',
                'description' => 'Durable trail running shoes with excellent grip and ankle support.',
                'price' => 119.99,
                'stock' => 16,
                'image_path' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=900&q=80',
            ],
            [
                'category_id' => $sports->id,
                'name' => 'Yoga Mat Premium',
                'slug' => 'yoga-mat-premium',
                'description' => 'Non-slip yoga mat with extra cushioning for joint protection.',
                'price' => 45.00,
                'stock' => 22,
                'image_path' => 'https://images.unsplash.com/photo-1601925260368-ae2f83cf8b7f?auto=format&fit=crop&w=900&q=80',
            ],
            [
                'category_id' => $books->id,
                'name' => 'The Art of Programming',
                'slug' => 'the-art-of-programming',
                'description' => 'Comprehensive guide to modern programming practices and patterns.',
                'price' => 54.99,
                'stock' => 30,
                'image_path' => 'https://images.unsplash.com/photo-1532012197267-da84d127e765?auto=format&fit=crop&w=900&q=80',
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['slug' => $product['slug']],
                [
                    ...$product,
                    'is_active' => true,
                ]
            );
        }
    }
}