<?php

namespace App\Http\Controllers;

use App\Services\FinancialHealthService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinancialHealthController extends Controller
{
    private FinancialHealthService $healthService;

    public function __construct(FinancialHealthService $healthService)
    {
        $this->healthService = $healthService;
    }

    /**
     * Show the user's financial health score dashboard.
     */
    public function show(): View
    {
        $healthData = $this->healthService->calculateScore(auth()->id());
        
        return view('health-score.show', compact('healthData'));
    }
}
