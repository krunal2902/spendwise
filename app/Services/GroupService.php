<?php

namespace App\Services;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GroupService
{
    public function __construct(
        private ActivityLogService $activityLogService,
    ) {}

    /**
     * Create a new group and add the owner as admin member.
     */
    public function create(User $user, array $data): Group
    {
        return DB::transaction(function () use ($user, $data) {
            $data['invite_code'] = Group::generateInviteCode();

            $group = $user->ownedGroups()->create($data);

            // Add the owner as admin member automatically
            $group->memberships()->create([
                'user_id'   => $user->id,
                'role'      => 'admin',
                'status'    => 'active',
                'joined_at' => now(),
            ]);

            $this->activityLogService->log('created', $group, null, $group->toArray());

            return $group;
        });
    }

    /**
     * Update group details.
     */
    public function update(Group $group, array $data): Group
    {
        $oldValues = $group->toArray();

        $group->update($data);
        $group->refresh();

        $this->activityLogService->log('updated', $group, $oldValues, $group->toArray());

        return $group;
    }

    /**
     * Delete a group (only if no expenses exist).
     */
    public function delete(Group $group): void
    {
        DB::transaction(function () use ($group) {
            $oldValues = $group->toArray();

            $this->activityLogService->log('deleted', $group, $oldValues, null);

            // Delete memberships first (cascade should handle, but be explicit)
            $group->memberships()->delete();
            $group->delete();
        });
    }

    /**
     * Regenerate the invite code for a group.
     */
    public function regenerateInviteCode(Group $group): string
    {
        $newCode = Group::generateInviteCode();
        $group->update(['invite_code' => $newCode]);

        return $newCode;
    }

    /**
     * Add a member to the group.
     */
    public function addMember(Group $group, User $user, string $role = 'member'): GroupMember
    {
        // Check if user already has a membership record (could be removed)
        $existing = $group->memberships()->where('user_id', $user->id)->first();

        if ($existing) {
            if ($existing->status === 'active') {
                throw new \Exception('User is already an active member of this group.');
            }

            // Re-activate removed member
            $existing->update([
                'status'    => 'active',
                'role'      => $role,
                'joined_at' => now(),
            ]);

            return $existing->fresh();
        }

        return $group->memberships()->create([
            'user_id'   => $user->id,
            'role'      => $role,
            'status'    => 'active',
            'joined_at' => now(),
        ]);
    }

    /**
     * Remove a member from the group (soft – set status to removed).
     */
    public function removeMember(Group $group, User $user): void
    {
        if ($group->user_id === $user->id) {
            throw new \Exception('Cannot remove the group owner. Transfer ownership first or delete the group.');
        }

        $membership = $group->memberships()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->firstOrFail();

        $membership->update(['status' => 'removed']);
    }

    /**
     * Change a member's role.
     */
    public function changeRole(Group $group, User $user, string $newRole): void
    {
        if ($group->user_id === $user->id) {
            throw new \Exception('Cannot change the role of the group owner.');
        }

        $membership = $group->memberships()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->firstOrFail();

        $membership->update(['role' => $newRole]);
    }

    /**
     * Join a group via invite code.
     */
    public function joinViaCode(string $inviteCode, User $user): Group
    {
        $group = Group::where('invite_code', $inviteCode)
            ->where('is_active', true)
            ->firstOrFail();

        $this->addMember($group, $user);

        return $group;
    }

    /**
     * Get all groups for a user (owned + member of).
     */
    public function getForUser(User $user)
    {
        return Group::forUser($user->id)
            ->active()
            ->withCount(['memberships as active_members_count' => function ($q) {
                $q->where('status', 'active');
            }])
            ->latest()
            ->get();
    }
}
