<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('groups.index') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left"></i></a>
            <h2 class="text-xl font-bold text-gray-800">{{ $group->name }}</h2>
            @if($isOwner)
                <span class="px-2 py-1 text-xs rounded-full bg-amber-100 text-amber-800">Owner</span>
            @elseif($isAdmin)
                <span class="px-2 py-1 text-xs rounded-full bg-indigo-100 text-indigo-800">Admin</span>
            @endif
        </div>
    </x-slot>

    {{-- Group Details Card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="flex items-center gap-4 md:col-span-2">
                @if($group->image)
                    <img src="{{ asset('storage/' . $group->image) }}" alt="{{ $group->name }}"
                         class="w-16 h-16 rounded-xl object-cover border border-gray-200" />
                @else
                    <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-xl font-bold">
                        {{ strtoupper(substr($group->name, 0, 2)) }}
                    </div>
                @endif
                <div>
                    <p class="text-lg font-semibold text-gray-900">{{ $group->name }}</p>
                    @if($group->description)
                        <p class="text-sm text-gray-500 mt-1">{{ $group->description }}</p>
                    @endif
                </div>
            </div>
            <div>
                <p class="text-sm text-gray-500">Members</p>
                <p class="text-2xl font-bold text-gray-900">{{ $group->memberships->count() }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Currency</p>
                <p class="text-lg font-medium text-gray-900">{{ $group->currency }}</p>
            </div>
        </div>

        {{-- Action buttons --}}
        <div class="flex items-center gap-3 mt-5 pt-4 border-t border-gray-100 flex-wrap">
            <a href="{{ route('groups.expenses.index', $group) }}"
               class="inline-flex items-center gap-2 px-3 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition shadow-sm">
                <i class="fas fa-receipt text-xs"></i> Group Expenses
            </a>
            <a href="{{ route('groups.balances.index', $group) }}"
               class="inline-flex items-center gap-2 px-3 py-2 bg-teal-50 text-teal-700 rounded-lg text-sm font-medium hover:bg-teal-100 transition shadow-sm">
                <i class="fas fa-balance-scale text-xs"></i> Balances & Settlements
            </a>
            @if($isAdmin)
                <a href="{{ route('groups.edit', $group) }}"
                   class="inline-flex items-center gap-2 px-3 py-2 bg-indigo-50 text-indigo-700 rounded-lg text-sm font-medium hover:bg-indigo-100 transition">
                    <i class="fas fa-edit text-xs"></i> Edit Group
                </a>
                <a href="{{ route('groups.members.index', $group) }}"
                   class="inline-flex items-center gap-2 px-3 py-2 bg-teal-50 text-teal-700 rounded-lg text-sm font-medium hover:bg-teal-100 transition">
                    <i class="fas fa-user-plus text-xs"></i> Manage Members
                </a>
            @endif
            @if(!$isOwner)
                <form method="POST" action="{{ route('groups.leave', $group) }}" onsubmit="return confirm('Are you sure you want to leave this group?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="inline-flex items-center gap-2 px-3 py-2 bg-red-50 text-red-700 rounded-lg text-sm font-medium hover:bg-red-100 transition">
                        <i class="fas fa-sign-out-alt text-xs"></i> Leave Group
                    </button>
                </form>
            @endif
            @if($isOwner)
                <form method="POST" action="{{ route('groups.destroy', $group) }}" onsubmit="return confirm('Delete this group? This action cannot be undone.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="inline-flex items-center gap-2 px-3 py-2 bg-red-50 text-red-700 rounded-lg text-sm font-medium hover:bg-red-100 transition">
                        <i class="fas fa-trash text-xs"></i> Delete Group
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        
        {{-- Spending Overview --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 h-full">
                <div class="flex items-center justify-between mb-4 border-b pb-2">
                    <h3 class="text-sm font-semibold text-gray-800">
                        <i class="fas fa-chart-bar text-indigo-500 mr-2"></i>Spending Overview
                    </h3>
                    <span class="text-xs font-medium text-gray-500 text-right">Total Spent<br><span class="text-lg font-bold text-gray-900">₹{{ number_format($totalSpent, 2) }}</span></span>
                </div>
                
                @if($memberSpents->isEmpty())
                    <p class="text-sm text-gray-400 text-center py-6">No expenses recorded yet.</p>
                @else
                    <div class="space-y-4 pt-2">
                        @php $maxSpent = $memberSpents->max(); @endphp
                        @foreach($memberSpents as $userId => $spent)
                            @php
                                $member = $group->memberships->where('user_id', $userId)->first();
                                if(!$member) continue;
                            @endphp
                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm font-medium text-gray-700">{{ $member->user->name }}</span>
                                    <span class="text-sm font-semibold text-gray-900">₹{{ number_format($spent, 2) }}</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2">
                                    <div class="h-2 rounded-full bg-indigo-500" style="width: {{ $maxSpent > 0 ? ($spent / $maxSpent) * 100 : 0 }}%;"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Activity Feed --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 h-full">
                <h3 class="text-sm font-semibold text-gray-800 mb-4 border-b pb-2">
                    <i class="fas fa-history text-indigo-500 mr-2"></i>Activity Feed
                </h3>
                
                @if($activityFeed->isEmpty())
                    <p class="text-sm text-gray-400 text-center py-4">No recent activity.</p>
                @else
                    <div class="space-y-3">
                        @foreach($activityFeed as $activity)
                            <div class="flex gap-3">
                                <div class="mt-0.5">
                                    <div class="w-6 h-6 rounded-full bg-gray-50 flex items-center justify-center border border-gray-100">
                                        <i class="fas {{ $activity['icon'] }} text-[10px]"></i>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-gray-800">{{ $activity['title'] }}</p>
                                    <p class="text-[11px] text-gray-500 leading-tight mt-0.5">{{ $activity['description'] }}</p>
                                    <p class="text-[10px] text-gray-400 mt-0.5">{{ $activity['date']->diffForHumans() }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
        
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Invite Code Card --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="text-sm font-semibold text-gray-800 mb-4">
                    <i class="fas fa-link text-indigo-500 mr-2"></i>Invite Code
                </h3>
                <div class="bg-gray-50 rounded-lg p-4 text-center mb-3">
                    <p class="text-2xl font-bold tracking-widest text-indigo-600" id="inviteCode">{{ $group->invite_code }}</p>
                    <p class="text-xs text-gray-500 mt-1">Share this code to invite members</p>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="copyInviteCode()" class="flex-1 inline-flex items-center justify-center gap-2 px-3 py-2 bg-indigo-50 text-indigo-700 rounded-lg text-sm font-medium hover:bg-indigo-100 transition">
                        <i class="fas fa-copy text-xs"></i> Copy
                    </button>
                    @if($isAdmin)
                        <form method="POST" action="{{ route('groups.regenerate-code', $group) }}" class="flex-1">
                            @csrf
                            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 bg-gray-50 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-100 transition">
                                <i class="fas fa-sync text-xs"></i> New Code
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        {{-- Members List --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="text-sm font-semibold text-gray-800 mb-4">
                    <i class="fas fa-users text-indigo-500 mr-2"></i>Members ({{ $group->memberships->count() }})
                </h3>
                <div class="space-y-3">
                    @foreach($group->memberships as $membership)
                        <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-gray-50' : '' }}">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center text-white text-sm font-bold">
                                    {{ strtoupper(substr($membership->user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ $membership->user->name }}
                                        @if($membership->user_id === $group->user_id)
                                            <span class="ml-1 text-xs text-amber-600">(Owner)</span>
                                        @endif
                                        @if($membership->user_id === auth()->id())
                                            <span class="ml-1 text-xs text-indigo-600">(You)</span>
                                        @endif
                                    </p>
                                    <p class="text-xs text-gray-500">{{ $membership->user->email }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                @php
                                    $roleColors = ['admin' => 'bg-indigo-100 text-indigo-800', 'member' => 'bg-gray-100 text-gray-600'];
                                @endphp
                                <span class="px-2 py-1 text-xs rounded-full {{ $roleColors[$membership->role] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ ucfirst($membership->role) }}
                                </span>

                                @if($isAdmin && $membership->user_id !== $group->user_id && $membership->user_id !== auth()->id())
                                    <div class="flex items-center gap-1">
                                        {{-- Toggle Role --}}
                                        <form method="POST" action="{{ route('groups.change-role', [$group, $membership->user]) }}">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="role" value="{{ $membership->role === 'admin' ? 'member' : 'admin' }}" />
                                            <button type="submit" class="text-gray-400 hover:text-indigo-600 text-sm" title="Change role to {{ $membership->role === 'admin' ? 'Member' : 'Admin' }}">
                                                <i class="fas fa-user-edit"></i>
                                            </button>
                                        </form>
                                        {{-- Remove --}}
                                        <form method="POST" action="{{ route('groups.remove-member', [$group, $membership->user]) }}" onsubmit="return confirm('Remove this member from the group?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-gray-400 hover:text-red-600 text-sm" title="Remove member">
                                                <i class="fas fa-user-minus"></i>
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function copyInviteCode() {
            const code = document.getElementById('inviteCode').textContent.trim();
            navigator.clipboard.writeText(code).then(() => {
                // Quick feedback
                const btn = event.target.closest('button');
                const original = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check text-xs"></i> Copied!';
                setTimeout(() => { btn.innerHTML = original; }, 2000);
            });
        }
    </script>
    @endpush
</x-app-layout>
