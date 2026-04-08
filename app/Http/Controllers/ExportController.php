<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Income;
use App\Services\ExportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExportController extends Controller
{
    private ExportService $exportService;

    public function __construct(ExportService $exportService)
    {
        $this->exportService = $exportService;
    }

    /**
     * Export Expenses to CSV.
     */
    public function exportExpensesCsv(Request $request)
    {
        $query = Expense::with(['category', 'account'])->where('user_id', Auth::id());
        
        // Optional month filtering
        if ($request->has('month') && $request->has('year')) {
            $query->whereMonth('expense_date', $request->month)
                  ->whereYear('expense_date', $request->year);
        }

        $query->orderBy('expense_date', 'desc');

        $headers = ['Date', 'Category', 'Account', 'Amount', 'Description', 'Notes', 'Reference'];
        
        $filename = 'expenses_export_' . now()->format('Ymd_His') . '.csv';

        return $this->exportService->exportToCsv($filename, $headers, $query, function ($expense) {
            return [
                $expense->expense_date->format('Y-m-d'),
                $expense->category->name ?? 'Uncategorized',
                $expense->account->name ?? 'Unknown',
                $expense->amount,
                $expense->description,
                $expense->notes,
                $expense->reference,
            ];
        });
    }

    /**
     * Export Incomes to CSV.
     */
    public function exportIncomesCsv(Request $request)
    {
        $query = Income::with(['category', 'account'])->where('user_id', Auth::id());
        
        if ($request->has('month') && $request->has('year')) {
            $query->whereMonth('income_date', $request->month)
                  ->whereYear('income_date', $request->year);
        }

        $query->orderBy('income_date', 'desc');

        $headers = ['Date', 'Category', 'Account', 'Amount', 'Description', 'Reference'];
        
        $filename = 'incomes_export_' . now()->format('Ymd_His') . '.csv';

        return $this->exportService->exportToCsv($filename, $headers, $query, function ($income) {
            return [
                $income->income_date->format('Y-m-d'),
                $income->category->name ?? 'Uncategorized',
                $income->account->name ?? 'Unknown',
                $income->amount,
                $income->description,
                $income->reference,
            ];
        });
    }

    /**
     * Generate a PDF Monthly Statement.
     */
    public function exportMonthlyStatementPdf(Request $request)
    {
        $userId = Auth::id();
        $date = $request->has('month') ? Carbon::create($request->year, $request->month, 1) : Carbon::now();
        
        $incomes = Income::with('category')->where('user_id', $userId)
            ->whereMonth('income_date', $date->month)
            ->whereYear('income_date', $date->year)
            ->orderBy('income_date', 'asc')
            ->get();
            
        $expenses = Expense::with('category')->where('user_id', $userId)
            ->whereMonth('expense_date', $date->month)
            ->whereYear('expense_date', $date->year)
            ->orderBy('expense_date', 'asc')
            ->get();

        $totalIncome = $incomes->sum('amount');
        $totalExpense = $expenses->sum('amount');
        $netFlow = $totalIncome - $totalExpense;

        // Group expenses by category
        $expensesByCategory = $expenses->groupBy('category.name')->map(function ($group) {
            return $group->sum('amount');
        })->sortDesc();

        $data = [
            'user' => Auth::user(),
            'monthName' => $date->format('F Y'),
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'netFlow' => $netFlow,
            'incomes' => $incomes,
            'expenses' => $expenses,
            'expensesByCategory' => $expensesByCategory,
            'generatedAt' => now()->format('Y-m-d H:i:s'),
        ];

        return $this->exportService->exportToPdf('exports.pdf.monthly-statement', $data, 'Statement_' . $date->format('Y_M') . '.pdf');
    }
}
