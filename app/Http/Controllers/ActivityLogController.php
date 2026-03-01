<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = ActivityLog::where('user_id', $request->user()->id)
                ->select('activity_logs.*');

            return DataTables::eloquent($query)
                ->addColumn('formatted_time', fn($row) => $row->created_at->format('M d, Y H:i'))
                ->addColumn('action_badge', function ($row) {
                    $colors = [
                        'created' => 'bg-green-100 text-green-800',
                        'updated' => 'bg-blue-100 text-blue-800',
                        'deleted' => 'bg-red-100 text-red-800',
                    ];
                    $color = $colors[$row->action] ?? 'bg-gray-100 text-gray-800';
                    return '<span class="px-2 py-1 text-xs rounded-full '.$color.'">'.ucfirst($row->action).'</span>';
                })
                ->addColumn('model_info', fn($row) => class_basename($row->model_type) . ' #' . $row->model_id)
                ->addColumn('details', function ($row) {
                    $vals = $row->new_values ?? $row->old_values;
                    if (!$vals) return '—';
                    $parts = [];
                    if (isset($vals['amount'])) $parts[] = '₹' . number_format($vals['amount'], 2);
                    if (isset($vals['description'])) $parts[] = $vals['description'];
                    return implode(' — ', $parts) ?: '—';
                })
                ->rawColumns(['action_badge'])
                ->make(true);
        }

        return view('activity-logs.index');
    }
}
