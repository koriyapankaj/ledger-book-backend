<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            AccountSeeder::class,
            ContactSeeder::class,
        ]);

        echo "\n";
        echo "🎉 ===============================================\n";
        echo "   Database seeding completed successfully!\n";
        echo "   ===============================================\n";
        echo "\n";
        echo "   📊 Seeded:\n";
        echo "      - Users with test accounts\n";
        echo "      - Complete category structure (Income & Expense)\n";
        echo "      - Default accounts (Cash, Bank, Wallets, Credit Card)\n";
        echo "      - Sample contacts for debt tracking\n";
        echo "\n";
        echo "   🔐 Login Credentials:\n";
        echo "      Email: test@example.com\n";
        echo "      Password: password\n";
        echo "\n";
    }
}