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
        'type',
        'start_date',
        'end_date',
        'carry_forward',
        'carried_amount',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount'         => 'decimal:2',
            'carried_amount' => 'decimal:2',
            'month'          => 'integer',
            'year'           => 'integer',
            'start_date'     => 'date',
            'end_date'       => 'date',
            'carry_forward'  => 'boolean',
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
        return $query->where(function ($q) use ($month, $year) {
            // Monthly budgets matching month/year
            $q->where(function ($q2) use ($month, $year) {
                $q2->where('type', 'monthly')
                   ->where('month', $month)
                   ->where('year', $year);
            })
            // Custom budgets active during this month
            ->orWhere(function ($q2) use ($month, $year) {
                $startOfMonth = sprintf('%04d-%02d-01', $year, $month);
                $endOfMonth = date('Y-m-t', strtotime($startOfMonth));
                $q2->where('type', 'custom')
                   ->where('start_date', '<=', $endOfMonth)
                   ->where('end_date', '>=', $startOfMonth);
            });
        });
    }

    /**
     * Filter budgets by year only.
     */
    public function scopeForYear($query, int $year)
    {
        return $query->where(function ($q) use ($year) {
            $q->where(function ($q2) use ($year) {
                $q2->where('type', 'monthly')->where('year', $year);
            })->orWhere(function ($q2) use ($year) {
                $q2->where('type', 'custom')
                   ->whereYear('start_date', '<=', $year)
                   ->whereYear('end_date', '>=', $year);
            });
        });
    }

    /**
     * Filter only custom (date-range) budgets.
     */
    public function scopeCustomType($query)
    {
        return $query->where('type', 'custom');
    }

    /**
     * Filter currently active custom budgets.
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->where('type', 'monthly')
              ->orWhere(function ($q2) {
                  $q2->where('type', 'custom')
                     ->where('start_date', '<=', now())
                     ->where('end_date', '>=', now());
              });
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Get the display label for this budget's period.
     */
    public function getPeriodLabelAttribute(): string
    {
        if ($this->type === 'custom' && $this->start_date && $this->end_date) {
            return $this->start_date->format('M d, Y') . ' – ' . $this->end_date->format('M d, Y');
        }
        return date('M', mktime(0, 0, 0, $this->month, 1)) . ' ' . $this->year;
    }

    /**
     * Check if this is a custom date-range budget.
     */
    public function getIsCustomAttribute(): bool
    {
        return $this->type === 'custom';
    }

    /**
     * Calculate total expenses for this budget's period.
     */
    public function getSpentAttribute(): float
    {
        $query = $this->user->expenses();

        if ($this->type === 'custom' && $this->start_date && $this->end_date) {
            $query->whereBetween('expense_date', [$this->start_date, $this->end_date]);
        } else {
            $query->whereMonth('expense_date', $this->month)
                  ->whereYear('expense_date', $this->year);
        }

        return (float) $query->sum('amount');
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
