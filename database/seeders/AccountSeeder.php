<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            echo "⚠️  No users found. Please create users first.\n";
            return;
        }

        foreach ($users as $user) {
            $this->createDefaultAccountsForUser($user);
        }

        echo "✅ Default accounts created for " . $users->count() . " user(s)!\n";
    }

    /**
     * Create default accounts for a user
     */
    private function createDefaultAccountsForUser(User $user): void
    {
        $defaultAccounts = [
            // Asset Accounts
            [
                'name' => 'Cash',
                'type' => 'asset',
                'subtype' => 'cash',
                'balance' => 5000.00,
                'color' => '#10B981',
                'icon' => 'wallet',
                'include_in_total' => true,
            ],
            [
                'name' => 'Savings Account',
                'type' => 'asset',
                'subtype' => 'bank_account',
                'balance' => 50000.00,
                'color' => '#3B82F6',
                'icon' => 'bank',
                'bank_name' => 'HDFC Bank',
                'include_in_total' => true,
            ],
            [
                'name' => 'PayTM Wallet',
                'type' => 'asset',
                'subtype' => 'digital_wallet',
                'balance' => 2000.00,
                'color' => '#0EA5E9',
                'icon' => 'smartphone',
                'include_in_total' => true,
            ],
            [
                'name' => 'PhonePe Wallet',
                'type' => 'asset',
                'subtype' => 'digital_wallet',
                'balance' => 1500.00,
                'color' => '#8B5CF6',
                'icon' => 'phone',
                'include_in_total' => true,
            ],
            
            // Liability Accounts
            [
                'name' => 'Credit Card - HDFC',
                'type' => 'liability',
                'subtype' => 'credit_card',
                'balance' => 0.00,
                'credit_limit' => 100000.00,
                'color' => '#EF4444',
                'icon' => 'credit-card',
                'bank_name' => 'HDFC Bank',
                'account_number' => '****1234',
                'include_in_total' => true,
            ],
        ];

        foreach ($defaultAccounts as $account) {
            Account::create([
                'user_id' => $user->id,
                'name' => $account['name'],
                'type' => $account['type'],
                'subtype' => $account['subtype'],
                'balance' => $account['balance'],
                'credit_limit' => $account['credit_limit'] ?? null,
                'color' => $account['color'],
                'icon' => $account['icon'],
                'bank_name' => $account['bank_name'] ?? null,
                'account_number' => $account['account_number'] ?? null,
                'include_in_total' => $account['include_in_total'],
                'is_active' => true,
            ]);
        }
    }
}