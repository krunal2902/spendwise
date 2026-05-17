<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed the default admin user.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@expenseflow.com'],
            [
                'name'     => 'Admin',
                'password' => Hash::make('password'),
                'role'     => 'admin',
                'phone'    => null,
                'currency' => 'INR',
            ]
        );

        User::firstOrCreate(
            ['email' => 'buddy@expenseflow.com'],
            [
                'name'     => 'Test Buddy',
                'password' => Hash::make('password'),
                'role'     => 'user',
                'phone'    => null,
                'currency' => 'INR',
            ]
        );
    }
}
