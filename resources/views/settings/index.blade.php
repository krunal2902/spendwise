<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Settings & Modes') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Emergency Mode Setting -->
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center {{ $user->emergency_mode ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-500' }}">
                                <i class="fas fa-exclamation-triangle text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Emergency Mode</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-xl">
                                    When activated, Emergency Mode immediately blocks the creation and modification of non-essential expenses. Use this if you need to strictly halt discretionary spending during a financial crisis.
                                </p>
                            </div>
                        </div>
                        
                        <form action="{{ route('settings.emergency-mode.toggle') }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2 {{ $user->emergency_mode ? 'bg-red-600' : 'bg-gray-200' }}" role="switch" aria-checked="{{ $user->emergency_mode ? 'true' : 'false' }}">
                                <span class="sr-only">Use setting</span>
                                <span pointer-events="none" class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $user->emergency_mode ? 'translate-x-5' : 'translate-x-0' }}"></span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Privacy / Focus Mode Setting -->
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center {{ $user->focus_mode ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-100 text-gray-500' }}">
                                <i class="fas fa-eye-slash text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Privacy / Focus Mode</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-xl">
                                    When activated, exact financial figures (balances, incomes, expenses) will be obscured/blurred across the application to prevent shoulder-surfing. You can hover over any blurred figure to reveal it temporarily.
                                </p>
                            </div>
                        </div>
                        
                        <form action="{{ route('settings.focus-mode.toggle') }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2 {{ $user->focus_mode ? 'bg-indigo-600' : 'bg-gray-200' }}" role="switch" aria-checked="{{ $user->focus_mode ? 'true' : 'false' }}">
                                <span class="sr-only">Use setting</span>
                                <span pointer-events="none" class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $user->focus_mode ? 'translate-x-5' : 'translate-x-0' }}"></span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</x-app-layout>
