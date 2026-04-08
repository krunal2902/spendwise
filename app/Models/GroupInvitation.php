<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class GroupInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'invited_by',
        'email',
        'token',
        'status',
        'expires_at',
        'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at'   => 'datetime',
            'responded_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'pending')
            ->where('expires_at', '<', now());
    }

    public function scopeValid($query)
    {
        return $query->where('status', 'pending')
            ->where('expires_at', '>=', now());
    }

    public function scopeForEmail($query, string $email)
    {
        return $query->where('email', strtolower($email));
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Generate a unique invitation token.
     */
    public static function generateToken(): string
    {
        do {
            $token = Str::random(48);
        } while (self::where('token', $token)->exists());

        return $token;
    }

    /**
     * Check if this invitation is still valid (pending & not expired).
     */
    public function isValid(): bool
    {
        return $this->status === 'pending' && $this->expires_at->isFuture();
    }

    /**
     * Check if this invitation has expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
