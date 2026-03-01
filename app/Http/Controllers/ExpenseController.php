<?php

namespace App\Http\Controllers;

use App\Http\Requests\Expense\StoreExpenseRequest;
use App\Http\Requests\Expense\UpdateExpenseRequest;
use App\Models\Expense;
use App\Services\CategoryService;
use App\Services\ExpenseService;
use App\Services\TagService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class ExpenseController extends Controller
{
    public function __construct(
        private ExpenseService $expenseService,
        private CategoryService $categoryService,
        private TagService $tagService,
    ) {}

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = $request->user()->expenses()
                ->with(['account', 'category'])
                ->select('expenses.*');

            return DataTables::eloquent($query)
                ->addColumn('category_name', fn($row) => $row->category->name ?? 'N/A')
                ->addColumn('account_name', fn($row) => $row->account->name ?? 'N/A')
                ->addColumn('formatted_amount', fn($row) => '₹' . number_format($row->amount, 2))
                ->addColumn('formatted_date', fn($row) => $row->expense_date->format('M d, Y'))
                ->addColumn('action', function ($row) {
                    $editUrl = route('expenses.edit', $row->id);
                    $deleteUrl = route('expenses.destroy', $row->id);
                    return '<div class="flex items-center gap-2">
                        <a href="'.$editUrl.'" class="text-indigo-600 hover:text-indigo-800 text-sm"><i class="fas fa-edit"></i></a>
                        <form method="POST" action="'.$deleteUrl.'" onsubmit="return confirm(\'Delete this expense? Balance will be restored.\')">
                            '.csrf_field().method_field('DELETE').'
                            <button type="submit" class="text-red-600 hover:text-red-800 text-sm"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>';
                })
                ->rawColumns(['action'])
                ->orderColumn('expense_date', 'expense_date $1')
                ->make(true);
        }

        return view('expenses.index');
    }

    public function create(Request $request): View
    {
        $accounts   = $request->user()->accounts()->active()->get();
        $categories = $this->categoryService->getForUser($request->user(), 'expense');
        $userTags   = $this->tagService->getForUser($request->user());

        return view('expenses.create', compact('accounts', 'categories', 'userTags'));
    }

    public function store(StoreExpenseRequest $request): RedirectResponse
    {
        try {
            $this->expenseService->create($request->user(), $request->validated());

            return redirect()->route('expenses.index')
                ->with('success', 'Expense recorded successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function edit(Request $request, Expense $expense): View
    {
        if ($expense->user_id !== $request->user()->id) {
            abort(403);
        }

        $expense->load(['tags', 'histories.user']);

        $accounts   = $request->user()->accounts()->active()->get();
        $categories = $this->categoryService->getForUser($request->user(), 'expense');
        $userTags   = $this->tagService->getForUser($request->user());

        return view('expenses.edit', compact('expense', 'accounts', 'categories', 'userTags'));
    }

    public function update(UpdateExpenseRequest $request, Expense $expense): RedirectResponse
    {
        try {
            $this->expenseService->update($expense, $request->validated());

            return redirect()->route('expenses.index')
                ->with('success', 'Expense updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function destroy(Request $request, Expense $expense): RedirectResponse
    {
        if ($expense->user_id !== $request->user()->id) {
            abort(403);
        }

        $this->expenseService->delete($expense);

        return redirect()->route('expenses.index')
            ->with('success', 'Expense deleted and balance restored.');
    }
}
