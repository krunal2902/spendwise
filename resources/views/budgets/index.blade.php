<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-gray-800">Budgets</h2>
    </x-slot>

    {{-- Month/Year Filter & Actions --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
        <form method="GET" action="{{ route('budgets.index') }}" class="flex items-center gap-3">
            <select name="month" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $currentMonth == $m ? 'selected' : '' }}>
                        {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                    </option>
                @endfor
            </select>
            <select name="year" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                @for($y = now()->year - 2; $y <= now()->year + 2; $y++)
                    <option value="{{ $y }}" {{ $currentYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded-lg text-sm font-medium hover:bg-gray-700 transition shadow-sm">
                <i class="fas fa-filter text-xs"></i> Filter
            </button>
        </form>

        <a href="{{ route('budgets.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition shadow-sm">
            <i class="fas fa-plus text-xs"></i> New Budget
        </a>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Budgeted</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">₹{{ number_format($summary['total_budgeted'], 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Spent</p>
            <p class="text-2xl font-bold text-red-600 mt-1">₹{{ number_format($summary['total_spent'], 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Remaining</p>
            <p class="text-2xl font-bold {{ $summary['remaining'] >= 0 ? 'text-green-600' : 'text-red-600' }} mt-1">
                ₹{{ number_format(abs($summary['remaining']), 2) }}
                @if($summary['remaining'] < 0) <span class="text-xs font-normal">(over)</span> @endif
            </p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Usage</p>
            <div class="mt-2">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-lg font-bold {{ $summary['usage_percent'] > 100 ? 'text-red-600' : ($summary['usage_percent'] > 80 ? 'text-amber-600' : 'text-green-600') }}">
                        {{ $summary['usage_percent'] }}%
                    </span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="h-2 rounded-full transition-all {{ $summary['usage_percent'] > 100 ? 'bg-red-500' : ($summary['usage_percent'] > 80 ? 'bg-amber-500' : 'bg-green-500') }}"
                         style="width: {{ min($summary['usage_percent'], 100) }}%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Budget List --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        @if($budgets->isEmpty())
            <div class="p-8 text-center">
                <i class="fas fa-piggy-bank text-4xl text-gray-300 mb-3"></i>
                <p class="text-gray-500">No budgets for {{ date('F', mktime(0, 0, 0, $currentMonth, 1)) }} {{ $currentYear }}.</p>
                <a href="{{ route('budgets.create') }}" class="inline-flex items-center gap-2 mt-3 text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                    <i class="fas fa-plus text-xs"></i> Create your first budget
                </a>
            </div>
        @else
            <div class="divide-y divide-gray-100">
                @foreach($budgets as $budget)
                    <div class="p-5 hover:bg-gray-50 transition">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <a href="{{ route('budgets.show', $budget) }}" class="text-base font-semibold text-gray-800 hover:text-indigo-600 transition">
                                    {{ $budget->name }}
                                </a>
                                @if($budget->notes)
                                    <p class="text-xs text-gray-400 mt-0.5">{{ Str::limit($budget->notes, 60) }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('budgets.edit', $budget) }}" class="text-indigo-600 hover:text-indigo-800 text-sm" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="{{ route('budgets.destroy', $budget) }}" onsubmit="return confirm('Delete this budget?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="flex items-center justify-between text-sm mb-2">
                            <span class="text-gray-500">
                                ₹{{ number_format($budget->spent, 2) }} / ₹{{ number_format($budget->amount, 2) }}
                            </span>
                            <span class="{{ $budget->usage_percent > 100 ? 'text-red-600' : ($budget->usage_percent > 80 ? 'text-amber-600' : 'text-green-600') }} font-semibold">
                                {{ $budget->usage_percent }}%
                            </span>
                        </div>

                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="h-2 rounded-full transition-all {{ $budget->usage_percent > 100 ? 'bg-red-500' : ($budget->usage_percent > 80 ? 'bg-amber-500' : 'bg-green-500') }}"
                                 style="width: {{ min($budget->usage_percent, 100) }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
