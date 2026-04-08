<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Reports') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Account Report Card -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100 flex flex-col items-center">
                        <svg class="w-16 h-16 text-blue-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                        <h3 class="text-xl font-bold mb-2">Account Report</h3>
                        <p class="text-gray-500 text-center mb-6">Analyze your income, expenses, and net change per account.</p>
                        <a href="{{ route('reports.account') }}" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">View Report</a>
                    </div>
                </div>

                <!-- Category Report Card -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100 flex flex-col items-center">
                        <svg class="w-16 h-16 text-purple-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path>
                        </svg>
                        <h3 class="text-xl font-bold mb-2">Category Report</h3>
                        <p class="text-gray-500 text-center mb-6">Break down your expenses by category to see where your money goes.</p>
                        <a href="{{ route('reports.category') }}" class="px-6 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition">View Report</a>
                    </div>
                </div>

                <!-- Budget Analysis Card -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100 flex flex-col items-center">
                        <svg class="w-16 h-16 text-emerald-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <h3 class="text-xl font-bold mb-2">Budget Analysis</h3>
                        <p class="text-gray-500 text-center mb-6">Compare your actual spending against your planned budgets.</p>
                        <a href="{{ route('reports.budgets.index') }}" class="px-6 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 transition">View Analysis</a>
                    </div>
                </div>
                <!-- Monthly Statement Export Card -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100 flex flex-col items-center">
                        <svg class="w-16 h-16 text-red-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <h3 class="text-xl font-bold mb-2">Monthly Statement</h3>
                        <p class="text-gray-500 text-center mb-6">Generate and download a comprehensive PDF statement of all financial activity.</p>
                        <a href="{{ route('exports.statement.pdf') }}" class="px-6 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition flex items-center gap-2">
                            <i class="fas fa-file-pdf"></i> Download PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
