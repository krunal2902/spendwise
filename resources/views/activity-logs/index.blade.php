<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-gray-800">Activity Log</h2>
    </x-slot>

    <div class="mb-5">
        <p class="text-sm text-gray-500">All your account activities are tracked here automatically</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <table id="activityTable" class="w-full stripe hover" style="width:100%">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Action</th>
                    <th>Model</th>
                    <th>Details</th>
                </tr>
            </thead>
        </table>
    </div>

    @push('scripts')
    <script>
        $(function() {
            $('#activityTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('activity-logs.index') }}',
                columns: [
                    { data: 'formatted_time', name: 'created_at' },
                    { data: 'action_badge', name: 'action' },
                    { data: 'model_info', name: 'model_type', orderable: false },
                    { data: 'details', name: 'details', orderable: false, searchable: false }
                ],
                order: [[0, 'desc']],
                pageLength: 25,
                language: { emptyTable: 'No activity recorded yet.' }
            });
        });
    </script>
    @endpush
</x-app-layout>
