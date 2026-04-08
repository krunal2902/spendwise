<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ReportFilterRequest;
use App\Services\ReportService;
use Illuminate\View\View;

class ReportController extends Controller
{
    private ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Display the reports hub.
     */
    public function index(): View
    {
        return view('reports.index');
    }

    /**
     * Display account-wise report.
     */
    public function accountReport(ReportFilterRequest $request): View
    {
        $data = $this->reportService->getAccountReport(auth()->id(), $request->validated());
        return view('reports.account', $data);
    }

    /**
     * Display category-wise report.
     */
    public function categoryReport(ReportFilterRequest $request): View
    {
        $data = $this->reportService->getCategoryReport(auth()->id(), $request->validated());
        return view('reports.category', $data);
    }
}
