<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Budget extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'amount',
        'month',
        'year',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'month'  => 'integer',
            'year'   => 'integer',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categoryBudgets(): HasMany
    {
        return $this->hasMany(CategoryBudget::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Filter budgets by a specific month and year.
     */
    public function scopeForMonth($query, int $month, int $year)
    {
        return $query->where('month', $month)->where('year', $year);
    }

    /**
     * Filter budgets by year only.
     */
    public function scopeForYear($query, int $year)
    {
        return $query->where('year', $year);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Get the display label for this budget's month/year, e.g. "Mar 2026".
     */
    public function getPeriodLabelAttribute(): string
    {
        return date('M', mktime(0, 0, 0, $this->month, 1)) . ' ' . $this->year;
    }

    /**
     * Calculate total expenses for the user in this budget's month/year.
     */
    public function getSpentAttribute(): float
    {
        return (float) $this->user->expenses()
            ->whereMonth('expense_date', $this->month)
            ->whereYear('expense_date', $this->year)
            ->sum('amount');
    }

    /**
     * Remaining = budget amount - spent.
     */
    public function getRemainingAttribute(): float
    {
        return (float) ($this->amount - $this->spent);
    }

    /**
     * Percentage used (0-100+).
     */
    public function getUsagePercentAttribute(): float
    {
        if ($this->amount <= 0) {
            return 0;
        }
        return round(($this->spent / $this->amount) * 100, 1);
    }
}
