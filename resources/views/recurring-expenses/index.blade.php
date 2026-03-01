<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-gray-800">Recurring Expenses</h2>
    </x-slot>

    <div class="flex items-center justify-between mb-5">
        <p class="text-sm text-gray-500">Manage your automatic, scheduled expenses</p>
        <a href="{{ route('recurring-expenses.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition shadow-sm">
            <i class="fas fa-plus text-xs"></i> New Recurring Expense
        </a>
    </div>

    @if($recurringExpenses->isEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center">
            <i class="fas fa-redo-alt text-4xl text-gray-300 mb-3"></i>
            <p class="text-gray-500">No recurring expenses set up yet.</p>
            <a href="{{ route('recurring-expenses.create') }}" class="inline-flex items-center gap-2 mt-3 text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                <i class="fas fa-plus text-xs"></i> Create your first recurring expense
            </a>
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 divide-y divide-gray-100">
            @foreach($recurringExpenses as $recurring)
                <div class="p-5 {{ !$recurring->is_active ? 'opacity-50' : '' }}">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="text-sm font-semibold text-gray-800">
                                    {{ $recurring->description ?? $recurring->category->name }}
                                </h3>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $recurring->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $recurring->is_active ? 'Active' : 'Paused' }}
                                </span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-700">
                                    {{ $recurring->frequency_label }}
                                </span>
                                @if($recurring->is_due)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-700">
                                        <i class="fas fa-clock text-xs mr-1"></i> Due
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-400">
                                {{ $recurring->category->name }} · {{ $recurring->account->name }}
                                · Next: {{ $recurring->next_due_date->format('M d, Y') }}
                                @if($recurring->last_processed_at)
                                    · Last: {{ $recurring->last_processed_at->format('M d, Y') }}
                                @endif
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-bold text-red-600">₹{{ number_format($recurring->amount, 2) }}</span>
                            <div class="flex items-center gap-1">
                                <form method="POST" action="{{ route('recurring-expenses.toggle', $recurring) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="text-sm {{ $recurring->is_active ? 'text-amber-600 hover:text-amber-800' : 'text-green-600 hover:text-green-800' }}" title="{{ $recurring->is_active ? 'Pause' : 'Activate' }}">
                                        <i class="fas {{ $recurring->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                                    </button>
                                </form>
                                <a href="{{ route('recurring-expenses.edit', $recurring) }}" class="text-indigo-600 hover:text-indigo-800 text-sm" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="{{ route('recurring-expenses.destroy', $recurring) }}" onsubmit="return confirm('Delete this recurring expense?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-app-layout>
