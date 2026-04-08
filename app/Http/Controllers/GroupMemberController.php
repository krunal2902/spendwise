<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use App\Services\GroupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GroupMemberController extends Controller
{
    public function __construct(
        private GroupService $groupService,
    ) {}

    /**
     * Display members list for a group.
     */
    public function index(Request $request, Group $group): View
    {
        if (!$group->hasMember($request->user()) && $group->user_id !== $request->user()->id) {
            abort(403);
        }

        $group->load(['owner', 'memberships' => function ($q) {
            $q->where('status', 'active')
              ->with('user')
              ->orderByRaw("FIELD(role, 'admin', 'member')")
              ->orderBy('joined_at');
        }]);

        $isAdmin = $group->isAdmin($request->user());

        return view('groups.members.index', compact('group', 'isAdmin'));
    }

    /**
     * Update a member's role.
     */
    public function updateRole(Request $request, Group $group, User $user): RedirectResponse
    {
        if (!$group->isAdmin($request->user())) {
            abort(403);
        }

        $request->validate(['role' => 'required|in:admin,member']);

        try {
            $this->groupService->changeRole($group, $user, $request->role);
            return back()->with('success', $user->name . '\'s role updated to ' . ucfirst($request->role) . '.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Remove a member from the group.
     */
    public function remove(Request $request, Group $group, User $user): RedirectResponse
    {
        if (!$group->isAdmin($request->user())) {
            abort(403);
        }

        try {
            $this->groupService->removeMember($group, $user);
            return back()->with('success', $user->name . ' has been removed from the group.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
