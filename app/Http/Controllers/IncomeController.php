<?php

namespace App\Http\Controllers;

use App\Http\Requests\Income\StoreIncomeRequest;
use App\Http\Requests\Income\UpdateIncomeRequest;
use App\Models\Income;
use App\Services\CategoryService;
use App\Services\IncomeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class IncomeController extends Controller
{
    public function __construct(
        private IncomeService $incomeService,
        private CategoryService $categoryService,
    ) {}

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = $request->user()->incomes()
                ->with(['account', 'category'])
                ->select('incomes.*');

            return DataTables::eloquent($query)
                ->addColumn('category_name', fn($row) => $row->category->name ?? 'N/A')
                ->addColumn('account_name', fn($row) => $row->account->name ?? 'N/A')
                ->addColumn('formatted_amount', fn($row) => '₹' . number_format($row->amount, 2))
                ->addColumn('formatted_date', fn($row) => $row->income_date->format('M d, Y'))
                ->addColumn('action', function ($row) {
                    $editUrl = route('incomes.edit', $row->id);
                    $deleteUrl = route('incomes.destroy', $row->id);
                    return '<div class="flex items-center gap-2">
                        <a href="'.$editUrl.'" class="text-indigo-600 hover:text-indigo-800 text-sm"><i class="fas fa-edit"></i></a>
                        <form method="POST" action="'.$deleteUrl.'" onsubmit="return confirm(\'Delete this income?\')">
                            '.csrf_field().method_field('DELETE').'
                            <button type="submit" class="text-red-600 hover:text-red-800 text-sm"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>';
                })
                ->rawColumns(['action'])
                ->orderColumn('income_date', 'income_date $1')
                ->make(true);
        }

        return view('incomes.index');
    }

    public function create(Request $request): View
    {
        $accounts = $request->user()->accounts()->active()->get();
        $categories = $this->categoryService->getForUser($request->user(), 'income');

        return view('incomes.create', compact('accounts', 'categories'));
    }

    public function store(StoreIncomeRequest $request): RedirectResponse
    {
        $this->incomeService->create($request->user(), $request->validated());

        return redirect()->route('incomes.index')
            ->with('success', 'Income recorded successfully.');
    }

    public function edit(Request $request, Income $income): View
    {
        if ($income->user_id !== $request->user()->id) {
            abort(403);
        }

        $accounts = $request->user()->accounts()->active()->get();
        $categories = $this->categoryService->getForUser($request->user(), 'income');

        return view('incomes.edit', compact('income', 'accounts', 'categories'));
    }

    public function update(UpdateIncomeRequest $request, Income $income): RedirectResponse
    {
        $this->incomeService->update($income, $request->validated());

        return redirect()->route('incomes.index')
            ->with('success', 'Income updated successfully.');
    }

    public function destroy(Request $request, Income $income): RedirectResponse
    {
        if ($income->user_id !== $request->user()->id) {
            abort(403);
        }

        $this->incomeService->delete($income);

        return redirect()->route('incomes.index')
            ->with('success', 'Income deleted successfully.');
    }
}
