<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Financial Health Score') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            <!-- Global Score Gauge & Top Level -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-8 text-center flex flex-col md:flex-row items-center justify-center gap-12">
                    <!-- The Gauge -->
                    <div class="relative w-48 h-48 flex-shrink-0">
                        @php
                            $score = $healthData['score'];
                            // Determine Color
                            $colorHex = '#10B981'; // emerald-500
                            $textColor = 'text-emerald-500';
                            $message = 'Excellent Shape';
                            if ($score < 50) {
                                $colorHex = '#EF4444'; // red-500
                                $textColor = 'text-red-500';
                                $message = 'Needs Attention';
                            } elseif ($score < 75) {
                                $colorHex = '#F59E0B'; // amber-500
                                $textColor = 'text-amber-500';
                                $message = 'Doing Okay';
                            }
                        @endphp
                        
                        <svg viewBox="0 0 36 36" class="w-full h-full block">
                            <!-- Background Circle -->
                            <path class="text-gray-200 dark:text-gray-700" stroke-dasharray="100, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="currentColor" stroke-width="3"></path>
                            <!-- Progress Circle -->
                            <path stroke="{{ $colorHex }}" stroke-dasharray="{{ $score }}, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke-width="3" class="transition-all duration-1000 ease-out"></path>
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-5xl font-extrabold text-gray-800 dark:text-white">{{ $score }}</span>
                            <span class="text-sm font-medium text-gray-500 uppercase tracking-widest mt-1">/ 100</span>
                        </div>
                    </div>
                    
                    <!-- Top Level Text Message -->
                    <div class="text-left max-w-lg">
                        <h3 class="text-3xl font-bold {{ $textColor }} mb-2">{{ $message }}</h3>
                        <p class="text-gray-600 dark:text-gray-400 text-lg">Your SpendWise health engine has analyzed your habits, budgeting capabilities, account statuses, and variance trends to generate this score.</p>
                        
                        @if ($score == 100)
                            <div class="mt-4 inline-block bg-teal-100 text-teal-800 px-4 py-2 rounded-lg text-sm font-semibold border border-teal-200 dark:bg-teal-900 dark:text-teal-200 dark:border-teal-700">
                                🏆 Flawless Finances! Keep it up.
                            </div>
                        @else
                            <div class="mt-4 text-sm text-gray-500">
                                Review the breakdown below to see exactly how you can improve your score.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Breakdown Column -->
                <div class="lg:col-span-2 space-y-4">
                    <h3 class="text-xl font-bold text-gray-800 dark:text-gray-200 mb-4 px-1">Score Breakdown</h3>
                    
                    @foreach($healthData['breakdown'] as $key => $metric)
                        @php
                            $metricPct = $metric['max'] > 0 ? ($metric['score'] / $metric['max']) * 100 : 0;
                            $mColor = 'bg-emerald-500';
                            $tColor = 'text-emerald-600 dark:text-emerald-400';
                            if ($metricPct < 50) {
                                $mColor = 'bg-red-500';
                                $tColor = 'text-red-500';
                            } elseif ($metricPct < 80) {
                                $mColor = 'bg-amber-500';
                                $tColor = 'text-amber-500';
                            }
                        @endphp
                        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-5 border border-gray-100 dark:border-gray-700 hover:shadow-md transition">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <h4 class="font-bold text-gray-800 dark:text-white">{{ $metric['label'] }}</h4>
                                    <p class="text-sm text-gray-500 mt-1">{{ $metric['description'] }}</p>
                                </div>
                                <div class="text-right flex-shrink-0 ml-4">
                                    <span class="text-2xl font-black {{ $tColor }}">{{ $metric['score'] }}</span>
                                    <span class="text-gray-400 text-sm">/ {{ $metric['max'] }}</span>
                                </div>
                            </div>
                            
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5 mt-4">
                                <div class="{{ $mColor }} h-1.5 rounded-full transition-all duration-700" style="width: {{ $metricPct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Recommendations Column -->
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 border border-gray-100 dark:border-gray-700 sticky top-6">
                        <div class="flex items-center gap-3 mb-6 border-b border-gray-200 dark:border-gray-700 pb-4">
                            <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                                <i class="fas fa-lightbulb"></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-800 dark:text-gray-200">Recommendations</h3>
                        </div>
                        
                        @if(empty($healthData['recommendations']))
                            <div class="text-center py-6 text-gray-500">
                                <p>You have no actionable recommendations. Your finances are perfectly optimized!</p>
                            </div>
                        @else
                            <ul class="space-y-4">
                                @foreach($healthData['recommendations'] as $rec)
                                    <li class="flex items-start gap-3">
                                        <i class="fas fa-check-circle text-emerald-500 mt-1"></i>
                                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ $rec }}</p>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                        
                        <div class="mt-8 bg-slate-50 dark:bg-slate-900 p-4 rounded border border-slate-200 dark:border-slate-800">
                            <p class="text-xs text-slate-500 dark:text-slate-400 text-center">
                                <i class="fas fa-info-circle mr-1"></i> Scores are recalculated dynamically based on your current month's transactions, active budgets, and historical averages.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</x-app-layout>
