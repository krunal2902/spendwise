<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\ExpenseHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ExpenseService
{
    public function __construct(
        private ActivityLogService $activityLogService,
        private TagService $tagService,
        private AlertService $alertService,
    ) {}

    /**
     * Create expense and decrease account balance.
     */
    public function create(User $user, array $data): Expense
    {
        return DB::transaction(function () use ($user, $data) {
            $account = $user->accounts()->lockForUpdate()->findOrFail($data['account_id']);

            // Check sufficient balance
            if ($account->balance < $data['amount']) {
                throw new \Exception("Insufficient balance in {$account->name}. Available: ₹" . number_format($account->balance, 2));
            }

            // Check if category is locked
            $category = \App\Models\Category::find($data['category_id']);
            if ($category && $category->is_locked) {
                throw new \Exception("Category '{$category->name}' is locked. You cannot add expenses to a locked category.");
            }

            // Check Emergency Mode
            if ($user->emergency_mode && $category && !$category->is_essential) {
                throw new \Exception("Emergency Mode is ACTIVE. You cannot log non-essential expenses like '{$category->name}'.");
            }

            // Extract tags before creating expense
            $tags = $data['tags'] ?? [];
            unset($data['tags']);

            $expense = $user->expenses()->create($data);

            // Decrease account balance
            $account->decrement('balance', $expense->amount);

            // Sync tags
            if (!empty($tags)) {
                $this->tagService->syncForExpense($user, $expense, $tags);
            }

            $this->activityLogService->log('created', $expense, null, $expense->toArray());

            // Dispatch alerts
            $this->alertService->checkLowBalance($user->id, clone $account);
            $this->alertService->checkLargeExpense($user->id, clone $expense);
            $this->alertService->checkBudgetThresholds($user->id);

            return $expense;
        });
    }

    /**
     * Update expense and adjust account balance.
     */
    public function update(Expense $expense, array $data): Expense
    {
        return DB::transaction(function () use ($expense, $data) {
            $user = $expense->user;
            
            // Check Emergency Mode on Update
            if ($user->emergency_mode && isset($data['category_id'])) {
                $category = \App\Models\Category::find($data['category_id']);
                if ($category && !$category->is_essential) {
                    throw new \Exception("Emergency Mode is ACTIVE. You cannot change this expense to a non-essential category.");
                }
            }

            $oldValues = $expense->toArray();
            $oldAmount = $expense->amount;
            $oldAccountId = $expense->account_id;

            // Extract tags before updating
            $tags = $data['tags'] ?? [];
            unset($data['tags']);

            if ($oldAccountId !== ($data['account_id'] ?? $oldAccountId)) {
                // Account changed — reverse old, apply new
                $oldAccount = \App\Models\Account::lockForUpdate()->findOrFail($oldAccountId);
                $newAccount = \App\Models\Account::lockForUpdate()->findOrFail($data['account_id']);

                if ($newAccount->balance < $data['amount']) {
                    throw new \Exception("Insufficient balance in {$newAccount->name}.");
                }

                $oldAccount->increment('balance', $oldAmount);
                $newAccount->decrement('balance', $data['amount']);
            } else {
                // Same account — adjust difference
                $account = \App\Models\Account::lockForUpdate()->findOrFail($oldAccountId);
                $difference = ($data['amount'] ?? $oldAmount) - $oldAmount;

                if ($difference > 0 && $account->balance < $difference) {
                    throw new \Exception("Insufficient balance in {$account->name}.");
                }

                if ($difference != 0) {
                    $account->decrement('balance', $difference);
                }
            }

            // Log edit history before updating
            $this->logHistory($expense, $oldValues, $data);

            $expense->update($data);
            $expense->refresh();

            // Sync tags
            $this->tagService->syncForExpense($expense->user, $expense, $tags);

            $this->activityLogService->log('updated', $expense, $oldValues, $expense->toArray());

            // Dispatch alerts (re-check balance and budget)
            $this->alertService->checkLowBalance($expense->user_id, clone $expense->account);
            $this->alertService->checkBudgetThresholds($expense->user_id);

            return $expense;
        });
    }

    /**
     * Delete expense and restore account balance.
     */
    public function delete(Expense $expense): void
    {
        DB::transaction(function () use ($expense) {
            $oldValues = $expense->toArray();

            // Restore account balance
            $expense->account()->increment('balance', $expense->amount);

            $this->activityLogService->log('deleted', $expense, $oldValues, null);

            $expense->delete();
        });
    }

    /**
     * Log expense edit history.
     */
    private function logHistory(Expense $expense, array $oldValues, array $newValues): void
    {
        // Build a human-readable change summary
        $changes = [];
        $trackFields = ['amount', 'account_id', 'category_id', 'description', 'expense_date', 'reference', 'notes'];

        foreach ($trackFields as $field) {
            if (isset($newValues[$field]) && (string) ($oldValues[$field] ?? '') !== (string) $newValues[$field]) {
                $changes[] = str_replace('_', ' ', $field);
            }
        }

        if (empty($changes)) {
            return; // No meaningful changes
        }

        ExpenseHistory::create([
            'expense_id'     => $expense->id,
            'user_id'        => $expense->user_id,
            'old_values'     => array_intersect_key($oldValues, array_flip($trackFields)),
            'new_values'     => array_intersect_key($newValues, array_flip($trackFields)),
            'change_summary' => 'Changed: ' . implode(', ', $changes),
        ]);
    }
}
