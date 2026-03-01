<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TransferService
{
    public function __construct(
        private ActivityLogService $activityLogService,
    ) {}

    /**
     * Create a transfer between two accounts.
     */
    public function create(User $user, array $data): Transfer
    {
        return DB::transaction(function () use ($user, $data) {
            $fromAccount = Account::lockForUpdate()->findOrFail($data['from_account_id']);
            $toAccount = Account::lockForUpdate()->findOrFail($data['to_account_id']);

            // Verify ownership
            if ($fromAccount->user_id !== $user->id || $toAccount->user_id !== $user->id) {
                throw new \Exception('One or both accounts do not belong to you.');
            }

            // Same account check
            if ($fromAccount->id === $toAccount->id) {
                throw new \Exception('Cannot transfer to the same account.');
            }

            // Sufficient balance check
            if ($fromAccount->balance < $data['amount']) {
                throw new \Exception("Insufficient balance in {$fromAccount->name}. Available: ₹" . number_format($fromAccount->balance, 2));
            }

            $data['user_id'] = $user->id;
            $transfer = Transfer::create($data);

            // Adjust balances
            $fromAccount->decrement('balance', $transfer->amount);
            $toAccount->increment('balance', $transfer->amount);

            $this->activityLogService->log('created', $transfer, null, $transfer->toArray());

            return $transfer;
        });
    }

    /**
     * Delete a transfer and reverse balance changes.
     */
    public function delete(Transfer $transfer): void
    {
        DB::transaction(function () use ($transfer) {
            $oldValues = $transfer->toArray();

            // Reverse the transfer
            $transfer->fromAccount()->increment('balance', $transfer->amount);
            $transfer->toAccount()->decrement('balance', $transfer->amount);

            $this->activityLogService->log('deleted', $transfer, $oldValues, null);

            $transfer->delete();
        });
    }
}
