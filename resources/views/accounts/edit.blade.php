<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-gray-800">Edit Account: {{ $account->name }}</h2>
    </x-slot>

    <div class="max-w-2xl">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <form method="POST" action="{{ route('accounts.update', $account) }}">
                @csrf @method('PUT')

                <div class="mb-4">
                    <x-input-label for="name" :value="__('Account Name')" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $account->name)" required autofocus />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div class="mb-4">
                    <x-input-label for="type" :value="__('Account Type')" />
                    <select id="type" name="type" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                        <option value="bank" {{ old('type', $account->type) === 'bank' ? 'selected' : '' }}>Bank Account</option>
                        <option value="cash" {{ old('type', $account->type) === 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="digital" {{ old('type', $account->type) === 'digital' ? 'selected' : '' }}>Digital Wallet</option>
                    </select>
                    <x-input-error :messages="$errors->get('type')" class="mt-2" />
                </div>

                <div class="mb-4">
                    <x-input-label :value="__('Current Balance')" />
                    <p class="mt-1 text-lg font-semibold text-gray-900">₹{{ number_format($account->balance, 2) }}</p>
                    <p class="text-xs text-gray-500">Balance is updated via income, expense, and transfer transactions.</p>
                </div>

                <div class="mb-4">
                    <x-input-label for="color" :value="__('Color')" />
                    <input id="color" name="color" type="color" class="mt-1 h-10 w-20 border-gray-300 rounded-md" value="{{ old('color', $account->color ?? '#6366F1') }}" />
                    <x-input-error :messages="$errors->get('color')" class="mt-2" />
                </div>

                <div class="flex items-center gap-4 mt-6">
                    <x-primary-button>{{ __('Update Account') }}</x-primary-button>
                    <a href="{{ route('accounts.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
