<?php

namespace App\Http\Controllers;

use App\Http\Requests\Group\StoreGroupInvitationRequest;
use App\Models\Group;
use App\Models\GroupInvitation;
use App\Services\GroupInvitationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GroupInvitationController extends Controller
{
    public function __construct(
        private GroupInvitationService $invitationService,
    ) {}

    /**
     * Send an invitation.
     */
    public function store(StoreGroupInvitationRequest $request, Group $group): RedirectResponse
    {
        if (!$group->isAdmin($request->user())) {
            abort(403, 'Only group admins can send invitations.');
        }

        try {
            $this->invitationService->sendInvite($group, $request->user(), $request->email);
            return back()->with('success', 'Invitation sent to ' . $request->email . '.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Show pending invitations for the authenticated user.
     */
    public function myInvitations(Request $request): View
    {
        $invitations = $this->invitationService->getPendingForUser($request->user());

        return view('groups.invitations.index', compact('invitations'));
    }

    /**
     * Accept an invitation via token.
     */
    public function accept(Request $request, string $token): RedirectResponse
    {
        $invitation = GroupInvitation::where('token', $token)->firstOrFail();

        try {
            $this->invitationService->acceptInvite($invitation, $request->user());
            return redirect()->route('groups.show', $invitation->group)
                ->with('success', 'You have joined ' . $invitation->group->name . '!');
        } catch (\Exception $e) {
            return redirect()->route('invitations.mine')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Decline an invitation via token.
     */
    public function decline(Request $request, string $token): RedirectResponse
    {
        $invitation = GroupInvitation::where('token', $token)->firstOrFail();

        try {
            $this->invitationService->declineInvite($invitation, $request->user());
            return redirect()->route('invitations.mine')
                ->with('success', 'Invitation declined.');
        } catch (\Exception $e) {
            return redirect()->route('invitations.mine')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Revoke a pending invitation (admin action).
     */
    public function revoke(Request $request, Group $group, GroupInvitation $invitation): RedirectResponse
    {
        if (!$group->isAdmin($request->user())) {
            abort(403);
        }

        if ($invitation->group_id !== $group->id) {
            abort(404);
        }

        try {
            $this->invitationService->revokeInvite($invitation);
            return back()->with('success', 'Invitation revoked.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
