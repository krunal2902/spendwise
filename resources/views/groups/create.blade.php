<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-gray-800">Create Group</h2>
    </x-slot>

    <div class="max-w-2xl">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <form method="POST" action="{{ route('groups.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-4">
                    <x-input-label for="name" :value="__('Group Name')" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                                  :value="old('name')" required autofocus
                                  placeholder="e.g., Flatmates, Trip to Goa, Office Lunch" />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div class="mb-4">
                    <x-input-label for="description" :value="__('Description (optional)')" />
                    <textarea id="description" name="description" rows="3"
                              class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                              placeholder="What is this group for?">{{ old('description') }}</textarea>
                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                </div>

                <div class="mb-4">
                    <x-input-label for="currency" :value="__('Currency')" />
                    <select id="currency" name="currency"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="INR" {{ old('currency', 'INR') === 'INR' ? 'selected' : '' }}>₹ INR (Indian Rupee)</option>
                        <option value="USD" {{ old('currency') === 'USD' ? 'selected' : '' }}>$ USD (US Dollar)</option>
                        <option value="EUR" {{ old('currency') === 'EUR' ? 'selected' : '' }}>€ EUR (Euro)</option>
                        <option value="GBP" {{ old('currency') === 'GBP' ? 'selected' : '' }}>£ GBP (British Pound)</option>
                    </select>
                    <x-input-error :messages="$errors->get('currency')" class="mt-2" />
                </div>

                <div class="mb-4">
                    <x-input-label for="image" :value="__('Group Image (optional)')" />
                    <input id="image" name="image" type="file" accept="image/*"
                           class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                    <x-input-error :messages="$errors->get('image')" class="mt-2" />
                </div>

                <div class="flex items-center gap-4 mt-6">
                    <x-primary-button>{{ __('Create Group') }}</x-primary-button>
                    <a href="{{ route('groups.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
