<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Notifications') }}
            </h2>
            <form action="{{ route('notifications.mark-all-read') }}" method="POST">
                @csrf
                <button type="submit" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 font-medium">
                    Mark all as read
                </button>
            </form>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="p-0">
                    @forelse($notifications as $notification)
                        <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-start {{ $notification->is_read ? 'bg-white dark:bg-gray-800' : 'bg-blue-50 dark:bg-blue-900/20' }}">
                            
                            <div class="flex gap-4">
                                <div class="mt-1 flex-shrink-0">
                                    @if($notification->type === 'budget_threshold')
                                        <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center"><i class="fas fa-chart-pie text-sm"></i></div>
                                    @elseif($notification->type === 'low_balance')
                                        <div class="w-8 h-8 rounded-full bg-red-100 text-red-600 flex items-center justify-center"><i class="fas fa-arrow-down text-sm"></i></div>
                                    @elseif($notification->type === 'large_expense')
                                        <div class="w-8 h-8 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center"><i class="fas fa-exclamation-triangle text-sm"></i></div>
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-gray-100 text-gray-600 flex items-center justify-center"><i class="fas fa-bell text-sm"></i></div>
                                    @endif
                                </div>
                                
                                <div>
                                    <h4 class="font-bold text-gray-800 dark:text-gray-200">{{ $notification->title }}</h4>
                                    <p class="text-gray-600 dark:text-gray-400 mt-1">{{ $notification->message }}</p>
                                    <p class="text-xs text-gray-400 mt-2">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                            
                            @if(!$notification->is_read)
                                <form action="{{ route('notifications.mark-read', $notification) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium" title="Mark as read">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                            @endif
                            
                        </div>
                    @empty
                        <div class="p-12 text-center text-gray-500">
                            <i class="far fa-bell-slash text-4xl mb-3 text-gray-300"></i>
                            <p>You have no notifications.</p>
                        </div>
                    @endforelse
                </div>
                
                @if($notifications->hasPages())
                    <div class="p-4 border-t border-gray-100 dark:border-gray-700">
                        {{ $notifications->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
