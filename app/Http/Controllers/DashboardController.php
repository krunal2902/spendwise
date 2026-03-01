<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService,
    ) {}

    public function __invoke(Request $request): View
    {
        $data = $this->dashboardService->getSummary($request->user());
        return view('dashboard', $data);
    }
}
