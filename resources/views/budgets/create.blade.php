<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-gray-800">Create Budget</h2>
    </x-slot>

    <div class="max-w-2xl">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <form method="POST" action="{{ route('budgets.store') }}">
                @csrf

                <div class="mb-4">
                    <x-input-label for="name" :value="__('Budget Name')" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" placeholder="e.g., Monthly Household, Travel Fund" required />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div class="mb-4">
                    <x-input-label for="amount" :value="__('Budget Amount (₹)')" />
                    <x-text-input id="amount" name="amount" type="number" step="0.01" min="0.01" class="mt-1 block w-full" :value="old('amount')" required />
                    <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                </div>

                {{-- Budget Type Toggle --}}
                <div class="mb-4">
                    <x-input-label :value="__('Budget Type')" />
                    <div class="flex items-center gap-4 mt-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="type" value="monthly" {{ old('type', 'monthly') === 'monthly' ? 'checked' : '' }}
                                   onchange="toggleBudgetType()" class="text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-gray-700">Monthly</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="type" value="custom" {{ old('type') === 'custom' ? 'checked' : '' }}
                                   onchange="toggleBudgetType()" class="text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-gray-700">Custom Date Range</span>
                        </label>
                    </div>
                </div>

                {{-- Monthly Fields --}}
                <div id="monthlyFields" class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <x-input-label for="month" :value="__('Month')" />
                        <select id="month" name="month" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ old('month', $currentMonth) == $m ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                </option>
                            @endfor
                        </select>
                        <x-input-error :messages="$errors->get('month')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="year" :value="__('Year')" />
                        <select id="year" name="year" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            @for($y = now()->year - 2; $y <= now()->year + 2; $y++)
                                <option value="{{ $y }}" {{ old('year', $currentYear) == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                        <x-input-error :messages="$errors->get('year')" class="mt-2" />
                    </div>
                </div>

                {{-- Custom Date Range Fields --}}
                <div id="customFields" class="grid grid-cols-2 gap-4 mb-4 hidden">
                    <div>
                        <x-input-label for="start_date" :value="__('Start Date')" />
                        <x-text-input id="start_date" name="start_date" type="date" class="mt-1 block w-full" :value="old('start_date')" />
                        <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="end_date" :value="__('End Date')" />
                        <x-text-input id="end_date" name="end_date" type="date" class="mt-1 block w-full" :value="old('end_date')" />
                        <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
                    </div>
                </div>

                {{-- Carry Forward --}}
                <div class="mb-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="carry_forward" value="0">
                        <input type="checkbox" name="carry_forward" value="1" {{ old('carry_forward') ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <span class="text-sm text-gray-700">Carry forward unspent amount to next period</span>
                    </label>
                    <p class="text-xs text-gray-400 mt-1 ml-6">Remaining balance will be added to the next month's budget automatically.</p>
                </div>

                <div class="mb-4">
                    <x-input-label for="notes" :value="__('Notes (optional)')" />
                    <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="Any details about this budget...">{{ old('notes') }}</textarea>
                    <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                </div>

                <div class="flex items-center gap-4 mt-6">
                    <x-primary-button>{{ __('Create Budget') }}</x-primary-button>
                    <a href="{{ route('budgets.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function toggleBudgetType() {
            const type = document.querySelector('input[name="type"]:checked').value;
            document.getElementById('monthlyFields').classList.toggle('hidden', type === 'custom');
            document.getElementById('customFields').classList.toggle('hidden', type === 'monthly');
        }
        // Init on page load
        document.addEventListener('DOMContentLoaded', toggleBudgetType);
    </script>
    @endpush
</x-app-layout>
