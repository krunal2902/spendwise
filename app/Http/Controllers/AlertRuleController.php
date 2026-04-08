<?php

namespace App\Http\Controllers;

use App\Models\AlertRule;
use App\Http\Requests\StoreAlertRuleRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AlertRuleController extends Controller
{
    /**
     * Display a listing of the rules.
     */
    public function index(): View
    {
        $rules = AlertRule::where('user_id', Auth::id())->get();
        return view('alert-rules.index', compact('rules'));
    }

    /**
     * Store a newly created rule.
     */
    public function store(StoreAlertRuleRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();
        
        AlertRule::create($data);

        return redirect()->route('alert-rules.index')->with('success', 'Alert rule created successfully.');
    }

    /**
     * Update the specified rule.
     */
    public function update(StoreAlertRuleRequest $request, AlertRule $alertRule): RedirectResponse
    {
        if ($alertRule->user_id !== Auth::id()) {
            abort(403);
        }

        $alertRule->update($request->validated());

        return redirect()->route('alert-rules.index')->with('success', 'Alert rule updated.');
    }

    /**
     * Remove the specified rule.
     */
    public function destroy(AlertRule $alertRule): RedirectResponse
    {
        if ($alertRule->user_id !== Auth::id()) {
            abort(403);
        }

        $alertRule->delete();

        return redirect()->route('alert-rules.index')->with('success', 'Alert rule deleted.');
    }

    /**
     * Toggle the active status.
     */
    public function toggle(AlertRule $alertRule): RedirectResponse
    {
        if ($alertRule->user_id !== Auth::id()) {
            abort(403);
        }

        $alertRule->update(['is_active' => !$alertRule->is_active]);

        return redirect()->route('alert-rules.index')->with('success', 'Rule status updated.');
    }
}
