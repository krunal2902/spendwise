<?php

namespace App\Http\Controllers;

use App\Http\Requests\Group\StoreGroupSettlementRequest;
use App\Models\Group;
use App\Models\GroupSettlement;
use App\Services\GroupBalanceService;
use App\Services\GroupSettlementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GroupSettlementController extends Controller
{
    public function __construct(
        private GroupBalanceService $balanceService,
        private GroupSettlementService $settlementService,
    ) {}

    public function index(Request $request, Group $group): View
    {
        if (!$group->hasMember($request->user()) && $group->user_id !== $request->user()->id) {
            abort(403);
        }

        // 1. Get Net Balances
        $netBalances = $this->balanceService->getNetBalances($group);
        
        // 2. Get Simplified Settlements
        $suggestedSettlements = $this->balanceService->calculateSimplifiedSettlements($group);
        
        // Map user IDs in results to user objects for the view
        $group->load(['memberships' => function($q) {
            $q->where('status', 'active')->with('user');
        }]);
        $members = $group->memberships->pluck('user')->keyBy('id');

        $activeSuggested = array_map(function($settlement) use ($members) {
            return [
                'from_user' => $members[$settlement['from']] ?? null,
                'to_user'   => $members[$settlement['to']] ?? null,
                'amount'    => $settlement['amount'],
            ];
        }, $suggestedSettlements);

        // 3. Recent Settlements History
        $recentSettlements = $group->settlements()
            ->with(['paidBy', 'paidTo'])
            ->latest('settled_at')
            ->limit(10)
            ->get();

        return view('groups.balances.index', compact(
            'group', 'netBalances', 'members', 'activeSuggested', 'recentSettlements'
        ));
    }

    public function store(StoreGroupSettlementRequest $request, Group $group): RedirectResponse
    {
        if (!$group->hasMember($request->user()) && $group->user_id !== $request->user()->id) {
            abort(403);
        }

        try {
            $this->settlementService->create($group, $request->validated());
            return redirect()->route('groups.balances.index', $group)
                ->with('success', 'Settlement recorded successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy(Request $request, Group $group, GroupSettlement $settlement): RedirectResponse
    {
        if ($settlement->group_id !== $group->id) {
            abort(404);
        }

        // Only admins, or the people involved in the settlement can delete it
        if (!$group->isAdmin($request->user()) 
            && $settlement->paid_by !== $request->user()->id 
            && $settlement->paid_to !== $request->user()->id) {
            abort(403, 'You do not have permission to delete this settlement.');
        }

        try {
            $this->settlementService->delete($settlement);
            return redirect()->route('groups.balances.index', $group)
                ->with('success', 'Settlement deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
