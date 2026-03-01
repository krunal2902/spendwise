<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Seed system default categories.
     */
    public function run(): void
    {
        $categories = [
            // Income Categories
            ['name' => 'Salary',        'type' => 'income',  'icon' => 'fa-briefcase',     'color' => '#10B981'],
            ['name' => 'Freelance',     'type' => 'income',  'icon' => 'fa-laptop',        'color' => '#3B82F6'],
            ['name' => 'Investment',    'type' => 'income',  'icon' => 'fa-chart-line',    'color' => '#8B5CF6'],
            ['name' => 'Gift',          'type' => 'income',  'icon' => 'fa-gift',          'color' => '#F59E0B'],
            ['name' => 'Refund',        'type' => 'income',  'icon' => 'fa-rotate-left',   'color' => '#6366F1'],
            ['name' => 'Other Income',  'type' => 'income',  'icon' => 'fa-ellipsis',      'color' => '#6B7280'],

            // Expense Categories
            ['name' => 'Food & Dining',     'type' => 'expense', 'icon' => 'fa-utensils',        'color' => '#EF4444'],
            ['name' => 'Transportation',    'type' => 'expense', 'icon' => 'fa-car',             'color' => '#F97316'],
            ['name' => 'Shopping',          'type' => 'expense', 'icon' => 'fa-bag-shopping',    'color' => '#EC4899'],
            ['name' => 'Entertainment',     'type' => 'expense', 'icon' => 'fa-film',            'color' => '#A855F7'],
            ['name' => 'Bills & Utilities', 'type' => 'expense', 'icon' => 'fa-file-invoice',    'color' => '#F43F5E'],
            ['name' => 'Healthcare',        'type' => 'expense', 'icon' => 'fa-heart-pulse',     'color' => '#14B8A6'],
            ['name' => 'Education',         'type' => 'expense', 'icon' => 'fa-graduation-cap',  'color' => '#0EA5E9'],
            ['name' => 'Rent',              'type' => 'expense', 'icon' => 'fa-house',           'color' => '#64748B'],
            ['name' => 'Insurance',         'type' => 'expense', 'icon' => 'fa-shield-halved',   'color' => '#0D9488'],
            ['name' => 'Personal Care',     'type' => 'expense', 'icon' => 'fa-spa',             'color' => '#D946EF'],
            ['name' => 'Travel',            'type' => 'expense', 'icon' => 'fa-plane',           'color' => '#2563EB'],
            ['name' => 'Groceries',         'type' => 'expense', 'icon' => 'fa-cart-shopping',   'color' => '#16A34A'],
            ['name' => 'Subscriptions',     'type' => 'expense', 'icon' => 'fa-rotate',          'color' => '#7C3AED'],
            ['name' => 'Other Expense',     'type' => 'expense', 'icon' => 'fa-ellipsis',        'color' => '#6B7280'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['name' => $category['name'], 'is_system' => true],
                array_merge($category, [
                    'user_id'   => null,
                    'is_system' => true,
                    'is_active' => true,
                ])
            );
        }
    }
}
