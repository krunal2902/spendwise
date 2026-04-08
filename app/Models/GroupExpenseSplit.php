<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupExpenseSplit extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_expense_id',
        'user_id',
        'amount',
        'percentage',
        'is_settled',
        'settled_at',
    ];

    protected function casts(): array
    {
        return [
            'amount'     => 'decimal:2',
            'percentage' => 'decimal:2',
            'is_settled' => 'boolean',
            'settled_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function expense(): BelongsTo
    {
        return $this->belongsTo(GroupExpense::class, 'group_expense_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeSettled($query)
    {
        return $query->where('is_settled', true);
    }

    public function scopeUnsettled($query)
    {
        return $query->where('is_settled', false);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
