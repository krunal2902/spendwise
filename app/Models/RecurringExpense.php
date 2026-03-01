<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecurringExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'account_id',
        'category_id',
        'amount',
        'description',
        'frequency',
        'next_due_date',
        'last_processed_at',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount'            => 'decimal:2',
            'next_due_date'     => 'date',
            'last_processed_at' => 'datetime',
            'is_active'         => 'boolean',
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

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
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

    public function scopeDue($query)
    {
        return $query->where('next_due_date', '<=', now()->toDateString());
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Get the next due date after processing, based on frequency.
     */
    public function calculateNextDueDate(): string
    {
        return match ($this->frequency) {
            'daily'   => $this->next_due_date->addDay()->toDateString(),
            'weekly'  => $this->next_due_date->addWeek()->toDateString(),
            'monthly' => $this->next_due_date->addMonth()->toDateString(),
            'yearly'  => $this->next_due_date->addYear()->toDateString(),
        };
    }

    /**
     * Frequency label for display.
     */
    public function getFrequencyLabelAttribute(): string
    {
        return match ($this->frequency) {
            'daily'   => 'Daily',
            'weekly'  => 'Weekly',
            'monthly' => 'Monthly',
            'yearly'  => 'Yearly',
            default   => ucfirst($this->frequency),
        };
    }

    /**
     * Check if this recurring expense is currently due.
     */
    public function getIsDueAttribute(): bool
    {
        return $this->is_active && $this->next_due_date <= now()->toDateString();
    }
}
