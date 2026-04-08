<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * Display the settings page.
     */
    public function index(): View
    {
        return view('settings.index', [
            'user' => Auth::user()
        ]);
    }

    /**
     * Toggle emergency mode.
     */
    public function toggleEmergencyMode(): RedirectResponse
    {
        $user = Auth::user();
        $user->emergency_mode = !$user->emergency_mode;
        $user->save();

        $status = $user->emergency_mode ? 'enabled' : 'disabled';
        return back()->with('success', "Emergency Mode has been {$status}.");
    }

    /**
     * Toggle focus / privacy mode.
     */
    public function toggleFocusMode(): RedirectResponse
    {
        $user = Auth::user();
        $user->focus_mode = !$user->focus_mode;
        $user->save();

        $status = $user->focus_mode ? 'enabled' : 'disabled';
        return back()->with('success', "Privacy / Focus Mode has been {$status}.");
    }
}
