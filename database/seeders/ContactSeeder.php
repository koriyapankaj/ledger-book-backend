<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
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
            $this->createSampleContactsForUser($user);
        }

        echo "✅ Sample contacts created for " . $users->count() . " user(s)!\n";
    }

    /**
     * Create sample contacts for a user
     */
    private function createSampleContactsForUser(User $user): void
    {
        $sampleContacts = [
            [
                'name' => 'Amit Kumar',
                'email' => 'amit@example.com',
                'phone' => '+91 98765 43210',
                'balance' => 0.00, // Settled
                'notes' => 'Office colleague',
            ],
            [
                'name' => 'Rahul Sharma',
                'email' => 'rahul@example.com',
                'phone' => '+91 98765 43211',
                'balance' => 500.00, // He owes you ₹500
                'notes' => 'Friend - breakfast payments',
            ],
            [
                'name' => 'Priya Patel',
                'email' => 'priya@example.com',
                'phone' => '+91 98765 43212',
                'balance' => -1000.00, // You owe her ₹1000
                'notes' => 'Borrowed for emergency',
            ],
        ];

        foreach ($sampleContacts as $contact) {
            Contact::create([
                'user_id' => $user->id,
                'name' => $contact['name'],
                'email' => $contact['email'],
                'phone' => $contact['phone'],
                'balance' => $contact['balance'],
                'notes' => $contact['notes'],
                'is_active' => true,
            ]);
        }
    }
}