<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BudgetReportController extends Controller
{
    private ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Display a listing of budgets for analysis.
     */
    public function index(Request $request): View
    {
        $budgets = Budget::where('user_id', auth()->id())
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->orderBy('start_date', 'desc')
            ->paginate(12);

        return view('reports.budgets.index', compact('budgets'));
    }

    /**
     * Display the specified budget analysis.
     */
    public function show(Budget $budget): View
    {
        // Ensure user owns this budget
        if ($budget->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $analysis = $this->reportService->getBudgetAnalysis($budget->id);

        return view('reports.budgets.show', compact('budget', 'analysis'));
    }
}
