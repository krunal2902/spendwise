<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('groups.show', $group) }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left"></i></a>
            <h2 class="text-xl font-bold text-gray-800">Members – {{ $group->name }}</h2>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Members List --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-800">
                        <i class="fas fa-users text-indigo-500 mr-2"></i>Active Members ({{ $group->memberships->count() }})
                    </h3>
                </div>
                <div class="space-y-2">
                    @foreach($group->memberships as $membership)
                        <div class="flex items-center justify-between p-3 rounded-lg {{ $loop->even ? 'bg-gray-50' : '' }}">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center text-white text-sm font-bold">
                                    {{ strtoupper(substr($membership->user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ $membership->user->name }}
                                        @if($membership->user_id === $group->user_id)
                                            <span class="ml-1 px-1.5 py-0.5 text-xs rounded bg-amber-100 text-amber-700">Owner</span>
                                        @endif
                                        @if($membership->user_id === auth()->id())
                                            <span class="ml-1 text-xs text-indigo-600">(You)</span>
                                        @endif
                                    </p>
                                    <p class="text-xs text-gray-500">{{ $membership->user->email }} · Joined {{ $membership->joined_at->format('M d, Y') }}</p>
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
                                        <form method="POST" action="{{ route('groups.members.role', [$group, $membership->user]) }}">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="role" value="{{ $membership->role === 'admin' ? 'member' : 'admin' }}" />
                                            <button type="submit" class="p-1.5 text-gray-400 hover:text-indigo-600 rounded hover:bg-indigo-50 transition" title="Change to {{ $membership->role === 'admin' ? 'Member' : 'Admin' }}">
                                                <i class="fas fa-user-edit text-xs"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('groups.members.remove', [$group, $membership->user]) }}" onsubmit="return confirm('Remove {{ $membership->user->name }} from this group?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 rounded hover:bg-red-50 transition" title="Remove member">
                                                <i class="fas fa-user-minus text-xs"></i>
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

        {{-- Invite Sidebar --}}
        <div class="lg:col-span-1 space-y-5">

            {{-- Invite by Email --}}
            @if($isAdmin)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="text-sm font-semibold text-gray-800 mb-3">
                    <i class="fas fa-envelope text-indigo-500 mr-2"></i>Invite by Email
                </h3>
                <form method="POST" action="{{ route('groups.invitations.store', $group) }}">
                    @csrf
                    <div class="mb-3">
                        <x-text-input id="email" name="email" type="email" class="block w-full text-sm"
                                      placeholder="user@example.com" required />
                        <x-input-error :messages="$errors->get('email')" class="mt-1" />
                    </div>
                    <x-primary-button class="w-full justify-center text-sm">
                        <i class="fas fa-paper-plane mr-2 text-xs"></i> Send Invitation
                    </x-primary-button>
                </form>
            </div>
            @endif

            {{-- Invite Code --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="text-sm font-semibold text-gray-800 mb-3">
                    <i class="fas fa-link text-indigo-500 mr-2"></i>Invite Code
                </h3>
                <div class="bg-gray-50 rounded-lg p-3 text-center mb-3">
                    <p class="text-xl font-bold tracking-widest text-indigo-600" id="inviteCode">{{ $group->invite_code }}</p>
                </div>
                <button onclick="navigator.clipboard.writeText('{{ $group->invite_code }}').then(() => { this.textContent = '✓ Copied!'; setTimeout(() => { this.textContent = 'Copy Code'; }, 2000); })"
                        class="w-full px-3 py-2 bg-indigo-50 text-indigo-700 rounded-lg text-sm font-medium hover:bg-indigo-100 transition">
                    Copy Code
                </button>
            </div>

            {{-- Pending Invitations --}}
            @if($isAdmin)
            @php
                $pendingInvitations = $group->invitations()->where('status', 'pending')->where('expires_at', '>=', now())->with('invitedBy')->latest()->get();
            @endphp
            @if($pendingInvitations->isNotEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="text-sm font-semibold text-gray-800 mb-3">
                    <i class="fas fa-clock text-amber-500 mr-2"></i>Pending Invitations ({{ $pendingInvitations->count() }})
                </h3>
                <div class="space-y-2">
                    @foreach($pendingInvitations as $invitation)
                        <div class="flex items-center justify-between p-2 bg-amber-50 rounded-lg">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $invitation->email }}</p>
                                <p class="text-xs text-gray-500">Expires {{ $invitation->expires_at->diffForHumans() }}</p>
                            </div>
                            <form method="POST" action="{{ route('groups.invitations.revoke', [$group, $invitation]) }}" onsubmit="return confirm('Revoke this invitation?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 rounded hover:bg-red-50 transition" title="Revoke invitation">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
            @endif

        </div>
    </div>
</x-app-layout>
