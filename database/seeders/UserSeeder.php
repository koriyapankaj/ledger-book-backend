<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a default test user
        User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'currency' => 'INR',
            'timezone' => 'Asia/Kolkata',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create another user for testing multi-user scenarios
        User::create([
            'name' => 'Demo User',
            'email' => 'demo@example.com',
            'password' => Hash::make('password'),
            'currency' => 'INR',
            'timezone' => 'Asia/Kolkata',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        echo "✅ Test users created successfully!\n";
        echo "   📧 Email: test@example.com | Password: password\n";
        echo "   📧 Email: demo@example.com | Password: password\n";
    }
}