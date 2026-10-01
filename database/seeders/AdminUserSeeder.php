<?php

namespace Database\Seeders;

use App\Enum\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'System Admin',
                'email' => 'admin@restaurant.com',
                'password' => Hash::make('password123'),
                'role' => UserRole::ADMIN,
            ],
            [
                'name' => 'Waiter John',
                'email' => 'waiter@restaurant.com',
                'password' => Hash::make('password123'),
                'role' => UserRole::WAITER,
            ],
            [
                'name' => 'Cashier Sarah',
                'email' => 'cashier@restaurant.com',
                'password' => Hash::make('password123'),
                'role' => UserRole::CASHIER,
            ],
            [
                'name' => 'Chef Mario',
                'email' => 'kitchen@restaurant.com',
                'password' => Hash::make('password123'),
                'role' => UserRole::KITCHEN_STAFF,
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}
