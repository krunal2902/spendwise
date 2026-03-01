<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-gray-800">Transfers</h2>
    </x-slot>

    <div class="flex items-center justify-between mb-5">
        <p class="text-sm text-gray-500">Move money between accounts</p>
        <a href="{{ route('transfers.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition shadow-sm">
            <i class="fas fa-exchange-alt text-xs"></i> New Transfer
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <table id="transfersTable" class="w-full stripe hover" style="width:100%">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Amount</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
        </table>
    </div>

    @push('scripts')
    <script>
        $(function() {
            $('#transfersTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('transfers.index') }}',
                columns: [
                    { data: 'formatted_date', name: 'transfer_date' },
                    { data: 'from_account_name', name: 'from_account_name', orderable: false },
                    { data: 'to_account_name', name: 'to_account_name', orderable: false },
                    { data: 'formatted_amount', name: 'amount', className: 'text-right font-semibold text-indigo-600' },
                    { data: 'description', name: 'description', defaultContent: '—' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                ],
                order: [[0, 'desc']],
                pageLength: 15,
                language: { emptyTable: 'No transfers yet. Click "+ New Transfer" to move funds.' }
            });
        });
    </script>
    @endpush
</x-app-layout>
