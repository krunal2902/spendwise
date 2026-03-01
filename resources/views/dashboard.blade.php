<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-gray-800">Dashboard</h2>
    </x-slot>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
        <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-white/70 uppercase tracking-wider">Total Balance</p>
                    <p class="text-2xl font-bold mt-1">₹{{ number_format($totalBalance, 2) }}</p>
                    <p class="text-xs text-white/50 mt-1">{{ $accountCount }} account(s)</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-white/10 flex items-center justify-center">
                    <i class="fas fa-wallet text-xl"></i>
                </div>
            </div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-white/70 uppercase tracking-wider">Monthly Income</p>
                    <p class="text-2xl font-bold mt-1">+₹{{ number_format($monthlyIncome, 2) }}</p>
                    <p class="text-xs text-white/50 mt-1">{{ now()->format('F Y') }}</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-white/10 flex items-center justify-center">
                    <i class="fas fa-arrow-down text-xl"></i>
                </div>
            </div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-white/70 uppercase tracking-wider">Monthly Expenses</p>
                    <p class="text-2xl font-bold mt-1">-₹{{ number_format($monthlyExpense, 2) }}</p>
                    <p class="text-xs text-white/50 mt-1">{{ now()->format('F Y') }}</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-white/10 flex items-center justify-center">
                    <i class="fas fa-arrow-up text-xl"></i>
                </div>
            </div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-white/70 uppercase tracking-wider">Net Savings</p>
                    <p class="text-2xl font-bold mt-1">₹{{ number_format($monthlySavings, 2) }}</p>
                    <p class="text-xs text-white/50 mt-1">Income − Expenses</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-white/10 flex items-center justify-center">
                    <i class="fas fa-piggy-bank text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="flex flex-wrap gap-3 mb-6">
        <a href="{{ route('incomes.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition shadow-sm">
            <i class="fas fa-plus text-xs"></i> Add Income
        </a>
        <a href="{{ route('expenses.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition shadow-sm">
            <i class="fas fa-plus text-xs"></i> Add Expense
        </a>
        <a href="{{ route('transfers.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition shadow-sm">
            <i class="fas fa-exchange-alt text-xs"></i> Transfer
        </a>
        <a href="{{ route('accounts.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition shadow-sm border border-gray-200">
            <i class="fas fa-plus text-xs"></i> New Account
        </a>
    </div>

    {{-- Expense by Category --}}
    @if($expenseByCategory->isNotEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
            <h3 class="text-base font-semibold text-gray-800 mb-4">
                <i class="fas fa-chart-pie text-indigo-500 mr-2"></i>Expenses by Category — {{ now()->format('F') }}
            </h3>
            <div class="space-y-3">
                @php $maxExpense = $expenseByCategory->max('total'); @endphp
                @foreach($expenseByCategory as $cat)
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-sm font-medium text-gray-700">{{ $cat->category_name }}</span>
                            <span class="text-sm font-semibold text-gray-900">₹{{ number_format($cat->total, 2) }}</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2">
                            <div class="h-2 rounded-full transition-all" style="width: {{ ($cat->total / $maxExpense) * 100 }}%; background-color: {{ $cat->color ?? '#6366F1' }}"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Budget Overview & Upcoming Recurring --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">
        {{-- Budget Overview --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-base font-semibold text-gray-800">
                    <i class="fas fa-piggy-bank text-amber-500 mr-2"></i>Budget Overview
                </h3>
                <a href="{{ route('budgets.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">View All →</a>
            </div>
            @if($activeBudgets->isEmpty())
                <p class="text-sm text-gray-400">No budgets for this month.</p>
            @else
                <div class="space-y-3">
                    @foreach($activeBudgets as $budget)
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-sm font-medium text-gray-700">{{ $budget['name'] }}</span>
                                <span class="text-xs {{ $budget['is_exceeded'] ? 'text-red-600 font-bold' : 'text-gray-500' }}">
                                    ₹{{ number_format($budget['spent'], 2) }} / ₹{{ number_format($budget['amount'], 2) }}
                                </span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2">
                                @php
                                    $barColor = $budget['usage_percent'] > 100 ? '#EF4444' : ($budget['usage_percent'] > 80 ? '#F59E0B' : '#22C55E');
                                    $barWidth = min($budget['usage_percent'], 100);
                                @endphp
                                <div class="h-2 rounded-full transition-all" style="width: {{ $barWidth }}%; background-color: {{ $barColor }}"></div>
                            </div>
                            @if($budget['is_exceeded'])
                                <p class="text-xs text-red-500 mt-0.5"><i class="fas fa-exclamation-triangle text-xs"></i> Exceeded by ₹{{ number_format(abs($budget['remaining']), 2) }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Upcoming Recurring --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-base font-semibold text-gray-800">
                    <i class="fas fa-redo-alt text-purple-500 mr-2"></i>Upcoming Recurring
                </h3>
                <a href="{{ route('recurring-expenses.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">View All →</a>
            </div>
            @if($upcomingRecurring->isEmpty())
                <p class="text-sm text-gray-400">No upcoming recurring expenses in the next 7 days.</p>
            @else
                <div class="space-y-3">
                    @foreach($upcomingRecurring as $recurring)
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-sm font-medium text-gray-700">{{ $recurring->description ?? $recurring->category->name }}</p>
                                <p class="text-xs text-gray-400">{{ $recurring->next_due_date->format('M d') }} · {{ $recurring->account->name }} · {{ $recurring->frequency_label }}</p>
                            </div>
                            <span class="text-sm font-bold text-red-600">₹{{ number_format($recurring->amount, 2) }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Recent Transactions --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {{-- Recent Income --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="flex justify-between items-center p-5 pb-3">
                <h3 class="text-sm font-semibold text-gray-800"><i class="fas fa-arrow-down text-green-500 mr-2"></i>Recent Income</h3>
                <a href="{{ route('incomes.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">View All →</a>
            </div>
            <div class="px-5 pb-5 space-y-3">
                @forelse($recentIncome as $income)
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm font-medium text-gray-700">{{ $income->description ?? $income->category->name ?? 'Income' }}</p>
                            <p class="text-xs text-gray-400">{{ $income->income_date->format('M d') }} · {{ $income->account->name }}</p>
                        </div>
                        <span class="text-sm font-bold text-green-600">+₹{{ number_format($income->amount, 2) }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">No income recorded yet.</p>
                @endforelse
            </div>
        </div>

        {{-- Recent Expenses --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="flex justify-between items-center p-5 pb-3">
                <h3 class="text-sm font-semibold text-gray-800"><i class="fas fa-arrow-up text-red-500 mr-2"></i>Recent Expenses</h3>
                <a href="{{ route('expenses.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">View All →</a>
            </div>
            <div class="px-5 pb-5 space-y-3">
                @forelse($recentExpenses as $expense)
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm font-medium text-gray-700">{{ $expense->description ?? $expense->category->name ?? 'Expense' }}</p>
                            <p class="text-xs text-gray-400">{{ $expense->expense_date->format('M d') }} · {{ $expense->account->name }}</p>
                        </div>
                        <span class="text-sm font-bold text-red-600">-₹{{ number_format($expense->amount, 2) }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">No expenses recorded yet.</p>
                @endforelse
            </div>
        </div>

        {{-- Recent Transfers --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="flex justify-between items-center p-5 pb-3">
                <h3 class="text-sm font-semibold text-gray-800"><i class="fas fa-exchange-alt text-blue-500 mr-2"></i>Recent Transfers</h3>
                <a href="{{ route('transfers.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">View All →</a>
            </div>
            <div class="px-5 pb-5 space-y-3">
                @forelse($recentTransfers as $transfer)
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm font-medium text-gray-700">{{ $transfer->fromAccount->name }} → {{ $transfer->toAccount->name }}</p>
                            <p class="text-xs text-gray-400">{{ $transfer->transfer_date->format('M d') }}</p>
                        </div>
                        <span class="text-sm font-bold text-indigo-600">₹{{ number_format($transfer->amount, 2) }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">No transfers yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
