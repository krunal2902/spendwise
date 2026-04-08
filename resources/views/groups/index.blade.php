<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-gray-800">Groups</h2>
    </x-slot>

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-5">
        <div>
            <p class="text-sm text-gray-500">Manage your shared expense groups</p>
        </div>
        <div class="flex items-center gap-3">
            {{-- Join Group --}}
            <button onclick="document.getElementById('joinModal').classList.remove('hidden')"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition shadow-sm">
                <i class="fas fa-sign-in-alt text-xs"></i> Join Group
            </button>
            {{-- Create Group --}}
            <a href="{{ route('groups.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition shadow-sm">
                <i class="fas fa-plus text-xs"></i> New Group
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <table id="groupsTable" class="w-full stripe hover" style="width:100%">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Owner</th>
                    <th>Members</th>
                    <th>Your Role</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
        </table>
    </div>

    {{-- Join Group Modal --}}
    <div id="joinModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-800">Join a Group</h3>
                <button onclick="document.getElementById('joinModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" action="{{ route('groups.join') }}">
                @csrf
                <div class="mb-4">
                    <x-input-label for="invite_code" :value="__('Invite Code')" />
                    <x-text-input id="invite_code" name="invite_code" type="text" class="mt-1 block w-full uppercase tracking-widest"
                                  placeholder="e.g., ABCD1234" required maxlength="20" />
                    <p class="text-xs text-gray-500 mt-1">Enter the 8-character code shared by the group admin.</p>
                </div>
                <div class="flex items-center gap-3">
                    <x-primary-button>{{ __('Join Group') }}</x-primary-button>
                    <button type="button" onclick="document.getElementById('joinModal').classList.add('hidden')"
                            class="text-sm text-gray-600 hover:text-gray-900">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        $(function() {
            $('#groupsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('groups.index') }}',
                columns: [
                    { data: 'name', name: 'name' },
                    { data: 'owner_name', name: 'owner_name', orderable: false },
                    { data: 'members_count', name: 'members_count', orderable: false, searchable: false },
                    { data: 'role_badge', name: 'role_badge', orderable: false, searchable: false },
                    { data: 'created_at', name: 'created_at', render: function(data) {
                        return new Date(data).toLocaleDateString('en-IN', { year: 'numeric', month: 'short', day: 'numeric' });
                    }},
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                ],
                order: [[4, 'desc']],
                pageLength: 15,
                language: { emptyTable: 'No groups yet. Create a new group or join one with an invite code.' }
            });
        });
    </script>
    @endpush
</x-app-layout>
