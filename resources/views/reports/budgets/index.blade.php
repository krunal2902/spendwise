<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Budget Analysis') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <p class="mb-6 text-gray-600 dark:text-gray-400">Select a budget to view its detailed actual vs planned analysis.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @forelse($budgets as $budget)
                            <a href="{{ route('reports.budgets.show', $budget) }}" class="block group">
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-5 hover:shadow-md transition bg-white dark:bg-gray-800 group-hover:border-emerald-500">
                                    <div class="flex justify-between items-start mb-4">
                                        <h3 class="text-lg font-bold text-gray-800 dark:text-white">{{ $budget->name }}</h3>
                                        <span class="px-2 py-1 bg-emerald-100 text-emerald-800 text-xs rounded-full dark:bg-emerald-900 dark:text-emerald-200">
                                            {{ $budget->period_label }}
                                        </span>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-500">Budgeted:</span>
                                            <span class="font-medium text-gray-700 dark:text-gray-300">{{ number_format($budget->amount, 2) }}</span>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-500">Spent:</span>
                                            <span class="font-medium text-red-600">{{ number_format($budget->spent, 2) }}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 flex justify-between items-center text-sm font-medium text-emerald-600 dark:text-emerald-400 group-hover:text-emerald-700">
                                        <span>View Analysis</span>
                                        <i class="fas fa-arrow-right"></i>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="col-span-3 text-center py-8 text-gray-500">
                                No budgets found. Create a budget to start receiving analysis.
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-6">
                        {{ $budgets->links() }}
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</x-app-layout>
