<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Category Report') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Filter Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="GET" action="{{ route('reports.category') }}" class="flex flex-col md:flex-row gap-4 items-end">
                        <div class="w-full md:w-1/3">
                            <label for="date_from" class="block text-sm font-medium text-gray-700 dark:text-gray-300">From Date</label>
                            <input type="date" name="date_from" id="date_from" value="{{ $date_from }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                        <div class="w-full md:w-1/3">
                            <label for="date_to" class="block text-sm font-medium text-gray-700 dark:text-gray-300">To Date</label>
                            <input type="date" name="date_to" id="date_to" value="{{ $date_to }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                        <div class="w-full md:w-1/3">
                            <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category (Optional)</label>
                            <select name="category_id" id="category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">All Categories</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ $category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-full md:w-auto">
                            <button type="submit" class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Breakdown Table -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-semibold mb-4">Expense Breakdown</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full whitespace-no-wrap">
                                <thead>
                                    <tr class="text-left font-bold border-b border-gray-200 dark:border-gray-700 pb-2">
                                        <th class="px-4 py-3">Category</th>
                                        <th class="px-4 py-3 text-right">Amount</th>
                                        <th class="px-4 py-3 text-right">% of Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($category_data as $data)
                                        <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                            <td class="px-4 py-3 flex items-center">
                                                <div class="w-4 h-4 rounded-full mr-2" style="background-color: {{ $data->category->color ?? '#' . substr(md5($data->category->name), 0, 6) }}"></div>
                                                <span class="font-semibold">{{ $data->category->name }}</span>
                                            </td>
                                            <td class="px-4 py-3 text-right font-medium text-red-600">{{ number_format($data->total, 2) }}</td>
                                            <td class="px-4 py-3 text-right">
                                                {{ $total_expense > 0 ? round(($data->total / $total_expense) * 100, 1) : 0 }}%
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-4 py-3 text-center text-gray-500">No expense data found for this period.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr class="bg-gray-50 dark:bg-gray-700 font-bold border-t-2 border-gray-300 dark:border-gray-600">
                                        <td class="px-4 py-3">Total Expenses</td>
                                        <td class="px-4 py-3 text-right text-red-600">{{ number_format($total_expense, 2) }}</td>
                                        <td class="px-4 py-3 text-right">100%</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Chart Card -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100 flex flex-col items-center justify-center min-h-[400px]">
                        @if(count($chart_data['labels']) > 0)
                            <div class="w-full max-w-[400px]">
                                <canvas id="categoryChart"></canvas>
                            </div>
                        @else
                            <div class="flex flex-col items-center justify-center text-gray-500">
                                <svg class="w-16 h-16 mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path>
                                </svg>
                                <p>No chart data available.</p>
                            </div>
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
            const ctx = document.getElementById('categoryChart').getContext('2d');
            
            // Check if dark mode is active
            const isDarkMode = document.documentElement.classList.contains('dark') || 
                               window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            const textColor = isDarkMode ? '#e5e7eb' : '#374151';
            
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($chart_data['labels']) !!},
                    datasets: [{
                        data: {!! json_encode($chart_data['data']) !!},
                        backgroundColor: {!! json_encode($chart_data['colors']) !!},
                        borderWidth: 1,
                        borderColor: isDarkMode ? '#1f2937' : '#ffffff' // matches dark:bg-gray-800 or bg-white
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: { 
                                color: textColor,
                                usePointStyle: true,
                                padding: 20
                            }
                        },
                        title: {
                            display: true,
                            text: 'Expense Distribution',
                            color: textColor,
                            font: {
                                size: 16
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed !== null) {
                                        // Format as currency
                                        label += new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(context.parsed);
                                    }
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
            @endif
        });
    </script>
</x-app-layout>
