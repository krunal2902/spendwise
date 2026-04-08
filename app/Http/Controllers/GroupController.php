<?php

namespace App\Http\Controllers;

use App\Http\Requests\Group\StoreGroupRequest;
use App\Http\Requests\Group\UpdateGroupRequest;
use App\Models\Group;
use App\Models\User;
use App\Services\GroupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class GroupController extends Controller
{
    public function __construct(
        private GroupService $groupService,
    ) {}

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Group::forUser($request->user()->id)
                ->active()
                ->with('owner')
                ->withCount(['memberships as active_members_count' => function ($q) {
                    $q->where('status', 'active');
                }])
                ->select('groups.*');

            return DataTables::eloquent($query)
                ->addColumn('owner_name', fn($row) => $row->owner->name)
                ->addColumn('members_count', fn($row) => $row->active_members_count . ' members')
                ->addColumn('role_badge', function ($row) use ($request) {
                    $isOwner = $row->user_id === $request->user()->id;
                    if ($isOwner) {
                        return '<span class="px-2 py-1 text-xs rounded-full bg-amber-100 text-amber-800">Owner</span>';
                    }
                    $membership = $row->memberships()->where('user_id', $request->user()->id)->where('status', 'active')->first();
                    $role = $membership ? $membership->role : 'member';
                    $color = $role === 'admin' ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-600';
                    return '<span class="px-2 py-1 text-xs rounded-full '.$color.'">'.ucfirst($role).'</span>';
                })
                ->addColumn('action', function ($row) {
                    $showUrl = route('groups.show', $row->id);
                    $editUrl = route('groups.edit', $row->id);
                    $deleteUrl = route('groups.destroy', $row->id);
                    $actions = '<div class="flex items-center gap-2">';
                    $actions .= '<a href="'.$showUrl.'" class="text-gray-600 hover:text-gray-800 text-sm"><i class="fas fa-eye"></i></a>';
                    $actions .= '<a href="'.$editUrl.'" class="text-indigo-600 hover:text-indigo-800 text-sm"><i class="fas fa-edit"></i></a>';
                    if ($row->user_id === auth()->id()) {
                        $actions .= '<form method="POST" action="'.$deleteUrl.'" onsubmit="return confirm(\'Delete this group? All data will be lost.\')">'.csrf_field().method_field('DELETE').'<button type="submit" class="text-red-600 hover:text-red-800 text-sm"><i class="fas fa-trash"></i></button></form>';
                    }
                    $actions .= '</div>';
                    return $actions;
                })
                ->rawColumns(['role_badge', 'action'])
                ->make(true);
        }

        return view('groups.index');
    }

    public function create(): View
    {
        return view('groups.create');
    }

    public function store(StoreGroupRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Handle image upload
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('group-images', 'public');
        }

        $group = $this->groupService->create($request->user(), $data);

        return redirect()->route('groups.show', $group)
            ->with('success', 'Group created successfully!');
    }

    public function show(Request $request, Group $group): View
    {
        // Ensure user is a member or owner
        if (!$group->hasMember($request->user()) && $group->user_id !== $request->user()->id) {
            abort(403, 'You are not a member of this group.');
        }

        $group->load(['owner', 'memberships' => function ($q) {
            $q->where('status', 'active')->with('user')->orderBy('role')->orderBy('joined_at');
        }]);

        $isOwner = $group->user_id === $request->user()->id;
        $isAdmin = $group->isAdmin($request->user());

        // Dashboard Stats
        $totalSpent = $group->expenses()->sum('amount');
        
        $memberSpents = $group->expenses()
            ->selectRaw('paid_by, SUM(amount) as total')
            ->groupBy('paid_by')
            ->pluck('total', 'paid_by');

        $recentExpenses = $group->expenses()
            ->with(['paidBy', 'category'])
            ->latest('expense_date')
            ->take(5)
            ->get();

        // ---------------------------------------------------------
        // Generate Activity Feed (Expenses + Settlements + Joins)
        // ---------------------------------------------------------
        $activities = collect([]);

        // Expenses
        foreach ($recentExpenses as $expense) {
            $activities->push([
                'type' => 'expense',
                'icon' => 'fa-receipt text-indigo-500',
                'title' => 'Expense Added: ' . $expense->description,
                'description' => $expense->paidBy->name . ' paid ₹' . number_format($expense->amount, 2),
                'date' => $expense->created_at,
            ]);
        }

        // Settlements
        $recentSettlements = $group->settlements()
            ->with(['paidBy', 'paidTo'])
            ->latest('settled_at')
            ->take(5)
            ->get();
            
        foreach ($recentSettlements as $settlement) {
            $activities->push([
                'type' => 'settlement',
                'icon' => 'fa-handshake text-teal-500',
                'title' => 'Payment Made',
                'description' => $settlement->paidBy->name . ' paid ₹' . number_format($settlement->amount, 2) . ' to ' . $settlement->paidTo->name,
                'date' => $settlement->created_at,
            ]);
        }

        // Members Joined
        $recentMembers = $group->memberships()
            ->where('status', 'active')
            ->with('user')
            ->latest('joined_at')
            ->take(5)
            ->get();
            
        foreach ($recentMembers as $member) {
            // Skip owner creation noise if they just created the group
            if ($member->user_id === $group->user_id && $member->joined_at->diffInSeconds($group->created_at) < 5) {
                continue;
            }
            $activities->push([
                'type' => 'member',
                'icon' => 'fa-user-plus text-green-500',
                'title' => 'New Member',
                'description' => $member->user->name . ' joined the group.',
                'date' => $member->joined_at,
            ]);
        }

        $activityFeed = $activities->sortByDesc('date')->take(8);

        return view('groups.show', compact(
            'group', 'isOwner', 'isAdmin', 'totalSpent', 'memberSpents', 'recentExpenses', 'activityFeed'
        ));
    }

    public function edit(Request $request, Group $group): View
    {
        // Only owner or admin can edit
        if (!$group->isAdmin($request->user())) {
            abort(403, 'Only group admins can edit the group.');
        }

        return view('groups.edit', compact('group'));
    }

    public function update(UpdateGroupRequest $request, Group $group): RedirectResponse
    {
        if (!$group->isAdmin($request->user())) {
            abort(403, 'Only group admins can update the group.');
        }

        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('group-images', 'public');
        }

        $this->groupService->update($group, $data);

        return redirect()->route('groups.show', $group)
            ->with('success', 'Group updated successfully.');
    }

    public function destroy(Request $request, Group $group): RedirectResponse
    {
        // Only the owner can delete the group
        if ($group->user_id !== $request->user()->id) {
            abort(403, 'Only the group owner can delete the group.');
        }

        try {
            $this->groupService->delete($group);
            return redirect()->route('groups.index')
                ->with('success', 'Group deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('groups.show', $group)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Regenerate the invite code.
     */
    public function regenerateCode(Request $request, Group $group): RedirectResponse
    {
        if (!$group->isAdmin($request->user())) {
            abort(403);
        }

        $this->groupService->regenerateInviteCode($group);

        return back()->with('success', 'Invite code regenerated.');
    }

    /**
     * Join a group via invite code.
     */
    public function join(Request $request): RedirectResponse
    {
        $request->validate(['invite_code' => 'required|string|max:20']);

        try {
            $group = $this->groupService->joinViaCode($request->invite_code, $request->user());
            return redirect()->route('groups.show', $group)
                ->with('success', 'Successfully joined the group!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Remove a member from the group.
     */
    public function removeMember(Request $request, Group $group, User $user): RedirectResponse
    {
        if (!$group->isAdmin($request->user())) {
            abort(403);
        }

        try {
            $this->groupService->removeMember($group, $user);
            return back()->with('success', 'Member removed successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Change a member's role.
     */
    public function changeRole(Request $request, Group $group, User $user): RedirectResponse
    {
        if (!$group->isAdmin($request->user())) {
            abort(403);
        }

        $request->validate(['role' => 'required|in:admin,member']);

        try {
            $this->groupService->changeRole($group, $user, $request->role);
            return back()->with('success', 'Member role updated.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Leave a group (self-remove).
     */
    public function leave(Request $request, Group $group): RedirectResponse
    {
        try {
            $this->groupService->removeMember($group, $request->user());
            return redirect()->route('groups.index')
                ->with('success', 'You have left the group.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
