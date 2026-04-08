<?php

namespace App\Services;

use App\Models\Group;
use App\Models\GroupInvitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GroupInvitationService
{
    public function __construct(
        private GroupService $groupService,
        private ActivityLogService $activityLogService,
    ) {}

    /**
     * Send an invitation to join a group.
     */
    public function sendInvite(Group $group, User $invitedBy, string $email): GroupInvitation
    {
        $email = strtolower(trim($email));

        // Check if user is already a member
        $existingUser = User::where('email', $email)->first();
        if ($existingUser && $group->hasMember($existingUser)) {
            throw new \Exception('This user is already a member of the group.');
        }

        // Check if there's already a pending invitation for this email
        $existingInvite = $group->invitations()
            ->forEmail($email)
            ->valid()
            ->first();

        if ($existingInvite) {
            throw new \Exception('A pending invitation already exists for this email.');
        }

        $invitation = $group->invitations()->create([
            'invited_by' => $invitedBy->id,
            'email'      => $email,
            'token'      => GroupInvitation::generateToken(),
            'status'     => 'pending',
            'expires_at' => now()->addDays(7),
        ]);

        $this->activityLogService->log('created', $invitation, null, [
            'group' => $group->name,
            'email' => $email,
        ]);

        return $invitation;
    }

    /**
     * Accept an invitation.
     */
    public function acceptInvite(GroupInvitation $invitation, User $user): void
    {
        if (!$invitation->isValid()) {
            throw new \Exception('This invitation is no longer valid or has expired.');
        }

        // Verify the accepting user's email matches the invitation
        if (strtolower($user->email) !== strtolower($invitation->email)) {
            throw new \Exception('This invitation was sent to a different email address.');
        }

        DB::transaction(function () use ($invitation, $user) {
            $invitation->update([
                'status'       => 'accepted',
                'responded_at' => now(),
            ]);

            // Add user as member via GroupService
            $this->groupService->addMember($invitation->group, $user);

            $this->activityLogService->log('updated', $invitation, ['status' => 'pending'], [
                'status' => 'accepted',
                'user'   => $user->name,
            ]);
        });
    }

    /**
     * Decline an invitation.
     */
    public function declineInvite(GroupInvitation $invitation, User $user): void
    {
        if (!$invitation->isValid()) {
            throw new \Exception('This invitation is no longer valid.');
        }

        if (strtolower($user->email) !== strtolower($invitation->email)) {
            throw new \Exception('This invitation was sent to a different email address.');
        }

        $invitation->update([
            'status'       => 'declined',
            'responded_at' => now(),
        ]);
    }

    /**
     * Revoke a pending invitation (by group admin).
     */
    public function revokeInvite(GroupInvitation $invitation): void
    {
        if ($invitation->status !== 'pending') {
            throw new \Exception('Only pending invitations can be revoked.');
        }

        $invitation->update([
            'status'       => 'revoked',
            'responded_at' => now(),
        ]);
    }

    /**
     * Get pending invitations for a group.
     */
    public function getPendingForGroup(Group $group)
    {
        return $group->invitations()
            ->valid()
            ->with('invitedBy')
            ->latest()
            ->get();
    }

    /**
     * Get pending invitations for a user (by email).
     */
    public function getPendingForUser(User $user)
    {
        return GroupInvitation::forEmail($user->email)
            ->valid()
            ->with(['group.owner', 'invitedBy'])
            ->latest()
            ->get();
    }
}
