<?php

namespace App\Services;

use App\Models\Income;
use App\Models\Expense;
use App\Models\Transfer;
use App\Models\Account;
use App\Models\Category;
use App\Models\Budget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportService
{
    /**
     * Get the account report data
     */
    public function getAccountReport(int $userId, array $filters = []): array
    {
        $dateFrom = $filters['date_from'] ?? Carbon::now()->startOfMonth()->toDateString();
        $dateTo = $filters['date_to'] ?? Carbon::now()->endOfMonth()->toDateString();
        $accountId = $filters['account_id'] ?? null;

        $accountsQuery = Account::where('user_id', $userId);
        if ($accountId) {
            $accountsQuery->where('id', $accountId);
        }
        $accounts = $accountsQuery->get();
        $accountIds = $accounts->pluck('id')->toArray();

        $incomesQuery = Income::where('user_id', $userId)
            ->whereIn('account_id', $accountIds)
            ->whereBetween('income_date', [$dateFrom, $dateTo]);

        $expensesQuery = Expense::where('user_id', $userId)
            ->whereIn('account_id', $accountIds)
            ->whereBetween('expense_date', [$dateFrom, $dateTo]);

        $transfersOutQuery = Transfer::where('user_id', $userId)
            ->whereIn('from_account_id', $accountIds)
            ->whereBetween('transfer_date', [$dateFrom, $dateTo]);
            
        $transfersInQuery = Transfer::where('user_id', $userId)
            ->whereIn('to_account_id', $accountIds)
            ->whereBetween('transfer_date', [$dateFrom, $dateTo]);

        $totalIncome = $incomesQuery->sum('amount');
        $totalExpense = $expensesQuery->sum('amount');
        
        // Group by account
        $accountData = [];
        $labels = [];
        $incomeData = [];
        $expenseData = [];
        
        foreach ($accounts as $account) {
            $accIncome = Income::where('account_id', $account->id)->whereBetween('income_date', [$dateFrom, $dateTo])->sum('amount');
            $accExpense = Expense::where('account_id', $account->id)->whereBetween('expense_date', [$dateFrom, $dateTo])->sum('amount');
            $transferOut = Transfer::where('from_account_id', $account->id)->whereBetween('transfer_date', [$dateFrom, $dateTo])->sum('amount');
            $transferIn = Transfer::where('to_account_id', $account->id)->whereBetween('transfer_date', [$dateFrom, $dateTo])->sum('amount');
            
            $netChange = $accIncome - $accExpense + $transferIn - $transferOut;
            
            $accountData[] = [
                'account' => $account,
                'income' => $accIncome,
                'expense' => $accExpense,
                'transfer_in' => $transferIn,
                'transfer_out' => $transferOut,
                'net_change' => $netChange
            ];
            
            $labels[] = $account->name;
            $incomeData[] = $accIncome;
            $expenseData[] = $accExpense;
        }

        return [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'account_id' => $accountId,
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'accounts' => $accounts,
            'account_data' => $accountData,
            'chart_data' => [
                'labels' => $labels,
                'income' => $incomeData,
                'expense' => $expenseData,
            ]
        ];
    }

    /**
     * Get the category report data
     */
    public function getCategoryReport(int $userId, array $filters = []): array
    {
        $dateFrom = $filters['date_from'] ?? Carbon::now()->startOfMonth()->toDateString();
        $dateTo = $filters['date_to'] ?? Carbon::now()->endOfMonth()->toDateString();
        $categoryId = $filters['category_id'] ?? null;

        $expensesQuery = Expense::where('user_id', $userId)
            ->whereBetween('expense_date', [$dateFrom, $dateTo]);

        if ($categoryId) {
            $expensesQuery->where('category_id', $categoryId);
        }

        $totalExpense = $expensesQuery->sum('amount');
        
        $categoriesDesc = Expense::where('user_id', $userId)
            ->whereBetween('expense_date', [$dateFrom, $dateTo])
            ->when($categoryId, function($q) use ($categoryId) {
                return $q->where('category_id', $categoryId);
            })
            ->select('category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->with('category')
            ->get();
            
        $labels = [];
        $data = [];
        $colors = [];
        
        foreach ($categoriesDesc as $item) {
            if ($item->category) {
                $labels[] = $item->category->name;
                $data[] = $item->total;
                $colors[] = $item->category->color ?? '#' . substr(md5($item->category->name), 0, 6);
            }
        }
        
        $categories = Category::where('user_id', $userId)->orWhereNull('user_id')->get();

        return [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'category_id' => $categoryId,
            'total_expense' => $totalExpense,
            'categories' => $categories,
            'category_data' => $categoriesDesc,
            'chart_data' => [
                'labels' => $labels,
                'data' => $data,
                'colors' => $colors,
            ]
        ];
    }

    /**
     * Get detailed analytics for a specific budget
     */
    public function getBudgetAnalysis(int $budgetId): array
    {
        $budget = Budget::with('categoryBudgets.category')->findOrFail($budgetId);
        $userId = $budget->user_id;

        $budgeted = $budget->amount;
        $spent = $budget->spent;
        
        $variance = $budgeted - $spent;
        $usagePercent = $budget->usage_percent;

        $categoryBudgets = [];
        $totalCategoryBudgeted = 0;
        
        foreach ($budget->categoryBudgets as $cb) {
            $catSpentQuery = Expense::where('user_id', $userId)
                ->where('category_id', $cb->category_id);
                
            if ($budget->is_custom) {
                $catSpentQuery->whereBetween('expense_date', [$budget->start_date, $budget->end_date]);
            } else {
                $catSpentQuery->whereMonth('expense_date', $budget->month)
                              ->whereYear('expense_date', $budget->year);
            }
            
            $catSpent = (float) $catSpentQuery->sum('amount');
            $catBudget = (float) $cb->amount;
            $catVariance = $catBudget - $catSpent;
            $catUsagePercent = $catBudget > 0 ? round(($catSpent / $catBudget) * 100, 1) : 0;
            
            $totalCategoryBudgeted += $catBudget;

            $categoryBudgets[] = [
                'category' => $cb->category,
                'budgeted' => $catBudget,
                'spent' => $catSpent,
                'variance' => $catVariance,
                'usage_percent' => $catUsagePercent
            ];
        }
        
        // Month-over-month trend logic (only applicable if standard monthly budget)
        $trendData = [];
        if (!$budget->is_custom) {
            $prevMonth = $budget->month - 1;
            $prevYear = $budget->year;
            if ($prevMonth == 0) {
                $prevMonth = 12;
                $prevYear--;
            }
            
            $prevSpent = (float) Expense::where('user_id', $userId)
                ->whereMonth('expense_date', $prevMonth)
                ->whereYear('expense_date', $prevYear)
                ->sum('amount');
                
            $trendData = [
                'previous_spent' => $prevSpent,
                'current_spent' => $spent,
                'difference' => $spent - $prevSpent,
            ];
        }

        return [
            'budgeted' => $budgeted,
            'spent' => $spent,
            'variance' => $variance,
            'usage_percent' => $usagePercent,
            'category_budgets' => $categoryBudgets,
            'unallocated' => max(0, $budgeted - $totalCategoryBudgeted),
            'trend' => $trendData
        ];
    }
}
