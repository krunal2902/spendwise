<?php

namespace App\Services;

use App\Models\Group;
use App\Models\GroupSettlement;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GroupSettlementService
{
    public function __construct(
        private ActivityLogService $activityLogService,
    ) {}

    /**
     * Create a settlement (payment between users).
     */
    public function create(Group $group, array $data): GroupSettlement
    {
        return DB::transaction(function () use ($group, $data) {
            $settlement = $group->settlements()->create($data);

            $this->activityLogService->log('created', $settlement, null, [
                'group'  => $group->name,
                'amount' => $settlement->amount,
            ]);

            return $settlement;
        });
    }

    /**
     * Delete a settlement.
     */
    public function delete(GroupSettlement $settlement): void
    {
        DB::transaction(function () use ($settlement) {
            $oldValues = $settlement->toArray();
            
            $this->activityLogService->log('deleted', $settlement, $oldValues, null);
            
            $settlement->delete();
        });
    }
}
