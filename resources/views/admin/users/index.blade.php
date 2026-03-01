<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-gray-800">Admin — Users</h2>
    </x-slot>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-6">
        <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-white/70 uppercase">Total Users</p>
                    <p class="text-2xl font-bold mt-1">{{ $totalUsers }}</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-white/10 flex items-center justify-center">
                    <i class="fas fa-users text-xl"></i>
                </div>
            </div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-white/70 uppercase">Total Accounts</p>
                    <p class="text-2xl font-bold mt-1">{{ $totalAccounts }}</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-white/10 flex items-center justify-center">
                    <i class="fas fa-university text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <table id="usersTable" class="w-full stripe hover" style="width:100%">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Accounts</th>
                    <th>Income</th>
                    <th>Expenses</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
        </table>
    </div>

    @push('scripts')
    <script>
        $(function() {
            $('#usersTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('admin.users.index') }}',
                columns: [
                    { data: 'name', name: 'name' },
                    { data: 'email', name: 'email' },
                    { data: 'role_badge', name: 'role' },
                    { data: 'accounts_count', name: 'accounts_count', className: 'text-center' },
                    { data: 'incomes_count', name: 'incomes_count', className: 'text-center' },
                    { data: 'expenses_count', name: 'expenses_count', className: 'text-center' },
                    { data: 'formatted_date', name: 'created_at' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                ],
                order: [[0, 'asc']],
                pageLength: 20,
                language: { emptyTable: 'No users found.' }
            });
        });
    </script>
    @endpush
</x-app-layout>
