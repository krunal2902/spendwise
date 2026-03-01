<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Account;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = User::withCount(['accounts', 'incomes', 'expenses'])->select('users.*');

            return DataTables::eloquent($query)
            ->addColumn('accounts_count', fn($row) => $row->accounts_count)
            ->addColumn('incomes_count', fn($row) => $row->incomes_count)
            ->addColumn('expenses_count', fn($row) => $row->expenses_count)
            ->addColumn('role_badge', function ($row) {
                $color = $row->role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-600';
                return '<span class="px-2 py-1 text-xs rounded-full '.$color.'">'.ucfirst($row->role).'</span>';
            })
            ->addColumn('formatted_date', fn($row) => $row->created_at->format('M d, Y'))
            ->addColumn('action', function ($row) {
                return '<a href="'.route('admin.users.show', $row->id).'" class="text-indigo-600 hover:text-indigo-800 text-sm"><i class="fas fa-eye"></i> View</a>';
            })
            ->rawColumns(['role_badge', 'action'])
            ->make(true);
        }

        $totalUsers = User::count();
        $totalAccounts = Account::count();
        return view('admin.users.index', compact('totalUsers', 'totalAccounts'));
    }

    public function show(User $user): View
    {
        $user->loadCount(['accounts', 'incomes', 'expenses', 'transfers']);
        $accounts = $user->accounts()->get();
        $recentLogs = ActivityLog::where('user_id', $user->id)->latest()->take(10)->get();

        return view('admin.users.show', compact('user', 'accounts', 'recentLogs'));
    }
}
