<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-gray-800">Accounts</h2>
    </x-slot>

    <div class="flex items-center justify-between mb-5">
        <div>
            <p class="text-sm text-gray-500">Total Balance: <span class="font-bold text-gray-800">₹{{ number_format($totalBalance, 2) }}</span></p>
        </div>
        <a href="{{ route('accounts.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition shadow-sm">
            <i class="fas fa-plus text-xs"></i> New Account
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <table id="accountsTable" class="w-full stripe hover" style="width:100%">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Balance</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
        </table>
    </div>

    @push('scripts')
    <script>
        $(function() {
            $('#accountsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('accounts.index') }}',
                columns: [
                    { data: 'name', name: 'name' },
                    { data: 'type_badge', name: 'type' },
                    { data: 'formatted_balance', name: 'balance', className: 'text-right font-semibold' },
                    { data: 'is_active', name: 'is_active', render: function(data) {
                        return data ? '<span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Active</span>' :
                                      '<span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-500">Inactive</span>';
                    }},
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                ],
                order: [[0, 'asc']],
                pageLength: 15,
                language: { emptyTable: 'No accounts yet. Click "+ New Account" to create one.' }
            });
        });
    </script>
    @endpush
</x-app-layout>
