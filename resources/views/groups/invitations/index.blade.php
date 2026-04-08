<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-gray-800">My Invitations</h2>
    </x-slot>

    <div class="max-w-3xl">
        @if($invitations->isEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center">
                <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-envelope-open text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-1">No pending invitations</h3>
                <p class="text-sm text-gray-500">When someone invites you to a group, it will appear here.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($invitations as $invitation)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                        <div class="flex items-start justify-between">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-lg font-bold flex-shrink-0">
                                    {{ strtoupper(substr($invitation->group->name, 0, 2)) }}
                                </div>
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900">{{ $invitation->group->name }}</h3>
                                    <p class="text-sm text-gray-500 mt-0.5">
                                        Invited by <span class="font-medium text-gray-700">{{ $invitation->invitedBy->name }}</span>
                                    </p>
                                    @if($invitation->group->description)
                                        <p class="text-sm text-gray-500 mt-1">{{ Str::limit($invitation->group->description, 100) }}</p>
                                    @endif
                                    <p class="text-xs text-gray-400 mt-2">
                                        <i class="fas fa-clock mr-1"></i>Expires {{ $invitation->expires_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0 ml-4">
                                <form method="POST" action="{{ route('invitations.accept', $invitation->token) }}">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition shadow-sm">
                                        <i class="fas fa-check text-xs"></i> Accept
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('invitations.decline', $invitation->token) }}">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
                                        <i class="fas fa-times text-xs"></i> Decline
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
