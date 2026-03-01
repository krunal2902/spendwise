<?php

namespace App\Services;

use App\Models\Income;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class IncomeService
{
    public function __construct(
        private ActivityLogService $activityLogService,
    ) {}

    /**
     * Create income and increase account balance.
     */
    public function create(User $user, array $data): Income
    {
        return DB::transaction(function () use ($user, $data) {
            $income = $user->incomes()->create($data);

            // Increase account balance
            $income->account()->increment('balance', $income->amount);

            // Log activity
            $this->activityLogService->log('created', $income, null, $income->toArray());

            return $income;
        });
    }

    /**
     * Update income and adjust account balance.
     */
    public function update(Income $income, array $data): Income
    {
        return DB::transaction(function () use ($income, $data) {
            $oldValues = $income->toArray();
            $oldAmount = $income->amount;
            $oldAccountId = $income->account_id;

            $income->update($data);
            $income->refresh();

            // If account changed, reverse old and apply to new
            if ($oldAccountId !== $income->account_id) {
                // Reverse from old account
                \App\Models\Account::where('id', $oldAccountId)->decrement('balance', $oldAmount);
                // Apply to new account
                $income->account()->increment('balance', $income->amount);
            } else {
                // Same account — adjust the difference
                $difference = $income->amount - $oldAmount;
                if ($difference != 0) {
                    $income->account()->increment('balance', $difference);
                }
            }

            $this->activityLogService->log('updated', $income, $oldValues, $income->toArray());

            return $income;
        });
    }

    /**
     * Delete income and decrease account balance.
     */
    public function delete(Income $income): void
    {
        DB::transaction(function () use ($income) {
            $oldValues = $income->toArray();

            // Decrease account balance
            $income->account()->decrement('balance', $income->amount);

            $this->activityLogService->log('deleted', $income, $oldValues, null);

            $income->delete();
        });
    }
}
