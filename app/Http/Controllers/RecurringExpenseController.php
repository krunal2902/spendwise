<?php

namespace App\Http\Controllers;

use App\Http\Requests\RecurringExpense\StoreRecurringExpenseRequest;
use App\Http\Requests\RecurringExpense\UpdateRecurringExpenseRequest;
use App\Models\RecurringExpense;
use App\Services\CategoryService;
use App\Services\RecurringExpenseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RecurringExpenseController extends Controller
{
    public function __construct(
        private RecurringExpenseService $recurringExpenseService,
        private CategoryService $categoryService,
    ) {}

    /**
     * List all recurring expenses.
     */
    public function index(Request $request): View
    {
        $recurringExpenses = $this->recurringExpenseService->getForUser($request->user());

        return view('recurring-expenses.index', compact('recurringExpenses'));
    }

    /**
     * Show create form.
     */
    public function create(Request $request): View
    {
        $accounts   = $request->user()->accounts()->active()->get();
        $categories = $this->categoryService->getForUser($request->user(), 'expense');

        return view('recurring-expenses.create', compact('accounts', 'categories'));
    }

    /**
     * Store new recurring expense.
     */
    public function store(StoreRecurringExpenseRequest $request): RedirectResponse
    {
        $this->recurringExpenseService->create($request->user(), $request->validated());

        return redirect()->route('recurring-expenses.index')
            ->with('success', 'Recurring expense created successfully.');
    }

    /**
     * Show edit form.
     */
    public function edit(Request $request, RecurringExpense $recurringExpense): View
    {
        if ($recurringExpense->user_id !== $request->user()->id) {
            abort(403);
        }

        $accounts   = $request->user()->accounts()->active()->get();
        $categories = $this->categoryService->getForUser($request->user(), 'expense');

        return view('recurring-expenses.edit', compact('recurringExpense', 'accounts', 'categories'));
    }

    /**
     * Update recurring expense.
     */
    public function update(UpdateRecurringExpenseRequest $request, RecurringExpense $recurringExpense): RedirectResponse
    {
        $this->recurringExpenseService->update($recurringExpense, $request->validated());

        return redirect()->route('recurring-expenses.index')
            ->with('success', 'Recurring expense updated successfully.');
    }

    /**
     * Delete recurring expense.
     */
    public function destroy(Request $request, RecurringExpense $recurringExpense): RedirectResponse
    {
        if ($recurringExpense->user_id !== $request->user()->id) {
            abort(403);
        }

        $this->recurringExpenseService->delete($recurringExpense);

        return redirect()->route('recurring-expenses.index')
            ->with('success', 'Recurring expense deleted successfully.');
    }

    /**
     * Toggle active/inactive status.
     */
    public function toggle(Request $request, RecurringExpense $recurringExpense): RedirectResponse
    {
        if ($recurringExpense->user_id !== $request->user()->id) {
            abort(403);
        }

        $this->recurringExpenseService->toggle($recurringExpense);
        $status = $recurringExpense->is_active ? 'activated' : 'paused';

        return redirect()->route('recurring-expenses.index')
            ->with('success', "Recurring expense {$status}.");
    }
}
