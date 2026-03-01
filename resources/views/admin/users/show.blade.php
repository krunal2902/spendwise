<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.users.index') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left"></i></a>
            <h2 class="text-xl font-bold text-gray-800">User: {{ $user->name }}</h2>
        </div>
    </x-slot>

    {{-- User Info --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <p class="text-sm text-gray-500">Email</p>
                <p class="text-sm font-medium text-gray-900">{{ $user->email }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Role</p>
                <span class="px-2 py-1 text-xs rounded-full {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-600' }}">
                    {{ ucfirst($user->role) }}
                </span>
            </div>
            <div>
                <p class="text-sm text-gray-500">Joined</p>
                <p class="text-sm text-gray-700">{{ $user->created_at->format('M d, Y H:i') }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Stats</p>
                <p class="text-sm text-gray-700">{{ $user->accounts_count }} accounts · {{ $user->incomes_count }} income · {{ $user->expenses_count }} expenses · {{ $user->transfers_count }} transfers</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        {{-- User Accounts --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="p-5">
                <h3 class="text-sm font-semibold text-gray-800 mb-4"><i class="fas fa-university text-indigo-500 mr-2"></i>Accounts</h3>
                @forelse($accounts as $account)
                    <div class="flex justify-between items-center py-2 border-b last:border-b-0">
                        <div class="flex items-center gap-2">
                            @if($account->color)
                                <span class="w-3 h-3 rounded-full" style="background-color: {{ $account->color }}"></span>
                            @endif
                            <span class="text-sm font-medium text-gray-700">{{ $account->name }}</span>
                            <span class="text-xs text-gray-500">({{ ucfirst($account->type) }})</span>
                        </div>
                        <span class="text-sm font-semibold {{ $account->balance >= 0 ? 'text-gray-900' : 'text-red-600' }}">
                            ₹{{ number_format($account->balance, 2) }}
                        </span>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">No accounts.</p>
                @endforelse
            </div>
        </div>

        {{-- User Activity --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="p-5">
                <h3 class="text-sm font-semibold text-gray-800 mb-4"><i class="fas fa-history text-indigo-500 mr-2"></i>Recent Activity</h3>
                @forelse($recentLogs as $log)
                    <div class="flex justify-between items-center py-2 border-b last:border-b-0">
                        <div>
                            <span class="px-2 py-0.5 text-xs rounded-full
                                {{ $log->action === 'created' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $log->action === 'updated' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $log->action === 'deleted' ? 'bg-red-100 text-red-800' : '' }}">
                                {{ $log->action }}
                            </span>
                            <span class="text-sm text-gray-500 ml-1">{{ class_basename($log->model_type) }} #{{ $log->model_id }}</span>
                        </div>
                        <span class="text-xs text-gray-400">{{ $log->created_at->diffForHumans() }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">No activity.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
