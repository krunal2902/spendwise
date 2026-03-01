<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-gray-800">Categories</h2>
    </x-slot>

    <div class="flex items-center justify-between mb-5">
        <p class="text-sm text-gray-500">Manage income & expense categories</p>
        <a href="{{ route('categories.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition shadow-sm">
            <i class="fas fa-plus text-xs"></i> New Category
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <table id="categoriesTable" class="w-full stripe hover" style="width:100%">
            <thead>
                <tr>
                    <th>Color</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Source</th>
                    <th>Status</th>
                    <th>Lock</th>
                    <th>Actions</th>
                </tr>
            </thead>
        </table>
    </div>

    @push('scripts')
    <script>
        $(function() {
            $('#categoriesTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('categories.index') }}',
                columns: [
                    { data: 'color_dot', name: 'color', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'name', name: 'name' },
                    { data: 'type_badge', name: 'type' },
                    { data: 'system_badge', name: 'is_system' },
                    { data: 'status_badge', name: 'is_active' },
                    { data: 'lock_badge', name: 'is_locked', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                ],
                order: [[1, 'asc']],
                pageLength: 25,
                language: { emptyTable: 'No categories found.' }
            });
        });
    </script>
    @endpush
</x-app-layout>
