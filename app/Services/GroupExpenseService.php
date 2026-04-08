<?php

namespace App\Services;

use App\Models\Group;
use App\Models\GroupExpense;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GroupExpenseService
{
    public function __construct(
        private ActivityLogService $activityLogService,
    ) {}

    /**
     * Create a group expense with dynamic splits.
     */
    public function create(Group $group, User $creator, array $data): GroupExpense
    {
        return DB::transaction(function () use ($group, $creator, $data) {
            $splitType = $data['split_type'];
            $splits = $data['splits'] ?? [];
            unset($data['splits']);

            $expense = $group->expenses()->create([
                ...$data,
                'paid_by' => $data['paid_by'] ?? $creator->id,
            ]);

            $this->processSplits($expense, $splitType, $splits);

            $this->activityLogService->log('created', $expense, null, [
                'group'  => $group->name,
                'amount' => $expense->amount,
            ]);

            return $expense;
        });
    }

    /**
     * Update a group expense and rewrite its splits.
     */
    public function update(GroupExpense $expense, array $data): GroupExpense
    {
        return DB::transaction(function () use ($expense, $data) {
            $oldValues = $expense->toArray();

            $splitType = $data['split_type'] ?? $expense->split_type;
            $splits = $data['splits'] ?? [];
            unset($data['splits']);

            $expense->update($data);

            if (!empty($splits)) {
                $expense->splits()->delete();
                $this->processSplits($expense, $splitType, $splits);
            }

            $this->activityLogService->log('updated', $expense, $oldValues, $expense->toArray());

            return $expense->fresh();
        });
    }

    /**
     * Delete a group expense and its splits.
     */
    public function delete(GroupExpense $expense): void
    {
        DB::transaction(function () use ($expense) {
            $oldValues = $expense->toArray();

            $this->activityLogService->log('deleted', $expense, $oldValues, null);

            $expense->splits()->delete();
            $expense->delete();
        });
    }

    /**
     * Route the split creation logic.
     */
    private function processSplits(GroupExpense $expense, string $splitType, array $splits): void
    {
        switch ($splitType) {
            case 'equal':
                $this->calculateEqualSplit($expense, $splits);
                break;
            case 'exact':
                $this->calculateExactSplit($expense, $splits);
                break;
            case 'percentage':
                $this->calculatePercentageSplit($expense, $splits);
                break;
            default:
                throw new \InvalidArgumentException("Invalid split type: {$splitType}");
        }
    }

    /**
     * Calculate equal splits among participating users.
     * $splits should be an array of user_ids.
     */
    private function calculateEqualSplit(GroupExpense $expense, array $userIds): void
    {
        if (empty($userIds)) {
            throw new \Exception('Please select at least one member to split equally.');
        }

        $count = count($userIds);
        $totalAmount = (float) $expense->amount;

        // E.g., 100 / 3 = 33.3333 -> round 2 = 33.33
        $splitAmount = round($totalAmount / $count, 2);

        // Adjust for rounding differences (e.g. 100 - (3 * 33.33)  = 0.01)
        $difference = round($totalAmount - ($splitAmount * $count), 2);

        foreach ($userIds as $index => $userId) {
            $amount = $splitAmount;
            
            // Give formatting difference to the first person
            if ($index === 0) {
                $amount += $difference;
            }

            // Automatically mark settled if they paid for it
            $isSettled = ($userId == $expense->paid_by);

            $expense->splits()->create([
                'user_id'    => $userId,
                'amount'     => $amount,
                'is_settled' => $isSettled,
                'settled_at' => $isSettled ? now() : null,
            ]);
        }
    }

    /**
     * Parse exact splits directly.
     * $splits should be array: [['user_id' => X, 'amount' => Y], ...]
     */
    private function calculateExactSplit(GroupExpense $expense, array $splits): void
    {
        $sum = collect($splits)->sum('amount');
        if (round((float) $sum, 2) !== round((float) $expense->amount, 2)) {
            throw new \Exception('The requested exact splits sum (‘.$sum.’) does not match total expense amount (‘.$expense->amount.’).');
        }

        foreach ($splits as $split) {
            if ((float) $split['amount'] <= 0) continue;

            $isSettled = ($split['user_id'] == $expense->paid_by);

            $expense->splits()->create([
                'user_id'    => $split['user_id'],
                'amount'     => $split['amount'],
                'is_settled' => $isSettled,
                'settled_at' => $isSettled ? now() : null,
            ]);
        }
    }

    /**
     * Calculate splits by parsed percentage.
     * $splits should be array: [['user_id' => X, 'percentage' => Y], ...]
     */
    private function calculatePercentageSplit(GroupExpense $expense, array $splits): void
    {
        $sum = collect($splits)->sum('percentage');
        if (round((float) $sum, 2) !== 100.00) {
            throw new \Exception('Percentages must add up to exactly 100%.');
        }

        $totalAmount = (float) $expense->amount;
        $calculatedSum = 0;
        $splitData = [];

        foreach ($splits as $split) {
            $percentage = (float) $split['percentage'];
            if ($percentage <= 0) continue;

            $amount = round(($percentage / 100) * $totalAmount, 2);
            $calculatedSum += $amount;

            $isSettled = ($split['user_id'] == $expense->paid_by);

            $splitData[] = [
                'user_id'    => $split['user_id'],
                'amount'     => $amount,
                'percentage' => $percentage,
                'is_settled' => $isSettled,
                'settled_at' => $isSettled ? now() : null,
            ];
        }

        // Adjust rounding leftover
        $difference = round($totalAmount - $calculatedSum, 2);
        if ($difference != 0 && !empty($splitData)) {
            $splitData[0]['amount'] += $difference;
        }

        foreach ($splitData as $item) {
            $expense->splits()->create($item);
        }
    }
}
