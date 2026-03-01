<?php

namespace App\Http\Controllers;

use App\Http\Requests\Budget\StoreBudgetRequest;
use App\Http\Requests\Budget\StoreCategoryBudgetRequest;
use App\Http\Requests\Budget\UpdateBudgetRequest;
use App\Models\Budget;
use App\Services\BudgetService;
use App\Services\CategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BudgetController extends Controller
{
    public function __construct(
        private BudgetService $budgetService,
        private CategoryService $categoryService,
    ) {}

    /**
     * List budgets – default to current month/year, filterable.
     */
    public function index(Request $request): View
    {
        $month = (int) $request->get('month', now()->month);
        $year  = (int) $request->get('year', now()->year);

        $summary = $this->budgetService->getMonthlySummary($request->user(), $month, $year);

        return view('budgets.index', [
            'budgets'      => $summary['budgets'],
            'summary'      => $summary,
            'currentMonth' => $month,
            'currentYear'  => $year,
        ]);
    }

    /**
     * Show create form.
     */
    public function create(): View
    {
        return view('budgets.create', [
            'currentMonth' => now()->month,
            'currentYear'  => now()->year,
        ]);
    }

    /**
     * Store new budget.
     */
    public function store(StoreBudgetRequest $request): RedirectResponse
    {
        $this->budgetService->create($request->user(), $request->validated());

        return redirect()->route('budgets.index', [
            'month' => $request->month,
            'year'  => $request->year,
        ])->with('success', 'Budget created successfully.');
    }

    /**
     * Show a single budget with spending breakdown.
     */
    public function show(Request $request, Budget $budget): View
    {
        if ($budget->user_id !== $request->user()->id) {
            abort(403);
        }

        $budget->load('categoryBudgets.category');

        // Get expenses for this budget's month/year
        $expenses = $request->user()->expenses()
            ->with(['category', 'account'])
            ->whereMonth('expense_date', $budget->month)
            ->whereYear('expense_date', $budget->year)
            ->orderByDesc('expense_date')
            ->get();

        // Category-wise breakdown (actual spending)
        $categoryBreakdown = $request->user()->expenses()
            ->whereMonth('expense_date', $budget->month)
            ->whereYear('expense_date', $budget->year)
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->with('category')
            ->get();

        // All expense categories for the category budget form
        $categories = $this->categoryService->getForUser($request->user(), 'expense');

        return view('budgets.show', compact('budget', 'expenses', 'categoryBreakdown', 'categories'));
    }

    /**
     * Save category-wise budget allocations.
     */
    public function storeCategoryBudgets(StoreCategoryBudgetRequest $request, Budget $budget): RedirectResponse
    {
        $this->budgetService->syncCategoryBudgets($budget, $request->validated()['categories']);

        return redirect()->route('budgets.show', $budget)
            ->with('success', 'Category budgets updated successfully.');
    }

    /**
     * Show edit form.
     */
    public function edit(Request $request, Budget $budget): View
    {
        if ($budget->user_id !== $request->user()->id) {
            abort(403);
        }

        return view('budgets.edit', compact('budget'));
    }

    /**
     * Update budget.
     */
    public function update(UpdateBudgetRequest $request, Budget $budget): RedirectResponse
    {
        $this->budgetService->update($budget, $request->validated());

        return redirect()->route('budgets.index', [
            'month' => $budget->month,
            'year'  => $budget->year,
        ])->with('success', 'Budget updated successfully.');
    }

    /**
     * Delete budget.
     */
    public function destroy(Request $request, Budget $budget): RedirectResponse
    {
        if ($budget->user_id !== $request->user()->id) {
            abort(403);
        }

        $month = $budget->month;
        $year  = $budget->year;

        $this->budgetService->delete($budget);

        return redirect()->route('budgets.index', [
            'month' => $month,
            'year'  => $year,
        ])->with('success', 'Budget deleted successfully.');
    }
}
