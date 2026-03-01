<?php

namespace App\Http\Controllers;

use App\Http\Requests\Account\StoreAccountRequest;
use App\Http\Requests\Account\UpdateAccountRequest;
use App\Models\Account;
use App\Services\AccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class AccountController extends Controller
{
    public function __construct(
        private AccountService $accountService,
    ) {}

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = $request->user()->accounts()->select('accounts.*');

            return DataTables::eloquent($query)
                ->addColumn('formatted_balance', fn($row) => '₹' . number_format($row->balance, 2))
                ->addColumn('type_badge', function ($row) {
                    $colors = ['bank' => 'bg-blue-100 text-blue-800', 'cash' => 'bg-green-100 text-green-800', 'digital' => 'bg-purple-100 text-purple-800'];
                    $color = $colors[$row->type] ?? 'bg-gray-100 text-gray-800';
                    return '<span class="px-2 py-1 text-xs rounded-full '.$color.'">'.ucfirst($row->type).'</span>';
                })
                ->addColumn('action', function ($row) {
                    $showUrl = route('accounts.show', $row->id);
                    $editUrl = route('accounts.edit', $row->id);
                    $deleteUrl = route('accounts.destroy', $row->id);
                    return '<div class="flex items-center gap-2">
                        <a href="'.$showUrl.'" class="text-gray-600 hover:text-gray-800 text-sm"><i class="fas fa-eye"></i></a>
                        <a href="'.$editUrl.'" class="text-indigo-600 hover:text-indigo-800 text-sm"><i class="fas fa-edit"></i></a>
                        <form method="POST" action="'.$deleteUrl.'" onsubmit="return confirm(\'Delete this account?\')">
                            '.csrf_field().method_field('DELETE').'
                            <button type="submit" class="text-red-600 hover:text-red-800 text-sm"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>';
                })
                ->rawColumns(['type_badge', 'action'])
                ->make(true);
        }

        $totalBalance = $this->accountService->getTotalBalance($request->user());
        return view('accounts.index', compact('totalBalance'));
    }

    public function create(): View
    {
        return view('accounts.create');
    }

    public function store(StoreAccountRequest $request): RedirectResponse
    {
        $this->accountService->create($request->user(), $request->validated());

        return redirect()->route('accounts.index')
            ->with('success', 'Account created successfully.');
    }

    public function show(Request $request, Account $account): View
    {
        if ($account->user_id !== $request->user()->id) {
            abort(403);
        }

        $account->load(['incomes' => fn($q) => $q->latest('income_date')->limit(10),
                         'expenses' => fn($q) => $q->latest('expense_date')->limit(10)]);

        return view('accounts.show', compact('account'));
    }

    public function edit(Request $request, Account $account): View
    {
        if ($account->user_id !== $request->user()->id) {
            abort(403);
        }

        return view('accounts.edit', compact('account'));
    }

    public function update(UpdateAccountRequest $request, Account $account): RedirectResponse
    {
        $this->accountService->update($account, $request->validated());

        return redirect()->route('accounts.index')
            ->with('success', 'Account updated successfully.');
    }

    public function destroy(Request $request, Account $account): RedirectResponse
    {
        if ($account->user_id !== $request->user()->id) {
            abort(403);
        }

        try {
            $this->accountService->delete($account);
            return redirect()->route('accounts.index')
                ->with('success', 'Account deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('accounts.index')
                ->with('error', $e->getMessage());
        }
    }
}
