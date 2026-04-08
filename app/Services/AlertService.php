<?php

namespace App\Services;

use App\Models\AlertRule;
use App\Models\Budget;
use App\Models\Account;
use App\Models\Expense;
use Carbon\Carbon;

class AlertService
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Check budget threshold rules.
     */
    public function checkBudgetThresholds(int $userId): void
    {
        $rules = AlertRule::where('user_id', $userId)
            ->active()
            ->forType('budget_threshold')
            ->get();

        if ($rules->isEmpty()) {
            return;
        }

        $activeBudgets = Budget::where('user_id', $userId)->active()->get();

        foreach ($rules as $rule) {
            $threshold = $rule->conditions['threshold_percent'] ?? 100;
            
            foreach ($activeBudgets as $budget) {
                // Check if budget usage exceeds threshold
                if ($budget->usage_percent >= $threshold) {
                    // Prevent duplicate alerts in a short timeframe (e.g. 1 day)
                    if (!$rule->last_triggered_at || $rule->last_triggered_at->diffInDays(now()) >= 1) {
                        $this->triggerAlert($rule, [
                            'title' => "Budget Alert: {$budget->name}",
                            'message' => "You have used {$budget->usage_percent}% of your budget.",
                            'budget_id' => $budget->id,
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Check low balance rules after a transaction.
     */
    public function checkLowBalance(int $userId, Account $account): void
    {
        $rules = AlertRule::where('user_id', $userId)
            ->active()
            ->forType('low_balance')
            ->get();

        foreach ($rules as $rule) {
            $minBalance = $rule->conditions['amount'] ?? 0;

            if ($account->balance < $minBalance) {
                // Prevent duplicate alerts in a short timeframe
                if (!$rule->last_triggered_at || $rule->last_triggered_at->diffInDays(now()) >= 1) {
                    $this->triggerAlert($rule, [
                        'title' => "Low Balance: {$account->name}",
                        'message' => "Your account balance has dropped to " . number_format($account->balance, 2) . ". The alert threshold is " . number_format($minBalance, 2) . ".",
                        'account_id' => $account->id,
                    ]);
                }
            }
        }
    }

    /**
     * Check for large individual expenses.
     */
    public function checkLargeExpense(int $userId, Expense $expense): void
    {
        $rules = AlertRule::where('user_id', $userId)
            ->active()
            ->forType('large_expense')
            ->get();

        foreach ($rules as $rule) {
            $maxExpense = $rule->conditions['amount'] ?? 999999;

            if ($expense->amount >= $maxExpense) {
                // Don't throttle large expense alerts by day, just trigger it per expense
                $this->triggerAlert($rule, [
                    'title' => "Large Expense Alert",
                    'message' => "A large expense of " . number_format($expense->amount, 2) . " was recorded.",
                    'expense_id' => $expense->id,
                ]);
            }
        }
    }

    /**
     * Trigger the alert and create a notification.
     */
    protected function triggerAlert(AlertRule $rule, array $data): void
    {
        $this->notificationService->create(
            $rule->user_id,
            $data['title'],
            $data['message'],
            $rule->type,
            $data
        );

        $rule->update([
            'last_triggered_at' => Carbon::now(),
        ]);
    }
}
