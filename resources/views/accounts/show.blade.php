<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('accounts.index') }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left"></i></a>
            <h2 class="text-xl font-bold text-gray-800">{{ $account->name }}</h2>
        </div>
    </x-slot>

    {{-- Account Details Card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <p class="text-sm text-gray-500">Account Type</p>
                <p class="text-lg font-medium capitalize">{{ $account->type }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Balance</p>
                <p class="text-2xl font-bold {{ $account->balance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    ₹{{ number_format($account->balance, 2) }}
                </p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Status</p>
                <span class="inline-block px-2 py-1 text-sm rounded-full {{ $account->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-500' }}">
                    {{ $account->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
            <div>
                <p class="text-sm text-gray-500">Created</p>
                <p class="text-sm text-gray-700">{{ $account->created_at->format('M d, Y') }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        {{-- Recent Income --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="p-5">
                <h3 class="text-sm font-semibold text-gray-800 mb-4"><i class="fas fa-arrow-down text-green-500 mr-2"></i>Recent Income</h3>
                @if($account->incomes->isEmpty())
                    <p class="text-gray-400 text-sm">No income recorded yet.</p>
                @else
                    <div class="space-y-3">
                        @foreach($account->incomes as $income)
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $income->description ?? $income->category->name ?? 'Income' }}</p>
                                    <p class="text-xs text-gray-500">{{ $income->income_date->format('M d, Y') }}</p>
                                </div>
                                <p class="text-sm font-bold text-green-600">+₹{{ number_format($income->amount, 2) }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Recent Expenses --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="p-5">
                <h3 class="text-sm font-semibold text-gray-800 mb-4"><i class="fas fa-arrow-up text-red-500 mr-2"></i>Recent Expenses</h3>
                @if($account->expenses->isEmpty())
                    <p class="text-gray-400 text-sm">No expenses recorded yet.</p>
                @else
                    <div class="space-y-3">
                        @foreach($account->expenses as $expense)
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $expense->description ?? $expense->category->name ?? 'Expense' }}</p>
                                    <p class="text-xs text-gray-500">{{ $expense->expense_date->format('M d, Y') }}</p>
                                </div>
                                <p class="text-sm font-bold text-red-600">-₹{{ number_format($expense->amount, 2) }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
