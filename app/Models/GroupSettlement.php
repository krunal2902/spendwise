<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupSettlement extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'paid_by',
        'paid_to',
        'amount',
        'notes',
        'settled_at',
    ];

    protected function casts(): array
    {
        return [
            'amount'     => 'decimal:2',
            'settled_at' => 'datetime',
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

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function paidTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_to');
    }
}
