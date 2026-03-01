<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-gray-800">Add Expense</h2>
    </x-slot>

    <div class="max-w-2xl">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <form method="POST" action="{{ route('expenses.store') }}">
                @csrf

                <div class="mb-4">
                    <x-input-label for="account_id" :value="__('Account')" />
                    <select id="account_id" name="account_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                        <option value="">Select Account</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}" {{ old('account_id') == $account->id ? 'selected' : '' }}>
                                {{ $account->name }} (₹{{ number_format($account->balance, 2) }})
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('account_id')" class="mt-2" />
                </div>

                <div class="mb-4">
                    <x-input-label for="category_id" :value="__('Category')" />
                    <select id="category_id" name="category_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                </div>

                <div class="mb-4">
                    <x-input-label for="amount" :value="__('Amount (₹)')" />
                    <x-text-input id="amount" name="amount" type="number" step="0.01" min="0.01" class="mt-1 block w-full" :value="old('amount')" required />
                    <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                </div>

                <div class="mb-4">
                    <x-input-label for="expense_date" :value="__('Date')" />
                    <x-text-input id="expense_date" name="expense_date" type="date" class="mt-1 block w-full" :value="old('expense_date', date('Y-m-d'))" required />
                    <x-input-error :messages="$errors->get('expense_date')" class="mt-2" />
                </div>

                <div class="mb-4">
                    <x-input-label for="description" :value="__('Description (optional)')" />
                    <x-text-input id="description" name="description" type="text" class="mt-1 block w-full" :value="old('description')" placeholder="e.g., Lunch at cafe" />
                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                </div>

                {{-- Tags --}}
                <div class="mb-4">
                    <x-input-label :value="__('Tags (optional)')" />
                    <div id="tagContainer" class="flex flex-wrap gap-2 mt-1 mb-2">
                        @if(old('tags'))
                            @foreach(old('tags') as $tag)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-indigo-100 text-indigo-700 text-xs font-medium">
                                    {{ $tag }}
                                    <input type="hidden" name="tags[]" value="{{ $tag }}">
                                    <button type="button" onclick="this.parentElement.remove()" class="text-indigo-400 hover:text-indigo-600">&times;</button>
                                </span>
                            @endforeach
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="text" id="tagInput" list="tagSuggestions" placeholder="Type a tag and press Enter"
                               class="flex-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                        <datalist id="tagSuggestions">
                            @foreach($userTags as $tag)
                                <option value="{{ $tag->name }}">
                            @endforeach
                        </datalist>
                        <button type="button" onclick="addTag()" class="px-3 py-2 bg-indigo-50 text-indigo-600 rounded-md text-sm hover:bg-indigo-100">Add</button>
                    </div>
                    <x-input-error :messages="$errors->get('tags')" class="mt-2" />
                </div>

                <div class="mb-4">
                    <x-input-label for="notes" :value="__('Notes (optional)')" />
                    <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="Any additional notes...">{{ old('notes') }}</textarea>
                    <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                </div>

                <div class="mb-4">
                    <x-input-label for="reference" :value="__('Reference (optional)')" />
                    <x-text-input id="reference" name="reference" type="text" class="mt-1 block w-full" :value="old('reference')" placeholder="e.g., Receipt #123" />
                    <x-input-error :messages="$errors->get('reference')" class="mt-2" />
                </div>

                <div class="flex items-center gap-4 mt-6">
                    <x-primary-button>{{ __('Record Expense') }}</x-primary-button>
                    <a href="{{ route('expenses.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function addTag() {
            const input = document.getElementById('tagInput');
            const name = input.value.trim().toLowerCase();
            if (!name) return;

            const container = document.getElementById('tagContainer');
            // Check for duplicates
            const existing = container.querySelectorAll('input[name="tags[]"]');
            for (const el of existing) {
                if (el.value.toLowerCase() === name) { input.value = ''; return; }
            }

            const span = document.createElement('span');
            span.className = 'inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-indigo-100 text-indigo-700 text-xs font-medium';
            span.innerHTML = `${name}<input type="hidden" name="tags[]" value="${name}"><button type="button" onclick="this.parentElement.remove()" class="text-indigo-400 hover:text-indigo-600">&times;</button>`;
            container.appendChild(span);
            input.value = '';
        }

        document.getElementById('tagInput').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') { e.preventDefault(); addTag(); }
        });
    </script>
    @endpush
</x-app-layout>
