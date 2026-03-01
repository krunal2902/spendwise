<?php

namespace App\Http\Controllers;

use App\Http\Requests\Transfer\StoreTransferRequest;
use App\Models\Transfer;
use App\Services\TransferService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class TransferController extends Controller
{
    public function __construct(
        private TransferService $transferService,
    ) {}

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = $request->user()->transfers()
                ->with(['fromAccount', 'toAccount'])
                ->select('transfers.*');

            return DataTables::eloquent($query)
                ->addColumn('from_account_name', fn($row) => $row->fromAccount->name ?? 'N/A')
                ->addColumn('to_account_name', fn($row) => $row->toAccount->name ?? 'N/A')
                ->addColumn('formatted_amount', fn($row) => '₹' . number_format($row->amount, 2))
                ->addColumn('formatted_date', fn($row) => $row->transfer_date->format('M d, Y'))
                ->addColumn('action', function ($row) {
                    $deleteUrl = route('transfers.destroy', $row->id);
                    return '<form method="POST" action="'.$deleteUrl.'" onsubmit="return confirm(\'Reverse this transfer? Balances will be restored.\')">
                        '.csrf_field().method_field('DELETE').'
                        <button type="submit" class="text-red-600 hover:text-red-800 text-sm"><i class="fas fa-undo"></i> Reverse</button>
                    </form>';
                })
                ->rawColumns(['action'])
                ->orderColumn('transfer_date', 'transfer_date $1')
                ->make(true);
        }

        return view('transfers.index');
    }

    public function create(Request $request): View
    {
        $accounts = $request->user()->accounts()->active()->get();
        return view('transfers.create', compact('accounts'));
    }

    public function store(StoreTransferRequest $request): RedirectResponse
    {
        try {
            $this->transferService->create($request->user(), $request->validated());
            return redirect()->route('transfers.index')
                ->with('success', 'Transfer completed successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function destroy(Request $request, Transfer $transfer): RedirectResponse
    {
        if ($transfer->user_id !== $request->user()->id) {
            abort(403);
        }

        $this->transferService->delete($transfer);

        return redirect()->route('transfers.index')
            ->with('success', 'Transfer reversed and balances restored.');
    }
}
