<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-gray-800">{{ $budget->name }}</h2>
    </x-slot>

    {{-- Back Link --}}
    <div class="mb-5">
        <a href="{{ route('budgets.index', ['month' => $budget->month, 'year' => $budget->year]) }}" class="text-sm text-indigo-600 hover:text-indigo-800">
            <i class="fas fa-arrow-left text-xs"></i> Back to Budgets
        </a>
    </div>

    {{-- Budget Overview Card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h3 class="text-lg font-bold text-gray-800">{{ $budget->name }}</h3>
                <p class="text-sm text-gray-500">{{ $budget->period_label }}</p>
                @if($budget->notes)
                    <p class="text-sm text-gray-400 mt-1">{{ $budget->notes }}</p>
                @endif
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('budgets.edit', $budget) }}" class="px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded-lg text-sm hover:bg-indigo-100 transition">
                    <i class="fas fa-edit text-xs"></i> Edit
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
            <div class="bg-gray-50 rounded-lg p-3">
                <p class="text-xs font-semibold text-gray-500 uppercase">Budgeted</p>
                <p class="text-xl font-bold text-gray-800">₹{{ number_format($budget->amount, 2) }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-3">
                <p class="text-xs font-semibold text-gray-500 uppercase">Spent</p>
                <p class="text-xl font-bold text-red-600">₹{{ number_format($budget->spent, 2) }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-3">
                <p class="text-xs font-semibold text-gray-500 uppercase">Remaining</p>
                <p class="text-xl font-bold {{ $budget->remaining >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    ₹{{ number_format(abs($budget->remaining), 2) }}
                    @if($budget->remaining < 0) <span class="text-xs font-normal">(over budget)</span> @endif
                </p>
            </div>
        </div>

        {{-- Progress Bar --}}
        <div class="mb-2">
            <div class="flex items-center justify-between text-sm mb-1">
                <span class="text-gray-500">Progress</span>
                <span class="{{ $budget->usage_percent > 100 ? 'text-red-600' : ($budget->usage_percent > 80 ? 'text-amber-600' : 'text-green-600') }} font-semibold">
                    {{ $budget->usage_percent }}%
                </span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3">
                <div class="h-3 rounded-full transition-all {{ $budget->usage_percent > 100 ? 'bg-red-500' : ($budget->usage_percent > 80 ? 'bg-amber-500' : 'bg-green-500') }}"
                     style="width: {{ min($budget->usage_percent, 100) }}%"></div>
            </div>
        </div>
    </div>

    {{-- Category Breakdown --}}
    @if($categoryBreakdown->isNotEmpty())
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <h3 class="text-base font-bold text-gray-800 mb-4">Spending by Category</h3>
        <div class="space-y-3">
            @foreach($categoryBreakdown as $item)
                @php
                    $pct = $budget->amount > 0 ? round(($item->total / $budget->amount) * 100, 1) : 0;
                @endphp
                <div>
                    <div class="flex items-center justify-between text-sm mb-1">
                        <span class="text-gray-700 font-medium">{{ $item->category->name ?? 'Uncategorized' }}</span>
                        <span class="text-gray-600">₹{{ number_format($item->total, 2) }} ({{ $pct }}%)</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="h-2 rounded-full bg-indigo-500" style="width: {{ min($pct, 100) }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Recent Expenses --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="p-5 border-b border-gray-100">
            <h3 class="text-base font-bold text-gray-800">Expenses in {{ $budget->period_label }}</h3>
        </div>

        @if($expenses->isEmpty())
            <div class="p-8 text-center">
                <p class="text-gray-500 text-sm">No expenses recorded for this month.</p>
            </div>
        @else
            <div class="divide-y divide-gray-100">
                @foreach($expenses as $expense)
                    <div class="px-5 py-3 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-800">{{ $expense->description ?? $expense->category->name }}</p>
                            <p class="text-xs text-gray-400">
                                {{ $expense->expense_date->format('M d, Y') }}
                                · {{ $expense->account->name }}
                                · {{ $expense->category->name }}
                            </p>
                        </div>
                        <span class="text-sm font-semibold text-red-600">-₹{{ number_format($expense->amount, 2) }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
