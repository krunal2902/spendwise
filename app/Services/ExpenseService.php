<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ExpenseService
{
    public function __construct(
        private ActivityLogService $activityLogService,
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

            $expense = $user->expenses()->create($data);

            // Decrease account balance
            $account->decrement('balance', $expense->amount);

            $this->activityLogService->log('created', $expense, null, $expense->toArray());

            return $expense;
        });
    }

    /**
     * Update expense and adjust account balance.
     */
    public function update(Expense $expense, array $data): Expense
    {
        return DB::transaction(function () use ($expense, $data) {
            $oldValues = $expense->toArray();
            $oldAmount = $expense->amount;
            $oldAccountId = $expense->account_id;

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

            $expense->update($data);
            $expense->refresh();

            $this->activityLogService->log('updated', $expense, $oldValues, $expense->toArray());

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
}
