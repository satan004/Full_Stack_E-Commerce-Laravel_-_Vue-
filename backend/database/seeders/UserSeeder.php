<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'email' => 'admin@gmail.com',
                'name' => 'Admin Manager',
                'password' => Hash::make('1234567'),
                'is_admin' => true,
            ],
            [
                'email' => 'customer@example.com',
                'name' => 'Demo Customer',
                'password' => Hash::make('password'),
                'phone' => '+855 12 345 678',
                'address' => 'Phnom Penh, Cambodia',
                'is_admin' => false,
            ],
            [
                'email' => 'john.doe@example.com',
                'name' => 'John Doe',
                'password' => Hash::make('password'),
                'phone' => '+855 12 345 679',
                'address' => 'Siem Reap, Cambodia',
                'is_admin' => false,
            ],
            [
                'email' => 'jane.smith@example.com',
                'name' => 'Jane Smith',
                'password' => Hash::make('password'),
                'phone' => '+855 12 345 680',
                'address' => 'Battambang, Cambodia',
                'is_admin' => false,
            ],
            [
                'email' => 'mike.wilson@example.com',
                'name' => 'Mike Wilson',
                'password' => Hash::make('password'),
                'phone' => '+855 12 345 681',
                'address' => 'Sihanoukville, Cambodia',
                'is_admin' => false,
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                $user
            );
        }
    }
}