<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;

class DashboardService
{
    /**
     * Get dashboard summary data for a user.
     */
    public function getSummary(User $user): array
    {
        $currentMonth = Carbon::now();
        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();

        $totalBalance = $user->accounts()->active()->sum('balance');

        $monthlyIncome = $user->incomes()
            ->whereBetween('income_date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $monthlyExpense = $user->expenses()
            ->whereBetween('expense_date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $recentIncome = $user->incomes()
            ->with(['account', 'category'])
            ->latest('income_date')
            ->take(5)
            ->get();

        $recentExpenses = $user->expenses()
            ->with(['account', 'category'])
            ->latest('expense_date')
            ->take(5)
            ->get();

        $recentTransfers = $user->transfers()
            ->with(['fromAccount', 'toAccount'])
            ->latest('transfer_date')
            ->take(5)
            ->get();

        // Monthly expense by category (for chart)
        $expenseByCategory = $user->expenses()
            ->whereBetween('expense_date', [$startOfMonth, $endOfMonth])
            ->join('categories', 'expenses.category_id', '=', 'categories.id')
            ->selectRaw('categories.name as category_name, categories.color, SUM(expenses.amount) as total')
            ->groupBy('categories.id', 'categories.name', 'categories.color')
            ->orderByDesc('total')
            ->get();

        // Last 6 months income vs expense trend
        $monthlyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $start = $month->copy()->startOfMonth();
            $end = $month->copy()->endOfMonth();

            $monthlyTrend[] = [
                'month'   => $month->format('M Y'),
                'income'  => (float) $user->incomes()->whereBetween('income_date', [$start, $end])->sum('amount'),
                'expense' => (float) $user->expenses()->whereBetween('expense_date', [$start, $end])->sum('amount'),
            ];
        }

        return [
            'totalBalance'      => $totalBalance,
            'monthlyIncome'     => $monthlyIncome,
            'monthlyExpense'    => $monthlyExpense,
            'monthlySavings'    => $monthlyIncome - $monthlyExpense,
            'recentIncome'      => $recentIncome,
            'recentExpenses'    => $recentExpenses,
            'recentTransfers'   => $recentTransfers,
            'expenseByCategory' => $expenseByCategory,
            'monthlyTrend'      => $monthlyTrend,
            'accountCount'      => $user->accounts()->active()->count(),
        ];
    }
}
