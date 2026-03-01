<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-gray-800">Expenses</h2>
    </x-slot>

    <div class="flex items-center justify-between mb-5">
        <p class="text-sm text-gray-500">Track all your spending</p>
        <a href="{{ route('expenses.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition shadow-sm">
            <i class="fas fa-plus text-xs"></i> Add Expense
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <table id="expensesTable" class="w-full stripe hover" style="width:100%">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Category</th>
                    <th>Account</th>
                    <th>Description</th>
                    <th>Amount</th>
                    <th>Actions</th>
                </tr>
            </thead>
        </table>
    </div>

    @push('scripts')
    <script>
        $(function() {
            $('#expensesTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('expenses.index') }}',
                columns: [
                    { data: 'formatted_date', name: 'expense_date' },
                    { data: 'category_name', name: 'category_name', orderable: false },
                    { data: 'account_name', name: 'account_name', orderable: false },
                    { data: 'description', name: 'description', defaultContent: '—' },
                    { data: 'formatted_amount', name: 'amount', className: 'text-right font-semibold text-red-600' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                ],
                order: [[0, 'desc']],
                pageLength: 15,
                language: { emptyTable: 'No expenses recorded yet. Click "+ Add Expense" to get started.' }
            });
        });
    </script>
    @endpush
</x-app-layout>
