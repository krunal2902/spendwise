<?php

namespace App\Services;

use App\Models\Account;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AccountService
{
    /**
     * Create a new account for the user.
     */
    public function create(User $user, array $data): Account
    {
        return $user->accounts()->create($data);
    }

    /**
     * Update an existing account.
     */
    public function update(Account $account, array $data): Account
    {
        $account->update($data);
        return $account->fresh();
    }

    /**
     * Delete an account (only if no transactions exist).
     *
     * @throws \Exception
     */
    public function delete(Account $account): void
    {
        if ($account->hasTransactions()) {
            throw new \Exception('Cannot delete account with existing transactions. Please remove or reassign them first.');
        }

        $account->delete();
    }

    /**
     * Recalculate account balance from all transactions.
     * Safety mechanism for data integrity.
     */
    public function recalculateBalance(Account $account): void
    {
        $totalIncome = $account->incomes()->sum('amount');
        $totalExpense = $account->expenses()->sum('amount');
        $totalOutgoing = $account->outgoingTransfers()->selectRaw('SUM(amount + fee) as total')->value('total') ?? 0;
        $totalIncoming = $account->incomingTransfers()->sum('amount');

        $calculatedBalance = $totalIncome - $totalExpense - $totalOutgoing + $totalIncoming;

        // Add the initial balance (balance at creation time is stored separately or assumed 0)
        $account->update(['balance' => $calculatedBalance]);
    }

    /**
     * Get total balance across all active accounts for a user.
     */
    public function getTotalBalance(User $user): float
    {
        return (float) $user->accounts()->active()->sum('balance');
    }
}
