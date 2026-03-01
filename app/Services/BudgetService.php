<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\CategoryBudget;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class BudgetService
{
    public function __construct(
        private ActivityLogService $activityLogService,
    ) {}

    /**
     * Get budgets for a specific user, optionally filtered by month/year.
     */
    public function getForUser(User $user, ?int $month = null, ?int $year = null): Collection
    {
        $query = $user->budgets()->with('categoryBudgets.category')->orderByDesc('year')->orderByDesc('month');

        if ($month && $year) {
            $query->forMonth($month, $year);
        } elseif ($year) {
            $query->forYear($year);
        }

        return $query->get();
    }

    /**
     * Create a new budget.
     */
    public function create(User $user, array $data): Budget
    {
        $budget = $user->budgets()->create($data);

        $this->activityLogService->log('created', $budget, null, $budget->toArray());

        return $budget;
    }

    /**
     * Update an existing budget.
     */
    public function update(Budget $budget, array $data): Budget
    {
        $oldValues = $budget->toArray();

        $budget->update($data);
        $budget->refresh();

        $this->activityLogService->log('updated', $budget, $oldValues, $budget->toArray());

        return $budget;
    }

    /**
     * Delete a budget.
     */
    public function delete(Budget $budget): void
    {
        $oldValues = $budget->toArray();

        $this->activityLogService->log('deleted', $budget, $oldValues, null);

        $budget->delete();
    }

    /**
     * Get a summary for a given month/year.
     */
    public function getMonthlySummary(User $user, int $month, int $year): array
    {
        $budgets = $user->budgets()->with('categoryBudgets.category')->forMonth($month, $year)->get();

        $totalBudgeted = $budgets->sum('amount');
        $totalSpent = (float) $user->expenses()
            ->whereMonth('expense_date', $month)
            ->whereYear('expense_date', $year)
            ->sum('amount');

        return [
            'total_budgeted' => $totalBudgeted,
            'total_spent'    => $totalSpent,
            'remaining'      => $totalBudgeted - $totalSpent,
            'usage_percent'  => $totalBudgeted > 0
                ? round(($totalSpent / $totalBudgeted) * 100, 1)
                : 0,
            'budgets'        => $budgets,
        ];
    }

    /**
     * Sync category budgets for a given budget.
     * Accepts an array of ['category_id' => ..., 'amount' => ...] entries.
     */
    public function syncCategoryBudgets(Budget $budget, array $categories): void
    {
        DB::transaction(function () use ($budget, $categories) {
            // Delete existing category budgets
            $budget->categoryBudgets()->delete();

            // Insert new ones
            foreach ($categories as $entry) {
                if (!empty($entry['category_id']) && !empty($entry['amount']) && $entry['amount'] > 0) {
                    $budget->categoryBudgets()->create([
                        'category_id' => $entry['category_id'],
                        'amount'      => $entry['amount'],
                    ]);
                }
            }
        });

        $this->activityLogService->log('updated', $budget, ['action' => 'category_budgets_synced'], [
            'category_count' => count($categories),
        ]);
    }

    /**
     * Get category budget breakdown for a budget, with spent amounts.
     */
    public function getCategoryBreakdown(Budget $budget): Collection
    {
        return $budget->categoryBudgets()->with('category')->get();
    }
}
