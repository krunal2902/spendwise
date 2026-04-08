<?php

namespace App\Services;

use App\Models\Group;

class GroupBalanceService
{
    /**
     * Calculate pairwise raw balances for a group.
     * Raw balances: literally who paid for whom without optimization.
     * Format: [debtor_id => [creditor_id => amount_owed]]
     */
    public function calculateRawBalances(Group $group): array
    {
        $rawBalances = [];

        // 1. Unsettled Expenses
        $expenses = $group->expenses()->with('splits')->get();

        foreach ($expenses as $expense) {
            $creditorId = $expense->paid_by;

            foreach ($expense->splits as $split) {
                if ($split->is_settled) {
                    continue; // Skip already settled splits
                }

                $debtorId = $split->user_id;

                if ($debtorId === $creditorId) {
                    continue; // User paid for themselves
                }

                if (!isset($rawBalances[$debtorId])) {
                    $rawBalances[$debtorId] = [];
                }
                if (!isset($rawBalances[$debtorId][$creditorId])) {
                    $rawBalances[$debtorId][$creditorId] = 0;
                }

                $rawBalances[$debtorId][$creditorId] += (float) $split->amount;
            }
        }

        return $rawBalances;
    }

    /**
     * Calculate net balances for every user in the group.
     * (+ve = they are owed money, -ve = they owe money)
     * Format: [user_id => net_balance]
     */
    public function getNetBalances(Group $group): array
    {
        $rawBalances = $this->calculateRawBalances($group);
        $netBalances = [];

        // Initializing with 0s for all members
        foreach ($group->memberships()->where('status', 'active')->get() as $member) {
            $netBalances[$member->user_id] = 0.0;
        }

        foreach ($rawBalances as $debtorId => $creditors) {
            foreach ($creditors as $creditorId => $amount) {
                if (!isset($netBalances[$debtorId])) $netBalances[$debtorId] = 0.0;
                if (!isset($netBalances[$creditorId])) $netBalances[$creditorId] = 0.0;

                $netBalances[$debtorId] -= $amount;
                $netBalances[$creditorId] += $amount;
            }
        }

        // Apply settlements to net balances
        $settlements = $group->settlements()->get();
        foreach ($settlements as $settlement) {
            $debtorId = $settlement->paid_by;
            $creditorId = $settlement->paid_to;

            if (isset($netBalances[$debtorId])) $netBalances[$debtorId] += (float) $settlement->amount;
            if (isset($netBalances[$creditorId])) $netBalances[$creditorId] -= (float) $settlement->amount;
        }

        // Clean up formatting
        foreach ($netBalances as $userId => $balance) {
            $netBalances[$userId] = round($balance, 2);
        }

        return $netBalances;
    }

    /**
     * Simplify Debts Algorithm (Minimize Transactions).
     * Returns an array of suggested settlements:
     * [['from' => user_id, 'to' => user_id, 'amount' => amount]]
     */
    public function calculateSimplifiedSettlements(Group $group): array
    {
        $netBalances = $this->getNetBalances($group);

        $debtors = [];
        $creditors = [];

        foreach ($netBalances as $userId => $balance) {
            if ($balance < -0.01) {
                $debtors[] = ['id' => $userId, 'balance' => abs($balance)];
            } elseif ($balance > 0.01) {
                $creditors[] = ['id' => $userId, 'balance' => $balance];
            }
        }

        // Sort both descending by amount
        usort($debtors, fn($a, $b) => $b['balance'] <=> $a['balance']);
        usort($creditors, fn($a, $b) => $b['balance'] <=> $a['balance']);

        $settlements = [];
        $i = 0; // Debtors index
        $j = 0; // Creditors index

        while ($i < count($debtors) && $j < count($creditors)) {
            $debtAmount = $debtors[$i]['balance'];
            $creditAmount = $creditors[$j]['balance'];

            $settledAmount = min($debtAmount, $creditAmount);

            $settlements[] = [
                'from'   => $debtors[$i]['id'],
                'to'     => $creditors[$j]['id'],
                'amount' => round($settledAmount, 2),
            ];

            $debtors[$i]['balance'] -= $settledAmount;
            $creditors[$j]['balance'] -= $settledAmount;

            if ($debtors[$i]['balance'] < 0.01) {
                $i++;
            }
            if ($creditors[$j]['balance'] < 0.01) {
                $j++;
            }
        }

        return $settlements;
    }
}
