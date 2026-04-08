<?php

namespace App\Http\Controllers;

use App\Http\Requests\Group\StoreGroupExpenseRequest;
use App\Http\Requests\Group\UpdateGroupExpenseRequest;
use App\Models\Group;
use App\Models\GroupExpense;
use App\Models\Category;
use App\Services\GroupExpenseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class GroupExpenseController extends Controller
{
    public function __construct(
        private GroupExpenseService $expenseService,
    ) {}

    public function index(Request $request, Group $group)
    {
        if (!$group->hasMember($request->user()) && $group->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($request->ajax()) {
            $query = $group->expenses()->with(['paidBy', 'category', 'splits.user'])->select('group_expenses.*');

            return DataTables::eloquent($query)
                ->addColumn('paid_by_name', fn($row) => $row->paidBy->name)
                ->addColumn('category_name', fn($row) => $row->category ? $row->category->name : '-')
                ->addColumn('formatted_amount', fn($row) => '₹' . number_format($row->amount, 2))
                ->addColumn('split_info', function ($row) {
                    $badgeData = ['equal' => 'bg-blue-100 text-blue-800', 'exact' => 'bg-purple-100 text-purple-800', 'percentage' => 'bg-green-100 text-green-800'];
                    $color = $badgeData[$row->split_type] ?? 'bg-gray-100 text-gray-800';
                    return '<span class="px-2 py-1 text-xs rounded-full '.$color.'">'.ucfirst($row->split_type).'</span>';
                })
                ->addColumn('action', function ($row) use ($group, $request) {
                    $editUrl = route('groups.expenses.edit', [$group, $row]);
                    $deleteUrl = route('groups.expenses.destroy', [$group, $row]);
                    
                    $actions = '<div class="flex items-center gap-2">';
                    $actions .= '<a href="#" class="text-gray-600 hover:text-gray-800 text-sm" onclick="showExpenseDetail('.$row->id.')"><i class="fas fa-eye"></i></a>';
                    
                    if ($row->paid_by === $request->user()->id || $group->isAdmin($request->user())) {
                        $actions .= '<a href="'.$editUrl.'" class="text-indigo-600 hover:text-indigo-800 text-sm"><i class="fas fa-edit"></i></a>';
                        $actions .= '<form method="POST" action="'.$deleteUrl.'" onsubmit="return confirm(\'Delete this expense?\')">'.csrf_field().method_field('DELETE').'<button type="submit" class="text-red-600 hover:text-red-800 text-sm"><i class="fas fa-trash"></i></button></form>';
                    }
                    $actions .= '</div>';
                    return $actions;
                })
                ->rawColumns(['split_info', 'action'])
                ->make(true);
        }

        return view('groups.expenses.index', compact('group'));
    }

    public function create(Request $request, Group $group): View
    {
        if (!$group->hasMember($request->user()) && $group->user_id !== $request->user()->id) {
            abort(403);
        }

        $group->load(['memberships' => function ($q) {
            $q->where('status', 'active')->with('user');
        }]);
        $categories = Category::where('user_id', $request->user()->id)->get();

        return view('groups.expenses.create', compact('group', 'categories'));
    }

    public function store(StoreGroupExpenseRequest $request, Group $group): RedirectResponse
    {
        if (!$group->hasMember($request->user()) && $group->user_id !== $request->user()->id) {
            abort(403);
        }

        $data = $request->validated();

        if ($request->hasFile('receipt_image')) {
            $data['receipt_image'] = $request->file('receipt_image')->store('receipts', 'public');
        }

        try {
            $this->expenseService->create($group, $request->user(), $data);
            return redirect()->route('groups.expenses.index', $group)
                ->with('success', 'Group expense added successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function edit(Request $request, Group $group, GroupExpense $expense): View
    {
        if ($expense->group_id !== $group->id) abort(404);
        
        if ($expense->paid_by !== $request->user()->id && !$group->isAdmin($request->user())) {
            abort(403, 'You can only edit expenses you created or if you are a group admin.');
        }

        $group->load(['memberships' => function ($q) {
            $q->where('status', 'active')->with('user');
        }]);
        $categories = Category::where('user_id', $request->user()->id)->get();
        $expense->load('splits');

        return view('groups.expenses.edit', compact('group', 'expense', 'categories'));
    }

    public function update(UpdateGroupExpenseRequest $request, Group $group, GroupExpense $expense): RedirectResponse
    {
        if ($expense->group_id !== $group->id) abort(404);

        if ($expense->paid_by !== $request->user()->id && !$group->isAdmin($request->user())) {
            abort(403, 'You can only edit expenses you created or if you are a group admin.');
        }

        $data = $request->validated();

        if ($request->hasFile('receipt_image')) {
            if ($expense->receipt_image) {
                Storage::disk('public')->delete($expense->receipt_image);
            }
            $data['receipt_image'] = $request->file('receipt_image')->store('receipts', 'public');
        }

        try {
            $this->expenseService->update($expense, $data);
            return redirect()->route('groups.expenses.index', $group)
                ->with('success', 'Group expense updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function destroy(Request $request, Group $group, GroupExpense $expense): RedirectResponse
    {
        if ($expense->group_id !== $group->id) abort(404);

        if ($expense->paid_by !== $request->user()->id && !$group->isAdmin($request->user())) {
            abort(403, 'You can only delete expenses you created or if you are a group admin.');
        }

        if ($expense->receipt_image) {
            Storage::disk('public')->delete($expense->receipt_image);
        }

        $this->expenseService->delete($expense);

        return redirect()->route('groups.expenses.index', $group)
            ->with('success', 'Group expense deleted successfully.');
    }
}
