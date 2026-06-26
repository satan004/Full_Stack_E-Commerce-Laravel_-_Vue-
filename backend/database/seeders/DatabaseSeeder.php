<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Setting::putMany(Setting::defaults());

        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin Manager',
                'password' => '1234567',
                'is_admin' => true,
            ],
        );

        User::updateOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name' => 'Demo Customer',
                'password' => 'password',
                'phone' => '+855 12 345 678',
                'address' => 'Phnom Penh, Cambodia',
                'is_admin' => false,
            ],
        );

        $electronics = Category::updateOrCreate(
            ['slug' => 'electronics'],
            [
                'name' => 'Electronics',
                'description' => 'Everyday devices, audio gear, and work essentials.',
            ],
        );

        $fashion = Category::updateOrCreate(
            ['slug' => 'fashion'],
            [
                'name' => 'Fashion',
                'description' => 'Wearable basics with clean style and durable materials.',
            ],
        );

        $home = Category::updateOrCreate(
            ['slug' => 'home-living'],
            [
                'name' => 'Home Living',
                'description' => 'Practical upgrades for kitchens, desks, and daily routines.',
            ],
        );

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
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['slug' => $product['slug']],
                [
                    ...$product,
                    'is_active' => true,
                ],
            );
        }
    }
}
