<?php

namespace App\Console\Commands;

use App\Services\RecurringExpenseService;
use Illuminate\Console\Command;

class ProcessRecurringExpensesCommand extends Command
{
    protected $signature = 'expenses:process-recurring';

    protected $description = 'Process all due recurring expenses and create actual expense entries.';

    public function __construct(private RecurringExpenseService $recurringExpenseService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Processing recurring expenses...');

        $result = $this->recurringExpenseService->processDue();

        $this->info("Total due: {$result['total']}");
        $this->info("Processed: {$result['processed']}");

        if ($result['skipped'] > 0) {
            $this->warn("Skipped: {$result['skipped']}");
            foreach ($result['errors'] as $error) {
                $this->error("  ✗ {$error}");
            }
        }

        $this->info('Done!');
        return 0;
    }
}
