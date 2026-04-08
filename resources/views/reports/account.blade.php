<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Account Report') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Filter Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="GET" action="{{ route('reports.account') }}" class="flex flex-col md:flex-row gap-4 items-end">
                        <div class="w-full md:w-1/3">
                            <label for="date_from" class="block text-sm font-medium text-gray-700 dark:text-gray-300">From Date</label>
                            <input type="date" name="date_from" id="date_from" value="{{ $date_from }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                        <div class="w-full md:w-1/3">
                            <label for="date_to" class="block text-sm font-medium text-gray-700 dark:text-gray-300">To Date</label>
                            <input type="date" name="date_to" id="date_to" value="{{ $date_to }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                        <div class="w-full md:w-1/3">
                            <label for="account_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Account (Optional)</label>
                            <select name="account_id" id="account_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">All Accounts</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}" {{ $account_id == $account->id ? 'selected' : '' }}>{{ $account->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-full md:w-auto">
                            <button type="submit" class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Data Table Card -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100 overflow-x-auto">
                        <table class="w-full whitespace-no-wrap">
                            <thead>
                                <tr class="text-left font-bold border-b border-gray-200 dark:border-gray-700 pb-2">
                                    <th class="px-4 py-3">Account</th>
                                    <th class="px-4 py-3 text-right">Income</th>
                                    <th class="px-4 py-3 text-right">Expense</th>
                                    <th class="px-4 py-3 text-right">Transfers</th>
                                    <th class="px-4 py-3 text-right">Net Change</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($account_data as $data)
                                    <tr class="border-b border-gray-200 dark:border-gray-700">
                                        <td class="px-4 py-3 font-semibold">{{ $data['account']->name }}</td>
                                        <td class="px-4 py-3 text-right text-green-600">+{{ number_format($data['income'], 2) }}</td>
                                        <td class="px-4 py-3 text-right text-red-600">-{{ number_format($data['expense'], 2) }}</td>
                                        <td class="px-4 py-3 text-right text-blue-600">
                                            @if($data['transfer_in'] - $data['transfer_out'] > 0)
                                                +{{ number_format($data['transfer_in'] - $data['transfer_out'], 2) }}
                                            @else
                                                {{ number_format($data['transfer_in'] - $data['transfer_out'], 2) }}
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right font-bold {{ $data['net_change'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $data['net_change'] >= 0 ? '+' : '' }}{{ number_format($data['net_change'], 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-3 text-center text-gray-500">No data found for this period.</td>
                                    </tr>
                                @endforelse
                                <tr class="bg-gray-50 dark:bg-gray-700 font-bold">
                                    <td class="px-4 py-3">Total</td>
                                    <td class="px-4 py-3 text-right text-green-600">+{{ number_format($total_income, 2) }}</td>
                                    <td class="px-4 py-3 text-right text-red-600">-{{ number_format($total_expense, 2) }}</td>
                                    <td class="px-4 py-3 text-right">0.00</td>
                                    <td class="px-4 py-3 text-right {{ ($total_income - $total_expense) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ ($total_income - $total_expense) >= 0 ? '+' : '' }}{{ number_format($total_income - $total_expense, 2) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Chart Card -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100 flex items-center justify-center h-full min-h-[300px]">
                        @if(count($chart_data['labels']) > 0)
                            <canvas id="accountChart"></canvas>
                        @else
                            <p class="text-gray-500">No chart data available.</p>
                        @endif
                    </div>
                </div>
            </div>
            
        </div>
    </div>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if(count($chart_data['labels']) > 0)
            const ctx = document.getElementById('accountChart').getContext('2d');
            
            // Check if dark mode is active
            const isDarkMode = document.documentElement.classList.contains('dark') || 
                               window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            const textColor = isDarkMode ? '#e5e7eb' : '#374151';
            const gridColor = isDarkMode ? '#374151' : '#e5e7eb';
            
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($chart_data['labels']) !!},
                    datasets: [
                        {
                            label: 'Income',
                            data: {!! json_encode($chart_data['income']) !!},
                            backgroundColor: 'rgba(34, 197, 94, 0.7)',
                            borderColor: 'rgb(34, 197, 94)',
                            borderWidth: 1
                        },
                        {
                            label: 'Expense',
                            data: {!! json_encode($chart_data['expense']) !!},
                            backgroundColor: 'rgba(239, 68, 68, 0.7)',
                            borderColor: 'rgb(239, 68, 68)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            labels: { color: textColor }
                        },
                        title: {
                            display: true,
                            text: 'Income vs Expense by Account',
                            color: textColor
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { color: textColor },
                            grid: { color: gridColor }
                        },
                        x: {
                            ticks: { color: textColor },
                            grid: { color: gridColor }
                        }
                    }
                }
            });
            @endif
        });
    </script>
</x-app-layout>
