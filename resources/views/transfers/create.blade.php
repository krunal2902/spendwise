<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-gray-800">New Transfer</h2>
    </x-slot>

    <div class="max-w-2xl">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <form method="POST" action="{{ route('transfers.store') }}">
                @csrf

                <div class="mb-4">
                    <x-input-label for="from_account_id" :value="__('From Account')" />
                    <select id="from_account_id" name="from_account_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                        <option value="">Select Source Account</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}" {{ old('from_account_id') == $account->id ? 'selected' : '' }}>
                                {{ $account->name }} (₹{{ number_format($account->balance, 2) }})
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('from_account_id')" class="mt-2" />
                </div>

                <div class="mb-4">
                    <x-input-label for="to_account_id" :value="__('To Account')" />
                    <select id="to_account_id" name="to_account_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                        <option value="">Select Destination Account</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}" {{ old('to_account_id') == $account->id ? 'selected' : '' }}>
                                {{ $account->name }} (₹{{ number_format($account->balance, 2) }})
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('to_account_id')" class="mt-2" />
                </div>

                <div class="mb-4">
                    <x-input-label for="amount" :value="__('Amount (₹)')" />
                    <x-text-input id="amount" name="amount" type="number" step="0.01" min="0.01" class="mt-1 block w-full" :value="old('amount')" required />
                    <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                </div>

                <div class="mb-4">
                    <x-input-label for="transfer_date" :value="__('Date')" />
                    <x-text-input id="transfer_date" name="transfer_date" type="date" class="mt-1 block w-full" :value="old('transfer_date', date('Y-m-d'))" required />
                    <x-input-error :messages="$errors->get('transfer_date')" class="mt-2" />
                </div>

                <div class="mb-4">
                    <x-input-label for="description" :value="__('Description (optional)')" />
                    <x-text-input id="description" name="description" type="text" class="mt-1 block w-full" :value="old('description')" placeholder="e.g., Move funds to savings" />
                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                </div>

                <div class="mb-4">
                    <x-input-label for="reference" :value="__('Reference (optional)')" />
                    <x-text-input id="reference" name="reference" type="text" class="mt-1 block w-full" :value="old('reference')" />
                    <x-input-error :messages="$errors->get('reference')" class="mt-2" />
                </div>

                <div class="flex items-center gap-4 mt-6">
                    <x-primary-button>{{ __('Transfer') }}</x-primary-button>
                    <a href="{{ route('transfers.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
