<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'currency',
        'image',
        'invite_code',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * The user who created/owns the group.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Group membership records.
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(GroupMember::class);
    }

    /**
     * Active members via the pivot.
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_members')
            ->wherePivot('status', 'active')
            ->withPivot(['role', 'status', 'joined_at'])
            ->withTimestamps();
    }

    /**
     * All members including removed (for history).
     */
    public function allMembers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_members')
            ->withPivot(['role', 'status', 'joined_at'])
            ->withTimestamps();
    }

    /**
     * Invitations sent for this group.
     */
    public function invitations(): HasMany
    {
        return $this->hasMany(GroupInvitation::class);
    }

    /**
     * Settlements (payments) recorded within this group.
     */
    public function settlements(): HasMany
    {
        return $this->hasMany(GroupSettlement::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('user_id', $userId)
              ->orWhereHas('memberships', function ($mq) use ($userId) {
                  $mq->where('user_id', $userId)->where('status', 'active');
              });
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Generate a unique invite code.
     */
    public static function generateInviteCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (self::where('invite_code', $code)->exists());

        return $code;
    }

    /**
     * Check if a user is a member of this group (active).
     */
    public function hasMember(User $user): bool
    {
        return $this->memberships()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();
    }

    /**
     * Check if a user is an admin of this group.
     */
    public function isAdmin(User $user): bool
    {
        // Owner is always admin
        if ($this->user_id === $user->id) {
            return true;
        }

        return $this->memberships()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->where('role', 'admin')
            ->exists();
    }

    /**
     * Get the member count (active only).
     */
    public function getActiveMemberCountAttribute(): int
    {
        return $this->memberships()->where('status', 'active')->count();
    }
}
