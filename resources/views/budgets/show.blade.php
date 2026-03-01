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

    {{-- Category Budget Allocations --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-bold text-gray-800">Category Budget Limits</h3>
            <button onclick="document.getElementById('categoryBudgetForm').classList.toggle('hidden')"
                    class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                <i class="fas fa-sliders-h text-xs"></i> {{ $budget->categoryBudgets->isNotEmpty() ? 'Edit Allocations' : 'Set Allocations' }}
            </button>
        </div>

        {{-- Existing Category Budgets Display --}}
        @if($budget->categoryBudgets->isNotEmpty())
            <div class="space-y-3 mb-4">
                @foreach($budget->categoryBudgets as $catBudget)
                    <div class="border border-gray-100 rounded-lg p-3">
                        <div class="flex items-center justify-between text-sm mb-1">
                            <span class="text-gray-700 font-medium">{{ $catBudget->category->name ?? 'Unknown' }}</span>
                            <div class="text-right">
                                <span class="{{ $catBudget->is_exceeded ? 'text-red-600' : 'text-gray-600' }}">
                                    ₹{{ number_format($catBudget->spent, 2) }}
                                </span>
                                <span class="text-gray-400">/</span>
                                <span class="text-gray-700 font-semibold">₹{{ number_format($catBudget->amount, 2) }}</span>
                            </div>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            @php $catPct = $catBudget->usage_percent; @endphp
                            <div class="h-2 rounded-full transition-all {{ $catPct > 100 ? 'bg-red-500' : ($catPct > 80 ? 'bg-amber-500' : 'bg-green-500') }}"
                                 style="width: {{ min($catPct, 100) }}%"></div>
                        </div>
                        <div class="flex items-center justify-between mt-1">
                            <span class="text-xs {{ $catBudget->remaining >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $catBudget->remaining >= 0 ? '₹'.number_format($catBudget->remaining, 2).' left' : '₹'.number_format(abs($catBudget->remaining), 2).' over' }}
                            </span>
                            <span class="text-xs {{ $catPct > 100 ? 'text-red-600' : ($catPct > 80 ? 'text-amber-600' : 'text-green-600') }} font-semibold">
                                {{ $catPct }}%
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-gray-400 mb-4">No per-category limits set yet. Click "Set Allocations" to assign spending limits per category.</p>
        @endif

        {{-- Category Budget Form (hidden by default) --}}
        <div id="categoryBudgetForm" class="{{ $budget->categoryBudgets->isEmpty() ? '' : 'hidden' }}">
            <form method="POST" action="{{ route('budgets.category-budgets.store', $budget) }}">
                @csrf

                <div id="categoryRows" class="space-y-3">
                    @if($budget->categoryBudgets->isNotEmpty())
                        @foreach($budget->categoryBudgets as $i => $catBudget)
                            <div class="flex items-center gap-3 category-row">
                                <select name="categories[{{ $i }}][category_id]" class="flex-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ $catBudget->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                <input type="number" name="categories[{{ $i }}][amount]" step="0.01" min="0.01"
                                       value="{{ $catBudget->amount }}" placeholder="Amount (₹)"
                                       class="w-40 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" required>
                                <button type="button" onclick="this.closest('.category-row').remove()" class="text-red-500 hover:text-red-700">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        @endforeach
                    @else
                        <div class="flex items-center gap-3 category-row">
                            <select name="categories[0][category_id]" class="flex-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            <input type="number" name="categories[0][amount]" step="0.01" min="0.01" placeholder="Amount (₹)"
                                   class="w-40 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" required>
                            <button type="button" onclick="this.closest('.category-row').remove()" class="text-red-500 hover:text-red-700">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    @endif
                </div>

                @if($errors->any())
                    <div class="mt-2">
                        @foreach($errors->all() as $error)
                            <p class="text-sm text-red-600">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <div class="flex items-center gap-3 mt-4">
                    <button type="button" onclick="addCategoryRow()" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                        <i class="fas fa-plus text-xs"></i> Add Category
                    </button>
                </div>

                <div class="flex items-center gap-4 mt-4 pt-4 border-t border-gray-100">
                    <x-primary-button>{{ __('Save Category Budgets') }}</x-primary-button>
                    <button type="button" onclick="document.getElementById('categoryBudgetForm').classList.add('hidden')" class="text-sm text-gray-600 hover:text-gray-900">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Category Spending Breakdown (actual spending) --}}
    @if($categoryBreakdown->isNotEmpty())
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <h3 class="text-base font-bold text-gray-800 mb-4">Actual Spending by Category</h3>
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

    @push('scripts')
    <script>
        let rowIndex = {{ $budget->categoryBudgets->count() ?: 1 }};

        function addCategoryRow() {
            const container = document.getElementById('categoryRows');
            const categoriesJson = @json($categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name]));

            let options = '<option value="">Select Category</option>';
            categoriesJson.forEach(c => {
                options += `<option value="${c.id}">${c.name}</option>`;
            });

            const row = document.createElement('div');
            row.className = 'flex items-center gap-3 category-row';
            row.innerHTML = `
                <select name="categories[${rowIndex}][category_id]" class="flex-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" required>
                    ${options}
                </select>
                <input type="number" name="categories[${rowIndex}][amount]" step="0.01" min="0.01" placeholder="Amount (₹)"
                       class="w-40 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" required>
                <button type="button" onclick="this.closest('.category-row').remove()" class="text-red-500 hover:text-red-700">
                    <i class="fas fa-times"></i>
                </button>
            `;
            container.appendChild(row);
            rowIndex++;
        }
    </script>
    @endpush
</x-app-layout>
