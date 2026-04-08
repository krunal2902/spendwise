<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GroupExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'paid_by',
        'category_id',
        'amount',
        'description',
        'expense_date',
        'split_type',
        'notes',
        'receipt_image',
    ];

    protected function casts(): array
    {
        return [
            'amount'       => 'decimal:2',
            'expense_date' => 'date',
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

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function splits(): HasMany
    {
        return $this->hasMany(GroupExpenseSplit::class);
    }
}
