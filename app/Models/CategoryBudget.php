<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CategoryBudget extends Model
{
    use HasFactory;

    protected $fillable = [
        'budget_id',
        'category_id',
        'amount',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Get actual spent amount for this category in the budget's month/year.
     */
    public function getSpentAttribute(): float
    {
        return (float) $this->budget->user->expenses()
            ->where('category_id', $this->category_id)
            ->whereMonth('expense_date', $this->budget->month)
            ->whereYear('expense_date', $this->budget->year)
            ->sum('amount');
    }

    /**
     * Remaining = allocated amount - spent.
     */
    public function getRemainingAttribute(): float
    {
        return (float) ($this->amount - $this->spent);
    }

    /**
     * Usage percentage for this category budget.
     */
    public function getUsagePercentAttribute(): float
    {
        if ($this->amount <= 0) {
            return 0;
        }
        return round(($this->spent / $this->amount) * 100, 1);
    }

    /**
     * Check if this category budget is exceeded.
     */
    public function getIsExceededAttribute(): bool
    {
        return $this->spent > $this->amount;
    }
}
