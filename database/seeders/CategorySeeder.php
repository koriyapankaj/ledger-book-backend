<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users or create a default user for testing
        $users = User::all();

        if ($users->isEmpty()) {
            echo "⚠️  No users found. Please create users first or run UserSeeder.\n";
            return;
        }

        foreach ($users as $user) {
            $this->createCategoriesForUser($user);
        }

        echo "✅ Categories seeded successfully for " . $users->count() . " user(s)!\n";
    }

    /**
     * Create default categories for a user
     */
    private function createCategoriesForUser(User $user): void
    {
        // Income Categories
        $incomeCategories = [
            [
                'name' => 'Salary',
                'type' => 'income',
                'color' => '#10B981',
                'icon' => 'briefcase',
                'order' => 1,
            ],
            [
                'name' => 'Freelance',
                'type' => 'income',
                'color' => '#3B82F6',
                'icon' => 'laptop',
                'order' => 2,
            ],
            [
                'name' => 'Investment Returns',
                'type' => 'income',
                'color' => '#8B5CF6',
                'icon' => 'trending-up',
                'order' => 3,
            ],
            [
                'name' => 'Rental Income',
                'type' => 'income',
                'color' => '#F59E0B',
                'icon' => 'home',
                'order' => 4,
            ],
            [
                'name' => 'Business Income',
                'type' => 'income',
                'color' => '#06B6D4',
                'icon' => 'store',
                'order' => 5,
            ],
            [
                'name' => 'Other Income',
                'type' => 'income',
                'color' => '#6B7280',
                'icon' => 'plus-circle',
                'order' => 6,
            ],
        ];

        // Expense Categories with Subcategories
        $expenseCategories = [
            // Food & Dining
            [
                'name' => 'Food & Dining',
                'type' => 'expense',
                'color' => '#EF4444',
                'icon' => 'utensils',
                'order' => 1,
                'subcategories' => [
                    ['name' => 'Breakfast', 'icon' => 'coffee', 'color' => '#F87171'],
                    ['name' => 'Lunch', 'icon' => 'utensils', 'color' => '#EF4444'],
                    ['name' => 'Dinner', 'icon' => 'moon', 'color' => '#DC2626'],
                    ['name' => 'Groceries', 'icon' => 'shopping-cart', 'color' => '#B91C1C'],
                    ['name' => 'Restaurants', 'icon' => 'store', 'color' => '#991B1B'],
                    ['name' => 'Fast Food', 'icon' => 'pizza', 'color' => '#7F1D1D'],
                ],
            ],
            
            // Transportation
            [
                'name' => 'Transportation',
                'type' => 'expense',
                'color' => '#F59E0B',
                'icon' => 'car',
                'order' => 2,
                'subcategories' => [
                    ['name' => 'Fuel', 'icon' => 'fuel', 'color' => '#FBBF24'],
                    ['name' => 'Public Transport', 'icon' => 'bus', 'color' => '#F59E0B'],
                    ['name' => 'Auto/Cab', 'icon' => 'taxi', 'color' => '#D97706'],
                    ['name' => 'Vehicle Maintenance', 'icon' => 'tool', 'color' => '#B45309'],
                    ['name' => 'Parking', 'icon' => 'parking', 'color' => '#92400E'],
                ],
            ],

            // Shopping
            [
                'name' => 'Shopping',
                'type' => 'expense',
                'color' => '#EC4899',
                'icon' => 'shopping-bag',
                'order' => 3,
                'subcategories' => [
                    ['name' => 'Clothing', 'icon' => 'shirt', 'color' => '#F472B6'],
                    ['name' => 'Electronics', 'icon' => 'smartphone', 'color' => '#EC4899'],
                    ['name' => 'Home & Kitchen', 'icon' => 'home', 'color' => '#DB2777'],
                    ['name' => 'Books', 'icon' => 'book', 'color' => '#BE185D'],
                    ['name' => 'Gifts', 'icon' => 'gift', 'color' => '#9F1239'],
                ],
            ],

            // Entertainment
            [
                'name' => 'Entertainment',
                'type' => 'expense',
                'color' => '#8B5CF6',
                'icon' => 'film',
                'order' => 4,
                'subcategories' => [
                    ['name' => 'Movies', 'icon' => 'film', 'color' => '#A78BFA'],
                    ['name' => 'Sports', 'icon' => 'activity', 'color' => '#8B5CF6'],
                    ['name' => 'Gaming', 'icon' => 'gamepad', 'color' => '#7C3AED'],
                    ['name' => 'Music', 'icon' => 'music', 'color' => '#6D28D9'],
                    ['name' => 'Subscriptions', 'icon' => 'tv', 'color' => '#5B21B6'],
                ],
            ],

            // Bills & Utilities
            [
                'name' => 'Bills & Utilities',
                'type' => 'expense',
                'color' => '#06B6D4',
                'icon' => 'file-text',
                'order' => 5,
                'subcategories' => [
                    ['name' => 'Electricity', 'icon' => 'zap', 'color' => '#22D3EE'],
                    ['name' => 'Water', 'icon' => 'droplet', 'color' => '#06B6D4'],
                    ['name' => 'Gas', 'icon' => 'flame', 'color' => '#0891B2'],
                    ['name' => 'Internet', 'icon' => 'wifi', 'color' => '#0E7490'],
                    ['name' => 'Mobile', 'icon' => 'phone', 'color' => '#155E75'],
                    ['name' => 'DTH/Cable', 'icon' => 'tv', 'color' => '#164E63'],
                ],
            ],

            // Healthcare
            [
                'name' => 'Healthcare',
                'type' => 'expense',
                'color' => '#10B981',
                'icon' => 'heart',
                'order' => 6,
                'subcategories' => [
                    ['name' => 'Doctor Visits', 'icon' => 'user-md', 'color' => '#34D399'],
                    ['name' => 'Medicines', 'icon' => 'pill', 'color' => '#10B981'],
                    ['name' => 'Lab Tests', 'icon' => 'flask', 'color' => '#059669'],
                    ['name' => 'Health Insurance', 'icon' => 'shield', 'color' => '#047857'],
                    ['name' => 'Gym/Fitness', 'icon' => 'dumbbell', 'color' => '#065F46'],
                ],
            ],

            // Education
            [
                'name' => 'Education',
                'type' => 'expense',
                'color' => '#3B82F6',
                'icon' => 'book-open',
                'order' => 7,
                'subcategories' => [
                    ['name' => 'Courses', 'icon' => 'graduation-cap', 'color' => '#60A5FA'],
                    ['name' => 'Books & Materials', 'icon' => 'book', 'color' => '#3B82F6'],
                    ['name' => 'Tuition Fees', 'icon' => 'school', 'color' => '#2563EB'],
                    ['name' => 'Training', 'icon' => 'certificate', 'color' => '#1D4ED8'],
                ],
            ],

            // Housing & Rent
            [
                'name' => 'Housing',
                'type' => 'expense',
                'color' => '#F97316',
                'icon' => 'home',
                'order' => 8,
                'subcategories' => [
                    ['name' => 'Rent', 'icon' => 'key', 'color' => '#FB923C'],
                    ['name' => 'Home Maintenance', 'icon' => 'tool', 'color' => '#F97316'],
                    ['name' => 'Property Tax', 'icon' => 'file-text', 'color' => '#EA580C'],
                    ['name' => 'Home Insurance', 'icon' => 'shield', 'color' => '#C2410C'],
                ],
            ],

            // Personal Care
            [
                'name' => 'Personal Care',
                'type' => 'expense',
                'color' => '#14B8A6',
                'icon' => 'user',
                'order' => 9,
                'subcategories' => [
                    ['name' => 'Salon/Grooming', 'icon' => 'scissors', 'color' => '#2DD4BF'],
                    ['name' => 'Cosmetics', 'icon' => 'sparkles', 'color' => '#14B8A6'],
                    ['name' => 'Spa/Wellness', 'icon' => 'spa', 'color' => '#0D9488'],
                ],
            ],

            // Travel
            [
                'name' => 'Travel',
                'type' => 'expense',
                'color' => '#0EA5E9',
                'icon' => 'plane',
                'order' => 10,
                'subcategories' => [
                    ['name' => 'Flight Tickets', 'icon' => 'plane', 'color' => '#38BDF8'],
                    ['name' => 'Hotels', 'icon' => 'hotel', 'color' => '#0EA5E9'],
                    ['name' => 'Vacation', 'icon' => 'beach', 'color' => '#0284C7'],
                    ['name' => 'Travel Insurance', 'icon' => 'shield', 'color' => '#0369A1'],
                ],
            ],

            // Insurance
            [
                'name' => 'Insurance',
                'type' => 'expense',
                'color' => '#6366F1',
                'icon' => 'shield',
                'order' => 11,
                'subcategories' => [
                    ['name' => 'Life Insurance', 'icon' => 'heart', 'color' => '#818CF8'],
                    ['name' => 'Health Insurance', 'icon' => 'medkit', 'color' => '#6366F1'],
                    ['name' => 'Vehicle Insurance', 'icon' => 'car', 'color' => '#4F46E5'],
                ],
            ],

            // Investments & Savings
            [
                'name' => 'Investments',
                'type' => 'expense',
                'color' => '#A855F7',
                'icon' => 'trending-up',
                'order' => 12,
                'subcategories' => [
                    ['name' => 'Mutual Funds', 'icon' => 'chart', 'color' => '#C084FC'],
                    ['name' => 'Stocks', 'icon' => 'activity', 'color' => '#A855F7'],
                    ['name' => 'Fixed Deposits', 'icon' => 'lock', 'color' => '#9333EA'],
                    ['name' => 'Gold', 'icon' => 'gem', 'color' => '#7E22CE'],
                    ['name' => 'Retirement Fund', 'icon' => 'piggy-bank', 'color' => '#6B21A8'],
                ],
            ],

            // Miscellaneous
            [
                'name' => 'Miscellaneous',
                'type' => 'expense',
                'color' => '#6B7280',
                'icon' => 'more-horizontal',
                'order' => 13,
                'subcategories' => [
                    ['name' => 'Donations', 'icon' => 'heart-hand', 'color' => '#9CA3AF'],
                    ['name' => 'Fees & Charges', 'icon' => 'credit-card', 'color' => '#6B7280'],
                    ['name' => 'Fines', 'icon' => 'alert-triangle', 'color' => '#4B5563'],
                    ['name' => 'Other', 'icon' => 'help-circle', 'color' => '#374151'],
                ],
            ],
        ];

        // Create income categories
        foreach ($incomeCategories as $category) {
            Category::create([
                'user_id' => $user->id,
                'name' => $category['name'],
                'type' => $category['type'],
                'color' => $category['color'],
                'icon' => $category['icon'],
                'order' => $category['order'],
                'is_active' => true,
            ]);
        }

        // Create expense categories with subcategories
        foreach ($expenseCategories as $category) {
            $parent = Category::create([
                'user_id' => $user->id,
                'name' => $category['name'],
                'type' => $category['type'],
                'color' => $category['color'],
                'icon' => $category['icon'],
                'order' => $category['order'],
                'is_active' => true,
            ]);

            // Create subcategories if they exist
            if (isset($category['subcategories'])) {
                foreach ($category['subcategories'] as $index => $subcategory) {
                    Category::create([
                        'user_id' => $user->id,
                        'parent_id' => $parent->id,
                        'name' => $subcategory['name'],
                        'type' => 'expense',
                        'color' => $subcategory['color'],
                        'icon' => $subcategory['icon'],
                        'order' => $index + 1,
                        'is_active' => true,
                    ]);
                }
            }
        }
    }
}