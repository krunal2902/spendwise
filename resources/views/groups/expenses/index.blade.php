<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('groups.show', $group) }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left"></i></a>
            <h2 class="text-xl font-bold text-gray-800">{{ $group->name }} - Expenses</h2>
        </div>
    </x-slot>

    <div class="flex items-center justify-between mb-5">
        <div>
            <p class="text-sm text-gray-500">Track and manage group expenses</p>
        </div>
        <a href="{{ route('groups.expenses.create', $group) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition shadow-sm">
            <i class="fas fa-plus text-xs"></i> Add Expense
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <table id="groupExpensesTable" class="w-full stripe hover" style="width:100%">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Description</th>
                    <th>Paid By</th>
                    <th>Category</th>
                    <th>Amount</th>
                    <th>Split Type</th>
                    <th>Actions</th>
                </tr>
            </thead>
        </table>
    </div>

    @push('scripts')
    <script>
        $(function() {
            $('#groupExpensesTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('groups.expenses.index', $group) }}',
                columns: [
                    { data: 'expense_date', name: 'expense_date' },
                    { data: 'description', name: 'description' },
                    { data: 'paid_by_name', name: 'paid_by_name' },
                    { data: 'category_name', name: 'category_name' },
                    { data: 'formatted_amount', name: 'amount', className: 'text-right font-semibold' },
                    { data: 'split_info', name: 'split_type' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                ],
                order: [[0, 'desc']],
                pageLength: 15,
                language: { emptyTable: 'No expenses yet. Click "Add Expense" to add one.' }
            });
        });

        // Placeholder for viewing details modal if needed
        function showExpenseDetail(id) {
            alert("Expense view not yet implemented fully, check edit page for now.");
        }
    </script>
    @endpush
</x-app-layout>
