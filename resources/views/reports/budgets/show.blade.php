<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Budget Analysis: ') }} {{ $budget->name }}
            </h2>
            <a href="{{ route('reports.budgets.index') }}" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                &larr; Back to Budgets
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Overall Health Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4 text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700 pb-2">Overall Health</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 text-center">
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg shadow-inner">
                            <p class="text-sm text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total Budgeted</p>
                            <p class="text-3xl font-bold text-gray-800 dark:text-white mt-1">{{ number_format($analysis['budgeted'], 2) }}</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg shadow-inner">
                            <p class="text-sm text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total Spent</p>
                            <p class="text-3xl font-bold text-red-500 mt-1">{{ number_format($analysis['spent'], 2) }}</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg shadow-inner">
                            <p class="text-sm text-gray-500 dark:text-gray-400 uppercase tracking-wide">Variance (Remaining)</p>
                            <p class="text-3xl font-bold mt-1 {{ $analysis['variance'] >= 0 ? 'text-emerald-500' : 'text-red-500' }}">
                                {{ $analysis['variance'] >= 0 ? '+' : '' }}{{ number_format($analysis['variance'], 2) }}
                            </p>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    @php
                        $pct = $analysis['usage_percent'];
                        $bgColor = 'bg-emerald-500';
                        if($pct > 80 && $pct <= 100) $bgColor = 'bg-yellow-400';
                        if($pct > 100) $bgColor = 'bg-red-500';
                        $barWidth = min($pct, 100);
                    @endphp
                    <div>
                        <div class="flex justify-between text-sm font-medium mb-1">
                            <span>Usage: {{ $pct }}%</span>
                            <span class="{{ $analysis['variance'] < 0 ? 'text-red-500' : 'text-gray-500' }}">
                                {{ $analysis['variance'] < 0 ? 'Over Budget!' : 'On Track' }}
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4 relative overflow-hidden">
                            <div class="{{ $bgColor }} h-4 rounded-full transition-all duration-500" style="width: {{ $barWidth }}%"></div>
                            @if($pct > 100)
                                <div class="absolute inset-y-0 right-0 bg-red-600 opacity-50" style="width: 100%"></div>
                                <div class="{{ $bgColor }} h-4 rounded-full absolute top-0 left-0" style="width: 100%"></div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Category Breakdown & Trend -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Category Drill-down -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-semibold mb-6 flex items-center gap-2">
                            <i class="fas fa-list text-gray-400"></i> Category Breakdown
                        </h3>
                        
                        <div class="space-y-6">
                            @forelse($analysis['category_budgets'] as $cb)
                                @php
                                    $cpct = $cb['usage_percent'];
                                    $cbgColor = 'bg-emerald-500';
                                    if($cpct > 80 && $cpct <= 100) $cbgColor = 'bg-yellow-400';
                                    if($cpct > 100) $cbgColor = 'bg-red-500';
                                    $cbarWidth = min($cpct, 100);
                                @endphp
                                <div>
                                    <div class="flex justify-between items-end mb-1 text-sm">
                                        <div class="flex items-center">
                                            <div class="w-3 h-3 rounded-full mr-2" style="background-color: {{ $cb['category']->color ?? '#888' }}"></div>
                                            <span class="font-medium">{{ $cb['category']->name }}</span>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-red-500">{{ number_format($cb['spent'], 2) }}</span> / 
                                            <span class="text-gray-500 dark:text-gray-400">{{ number_format($cb['budgeted'], 2) }}</span>
                                        </div>
                                    </div>
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div class="{{ $cbgColor }} h-2 rounded-full" style="width: {{ $cbarWidth }}%"></div>
                                    </div>
                                    <div class="flex justify-between text-xs mt-1 text-gray-500">
                                        <span>{{ $cpct }}% used</span>
                                        <span>{{ $cb['variance'] >= 0 ? number_format($cb['variance'], 2) . ' left' : 'Over by ' . number_format(abs($cb['variance']), 2) }}</span>
                                    </div>
                                </div>
                            @empty
                                <p class="text-gray-500 text-sm">No specific category budgets have been set for this budget.</p>
                            @endforelse

                            @if($analysis['unallocated'] > 0)
                                <div class="pt-4 border-t border-gray-100 dark:border-gray-700">
                                    <div class="flex justify-between text-sm text-gray-500">
                                        <span>Unallocated Budget</span>
                                        <span>{{ number_format($analysis['unallocated'], 2) }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Trend Chart -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-semibold mb-6 flex items-center gap-2">
                            <i class="fas fa-chart-line text-gray-400"></i> Month-over-Month Trend
                        </h3>

                        @if(!$budget->is_custom && !empty($analysis['trend']))
                            <div class="mb-6">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Previous month spending vs this budget's spending:</p>
                                <div class="mt-2 text-2xl font-bold flex items-center gap-2">
                                    @php
                                        $diff = $analysis['trend']['difference'];
                                    @endphp
                                    <span class="{{ $diff > 0 ? 'text-red-500' : 'text-emerald-500' }}">
                                        {{ $diff > 0 ? '+' : '' }}{{ number_format($diff, 2) }}
                                    </span>
                                    <span class="text-sm font-normal text-gray-500">
                                        vs last month ({{ number_format($analysis['trend']['previous_spent'], 2) }})
                                    </span>
                                </div>
                            </div>

                            <div class="h-64 relative w-full">
                                <canvas id="trendChart"></canvas>
                            </div>
                        @else
                            <div class="h-full flex flex-col items-center justify-center text-gray-500 py-12">
                                <svg class="w-16 h-16 mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p>Trend data is only available for standard monthly budgets.</p>
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
            @if(!$budget->is_custom && !empty($analysis['trend']))
            const ctx = document.getElementById('trendChart').getContext('2d');
            
            // Check if dark mode is active
            const isDarkMode = document.documentElement.classList.contains('dark') || 
                               window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            const textColor = isDarkMode ? '#e5e7eb' : '#374151';
            const gridColor = isDarkMode ? '#374151' : '#e5e7eb';
            
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Previous Month', 'Current Budget Month'],
                    datasets: [
                        {
                            label: 'Total Spent',
                            data: [
                                {{ $analysis['trend']['previous_spent'] }},
                                {{ $analysis['trend']['current_spent'] }}
                            ],
                            backgroundColor: [
                                'rgba(156, 163, 175, 0.7)', // gray
                                'rgba(59, 130, 246, 0.7)'  // blue
                            ],
                            borderColor: [
                                'rgb(156, 163, 175)',
                                'rgb(59, 130, 246)'
                            ],
                            borderWidth: 1,
                            barPercentage: 0.5
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { color: textColor },
                            grid: { color: gridColor }
                        },
                        x: {
                            ticks: { color: textColor },
                            grid: { display: false }
                        }
                    }
                }
            });
            @endif
        });
    </script>
</x-app-layout>
