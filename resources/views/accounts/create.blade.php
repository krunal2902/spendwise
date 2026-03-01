<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-gray-800">Create Account</h2>
    </x-slot>

    <div class="max-w-2xl">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <form method="POST" action="{{ route('accounts.store') }}">
                @csrf

                <div class="mb-4">
                    <x-input-label for="name" :value="__('Account Name')" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required autofocus placeholder="e.g., HDFC Savings, Cash Wallet" />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div class="mb-4">
                    <x-input-label for="type" :value="__('Account Type')" />
                    <select id="type" name="type" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                        <option value="">Select Type</option>
                        <option value="bank" {{ old('type') === 'bank' ? 'selected' : '' }}>Bank Account</option>
                        <option value="cash" {{ old('type') === 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="digital" {{ old('type') === 'digital' ? 'selected' : '' }}>Digital Wallet</option>
                    </select>
                    <x-input-error :messages="$errors->get('type')" class="mt-2" />
                </div>

                <div class="mb-4">
                    <x-input-label for="balance" :value="__('Opening Balance (₹)')" />
                    <x-text-input id="balance" name="balance" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('balance', '0.00')" required />
                    <x-input-error :messages="$errors->get('balance')" class="mt-2" />
                </div>

                <div class="mb-4">
                    <x-input-label for="color" :value="__('Color (optional)')" />
                    <input id="color" name="color" type="color" class="mt-1 h-10 w-20 border-gray-300 rounded-md" value="{{ old('color', '#6366F1') }}" />
                    <x-input-error :messages="$errors->get('color')" class="mt-2" />
                </div>

                <div class="flex items-center gap-4 mt-6">
                    <x-primary-button>{{ __('Create Account') }}</x-primary-button>
                    <a href="{{ route('accounts.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
