<?php

namespace App\Services;

use App\Models\User;
use App\Models\Income;
use App\Models\Expense;
use App\Models\Budget;
use App\Models\Account;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FinancialHealthService
{
    /**
     * Calculate and return the full health score profile for a user.
     */
    public function calculateScore(int $userId): array
    {
        $breakdown = [
            'savings_ratio' => $this->getSavingsRatioScore($userId),
            'budget_adherence' => $this->getBudgetAdherenceScore($userId),
            'expense_diversity' => $this->getExpenseDiversityScore($userId),
            'account_health' => $this->getAccountHealthScore($userId),
            'consistency' => $this->getConsistencyScore($userId),
        ];

        $totalScore = array_sum(array_column($breakdown, 'score'));
        
        // Collect recommendations from any components that didn't score perfectly
        $recommendations = [];
        foreach ($breakdown as $key => $data) {
            if ($data['score'] < $data['max']) {
                $recommendations = array_merge($recommendations, $data['recommendations']);
            }
        }

        return [
            'score' => min((int) round($totalScore), 100),
            'breakdown' => $breakdown,
            'recommendations' => array_slice(array_unique($recommendations), 0, 5), // Top 5 unique tips
        ];
    }

    /**
     * 30% Weight: Savings Ratio (Income - Expenses / Income)
     */
    private function getSavingsRatioScore(int $userId): array
    {
        $maxScore = 30;
        
        $currentMonth = Carbon::now();
        $incomes = Income::where('user_id', $userId)
            ->whereMonth('income_date', $currentMonth->month)
            ->whereYear('income_date', $currentMonth->year)
            ->sum('amount');
            
        $expenses = Expense::where('user_id', $userId)
            ->whereMonth('expense_date', $currentMonth->month)
            ->whereYear('expense_date', $currentMonth->year)
            ->sum('amount');

        if ($incomes == 0) {
            return [
                'score' => 0,
                'max' => $maxScore,
                'label' => 'Savings Ratio',
                'description' => 'No income recorded this month.',
                'recommendations' => ['Record your regular incomes to track your savings potential.']
            ];
        }

        $savings = $incomes - $expenses;
        $ratio = ($savings / $incomes) * 100;
        
        $score = 0;
        $recommendations = [];
        
        if ($ratio >= 20) {
            $score = $maxScore;
            $description = "Excellent! You are saving over 20% of your income.";
        } elseif ($ratio >= 10) {
            $score = 20;
            $description = "Good. You are saving between 10% and 20% of your income.";
            $recommendations[] = "Try to reduce non-essential expenses to push your savings rate above 20%.";
        } elseif ($ratio > 0) {
            $score = 10;
            $description = "Warning. Your savings rate is positive but very low (< 10%).";
            $recommendations[] = "You are spending almost everything you earn. Start cutting back on discretionary spending.";
        } else {
            $score = 0;
            $description = "Danger. You are spending more than you earn this month.";
            $recommendations[] = "Stop any non-essential spending immediately. You have a negative savings rate.";
        }

        return [
            'score' => $score,
            'max' => $maxScore,
            'label' => 'Savings Ratio',
            'description' => $description,
            'recommendations' => $recommendations
        ];
    }

    /**
     * 25% Weight: Budget Adherence
     */
    private function getBudgetAdherenceScore(int $userId): array
    {
        $maxScore = 25;
        $budgets = Budget::where('user_id', $userId)->active()->get();
        
        if ($budgets->isEmpty()) {
            return [
                'score' => 0,
                'max' => $maxScore,
                'label' => 'Budget Adherence',
                'description' => 'No active budgets found.',
                'recommendations' => ['Create monthly budgets to control your spending.']
            ];
        }

        $totalBudgets = $budgets->count();
        $onTrackBudgets = 0;
        
        foreach ($budgets as $budget) {
            if ($budget->usage_percent <= 100) {
                $onTrackBudgets++;
            }
        }
        
        $adherenceRatio = $onTrackBudgets / $totalBudgets;
        $score = round($adherenceRatio * $maxScore);
        
        $recommendations = [];
        if ($adherenceRatio == 1) {
            $description = "Perfect! You are adhering to all your budgets.";
        } elseif ($adherenceRatio >= 0.5) {
            $description = "Acceptable. You are on track for most of your budgets.";
            $recommendations[] = "Carefully monitor the budgets you are overspending on.";
        } else {
            $description = "Poor. You are exceeding the limits on a majority of your budgets.";
            $recommendations[] = "Your budgets are constantly exceeded. Either increase the budget caps or strictly limit spending.";
        }
        
        return [
            'score' => $score,
            'max' => $maxScore,
            'label' => 'Budget Adherence',
            'description' => $description,
            'recommendations' => $recommendations
        ];
    }

    /**
     * 15% Weight: Expense Diversity (no single category should dominate > 50%)
     */
    private function getExpenseDiversityScore(int $userId): array
    {
        $maxScore = 15;
        $currentMonth = Carbon::now();
        
        $expenses = Expense::where('user_id', $userId)
            ->whereMonth('expense_date', $currentMonth->month)
            ->whereYear('expense_date', $currentMonth->year)
            ->get();
            
        $totalExpenses = $expenses->sum('amount');
        if ($totalExpenses == 0) {
            return [
                'score' => 5, // Neutral score if no spending yet
                'max' => $maxScore,
                'label' => 'Expense Diversity',
                'description' => 'Not enough expense data this month.',
                'recommendations' => []
            ];
        }
        
        $categories = $expenses->groupBy('category_id')->map(function ($group) {
            return $group->sum('amount');
        });
        
        $highestCategoryAmount = $categories->max();
        $highestPercent = ($highestCategoryAmount / $totalExpenses) * 100;
        
        $score = 0;
        $recommendations = [];
        
        if ($highestPercent <= 40) {
            $score = $maxScore;
            $description = "Healthy distribution. No single category dominates your spending.";
        } elseif ($highestPercent <= 60) {
            $score = 10;
            $description = "Moderate dependency. One category takes up over 40% of your expenses.";
            $recommendations[] = "A large portion of your money goes to one category. Ensure this is for essentials like rent/mortgage.";
        } else {
            $score = 5;
            $description = "High dependency. Over 60% of your spending goes to a single category.";
            $recommendations[] = "Your spending is highly concentrated. Diversify your allocations if possible.";
        }
        
        return [
            'score' => $score,
            'max' => $maxScore,
            'label' => 'Expense Diversity',
            'description' => $description,
            'recommendations' => $recommendations
        ];
    }

    /**
     * 15% Weight: Account Health (positive balances)
     */
    private function getAccountHealthScore(int $userId): array
    {
        $maxScore = 15;
        $accounts = Account::where('user_id', $userId)->get();
        
        if ($accounts->isEmpty()) {
            return [
                'score' => 0,
                'max' => $maxScore,
                'label' => 'Account Health',
                'description' => 'No accounts created.',
                'recommendations' => ['Create accounts (Wallet, Bank, etc.) to track balances.']
            ];
        }

        $totalBalance = $accounts->sum('balance');
        $accountsInOverdraft = $accounts->where('balance', '<', 0)->count();
        
        $score = $maxScore;
        $recommendations = [];
        
        if ($accountsInOverdraft > 0) {
            $score -= ($accountsInOverdraft * 5); // Penalize 5 points per negative account
            $recommendations[] = "You have accounts in negative balance. Refill or transfer funds immediately to avoid fees.";
        }
        
        if ($totalBalance <= 0) {
            $score = 0;
            $description = "Critical: Your overall net worth across tracked accounts is negative or zero.";
            $recommendations[] = "Your total liquidity is negative. Immediate financial re-evaluation is necessary.";
        } else {
            $description = $accountsInOverdraft > 0 ? "You have positive total balance but some accounts are overdrafted." : "Excellent. All tracked accounts are in positive standing.";
        }
        
        return [
            'score' => max(0, $score), // Ensure not below 0
            'max' => $maxScore,
            'label' => 'Account Health',
            'description' => $description,
            'recommendations' => $recommendations
        ];
    }

    /**
     * 15% Weight: Consistency (Regular spending vs massive spikes)
     */
    private function getConsistencyScore(int $userId): array
    {
        $maxScore = 15;
        // Compare this month's spending vs the average of the last 3 months
        
        $currentMonth = Carbon::now();
        $thisMonthSpend = Expense::where('user_id', $userId)
            ->whereMonth('expense_date', $currentMonth->month)
            ->whereYear('expense_date', $currentMonth->year)
            ->sum('amount');
            
        $pastThreeMonthsSpend = 0;
        for ($i = 1; $i <= 3; $i++) {
            $date = Carbon::now()->subMonthsNoOverflow($i);
            $pastThreeMonthsSpend += Expense::where('user_id', $userId)
                ->whereMonth('expense_date', $date->month)
                ->whereYear('expense_date', $date->year)
                ->sum('amount');
        }
        
        $avgPastSpend = $pastThreeMonthsSpend / 3;
        
        if ($avgPastSpend == 0) {
            return [
                'score' => 10, // Neutral
                'max' => $maxScore,
                'label' => 'Consistency',
                'description' => 'Not enough historical data to compare consistency.',
                'recommendations' => []
            ];
        }
        
        $variance = ($thisMonthSpend - $avgPastSpend) / $avgPastSpend; // e.g. 0.5 means 50% more spending
        
        $recommendations = [];
        if ($variance <= 0.10) {
            // Spending is equal or less, or only slightly higher
            $score = $maxScore;
            $description = "Great consistency. Your spending aligns with your historical average.";
        } elseif ($variance <= 0.30) {
            // Spending is up to 30% higher
            $score = 10;
            $description = "Slightly elevated. You are spending somewhat more than your average.";
            $recommendations[] = "Keep an eye on sudden spikes in spending compared to previous months.";
        } else {
            // Massive spike
            $score = 0;
            $description = "High variance! Your spending is severely higher than your historical average.";
            $recommendations[] = "Your spending spiked significantly this month. Review large purchases and categorize them properly.";
        }
        
        return [
            'score' => $score,
            'max' => $maxScore,
            'label' => 'Consistency',
            'description' => $description,
            'recommendations' => $recommendations
        ];
    }
}
