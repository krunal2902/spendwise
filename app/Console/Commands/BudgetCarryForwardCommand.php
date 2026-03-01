<?php

namespace App\Console\Commands;

use App\Models\Budget;
use App\Services\BudgetService;
use Illuminate\Console\Command;

class BudgetCarryForwardCommand extends Command
{
    protected $signature = 'budgets:carry-forward
                            {--month= : Source month (defaults to previous month)}
                            {--year= : Source year (defaults to previous month\'s year)}';

    protected $description = 'Carry forward unspent budget amounts from the previous month to the current month.';

    public function __construct(private BudgetService $budgetService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        // Determine source month/year (default: previous month)
        $sourceMonth = (int) ($this->option('month') ?: now()->subMonth()->month);
        $sourceYear  = (int) ($this->option('year') ?: now()->subMonth()->year);

        // Target = next month
        $targetMonth = $sourceMonth === 12 ? 1 : $sourceMonth + 1;
        $targetYear  = $sourceMonth === 12 ? $sourceYear + 1 : $sourceYear;

        $this->info("Carrying forward from {$sourceMonth}/{$sourceYear} to {$targetMonth}/{$targetYear}...");

        // Find all budgets with carry_forward enabled for the source month
        $budgets = Budget::where('carry_forward', true)
            ->where(function ($q) use ($sourceMonth, $sourceYear) {
                $q->where(function ($q2) use ($sourceMonth, $sourceYear) {
                    // Monthly budgets
                    $q2->where('type', 'monthly')
                       ->where('month', $sourceMonth)
                       ->where('year', $sourceYear);
                })->orWhere(function ($q2) use ($sourceMonth, $sourceYear) {
                    // Custom budgets that end in the source month
                    $q2->where('type', 'custom')
                       ->whereMonth('end_date', $sourceMonth)
                       ->whereYear('end_date', $sourceYear);
                });
            })
            ->get();

        if ($budgets->isEmpty()) {
            $this->info('No budgets with carry-forward enabled found.');
            return 0;
        }

        $count = 0;
        foreach ($budgets as $budget) {
            $remaining = $budget->remaining;

            if ($remaining <= 0) {
                $this->line("  ⏭ {$budget->name}: No remaining amount to carry forward.");
                continue;
            }

            // Check if a target budget already exists
            $targetBudget = Budget::where('user_id', $budget->user_id)
                ->where('name', $budget->name)
                ->where('month', $targetMonth)
                ->where('year', $targetYear)
                ->first();

            if ($targetBudget) {
                // Add carried amount to existing budget
                $targetBudget->increment('amount', $remaining);
                $targetBudget->update(['carried_amount' => $remaining]);
                $this->line("  ✅ {$budget->name}: ₹" . number_format($remaining, 2) . " added to existing {$targetMonth}/{$targetYear} budget.");
            } else {
                // Create new budget for target month
                Budget::create([
                    'user_id'         => $budget->user_id,
                    'name'            => $budget->name,
                    'amount'          => $budget->amount + $remaining,
                    'month'           => $targetMonth,
                    'year'            => $targetYear,
                    'type'            => 'monthly',
                    'carry_forward'   => $budget->carry_forward,
                    'carried_amount'  => $remaining,
                    'notes'           => "Carried forward ₹" . number_format($remaining, 2) . " from " . date('M', mktime(0, 0, 0, $sourceMonth, 1)) . " {$sourceYear}",
                ]);
                $this->line("  ✅ {$budget->name}: New budget created for {$targetMonth}/{$targetYear} with ₹" . number_format($remaining, 2) . " carried forward.");
            }

            $count++;
        }

        $this->info("Done! {$count} budget(s) carried forward.");
        return 0;
    }
}
