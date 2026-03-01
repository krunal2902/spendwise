<?php

namespace App\Services;

use App\Models\RecurringExpense;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class RecurringExpenseService
{
    public function __construct(
        private ExpenseService $expenseService,
        private ActivityLogService $activityLogService,
    ) {}

    /**
     * Get all recurring expenses for a user.
     */
    public function getForUser(User $user): Collection
    {
        return $user->recurringExpenses()
            ->with(['account', 'category'])
            ->orderBy('next_due_date')
            ->get();
    }

    /**
     * Create a new recurring expense.
     */
    public function create(User $user, array $data): RecurringExpense
    {
        $recurring = $user->recurringExpenses()->create($data);

        $this->activityLogService->log('created', $recurring, null, $recurring->toArray());

        return $recurring;
    }

    /**
     * Update an existing recurring expense.
     */
    public function update(RecurringExpense $recurring, array $data): RecurringExpense
    {
        $oldValues = $recurring->toArray();

        $recurring->update($data);
        $recurring->refresh();

        $this->activityLogService->log('updated', $recurring, $oldValues, $recurring->toArray());

        return $recurring;
    }

    /**
     * Delete a recurring expense.
     */
    public function delete(RecurringExpense $recurring): void
    {
        $oldValues = $recurring->toArray();

        $this->activityLogService->log('deleted', $recurring, $oldValues, null);

        $recurring->delete();
    }

    /**
     * Toggle active status.
     */
    public function toggle(RecurringExpense $recurring): RecurringExpense
    {
        $recurring->update(['is_active' => !$recurring->is_active]);
        return $recurring->refresh();
    }

    /**
     * Process all due recurring expenses.
     * Creates actual expenses and advances the next_due_date.
     */
    public function processDue(): array
    {
        $dueItems = RecurringExpense::active()->due()
            ->with(['user', 'account', 'category'])
            ->get();

        $processed = 0;
        $skipped = 0;
        $errors = [];

        foreach ($dueItems as $recurring) {
            try {
                DB::transaction(function () use ($recurring) {
                    // Create the actual expense
                    $this->expenseService->create($recurring->user, [
                        'account_id'   => $recurring->account_id,
                        'category_id'  => $recurring->category_id,
                        'amount'       => $recurring->amount,
                        'description'  => $recurring->description ?? '[Auto] ' . $recurring->category->name,
                        'expense_date' => $recurring->next_due_date->toDateString(),
                        'reference'    => 'recurring-' . $recurring->id,
                        'notes'        => 'Auto-created from recurring expense: ' . ($recurring->description ?? $recurring->category->name),
                    ]);

                    // Advance the next due date
                    $recurring->update([
                        'next_due_date'     => $recurring->calculateNextDueDate(),
                        'last_processed_at' => now(),
                    ]);
                });

                $processed++;
            } catch (\Exception $e) {
                $skipped++;
                $errors[] = "#{$recurring->id} ({$recurring->description}): {$e->getMessage()}";
            }
        }

        return [
            'processed' => $processed,
            'skipped'   => $skipped,
            'errors'    => $errors,
            'total'     => $dueItems->count(),
        ];
    }
}
