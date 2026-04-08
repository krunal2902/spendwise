<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-gray-800">Add Group Expense</h2>
    </x-slot>

    <div class="max-w-4xl mx-auto">
        <form method="POST" action="{{ route('groups.expenses.store', $group) }}" enctype="multipart/form-data" id="expenseForm">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Left Column: Expense Details -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
                    <h3 class="font-semibold text-gray-800 mb-2 border-b pb-2">Expense Details</h3>
                    
                    <div>
                        <x-input-label for="description" :value="__('Description')" />
                        <x-text-input id="description" name="description" type="text" class="mt-1 block w-full" :value="old('description')" required placeholder="e.g., Dinner at Olive" />
                        <x-input-error :messages="$errors->get('description')" class="mt-1" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="amount" :value="__('Amount (₹)')" />
                            <x-text-input id="amount" name="amount" type="number" step="0.01" class="mt-1 block w-full" :value="old('amount')" required oninput="updateSplits()" />
                            <x-input-error :messages="$errors->get('amount')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="expense_date" :value="__('Date')" />
                            <x-text-input id="expense_date" name="expense_date" type="date" class="mt-1 block w-full" :value="old('expense_date', date('Y-m-d'))" required />
                            <x-input-error :messages="$errors->get('expense_date')" class="mt-1" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="paid_by" :value="__('Paid By')" />
                        <select id="paid_by" name="paid_by" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            @foreach($group->memberships as $membership)
                                <option value="{{ $membership->user_id }}" {{ old('paid_by', auth()->id()) == $membership->user_id ? 'selected' : '' }}>
                                    {{ $membership->user->name }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('paid_by')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="category_id" :value="__('Category (optional)')" />
                        <select id="category_id" name="category_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">No Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input-label for="notes" :value="__('Notes')" />
                        <textarea id="notes" name="notes" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <!-- Right Column: Splitting Logic -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4 flex flex-col">
                    <h3 class="font-semibold text-gray-800 mb-2 border-b pb-2">Split Rules</h3>
                    
                    <div>
                        <x-input-label for="split_type" :value="__('How to split?')" />
                        <select id="split_type" name="split_type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm mb-4" onchange="updateSplits()">
                            <option value="equal" {{ old('split_type') == 'equal' ? 'selected' : '' }}>Split Equally</option>
                            <option value="exact" {{ old('split_type') == 'exact' ? 'selected' : '' }}>Split by Exact Amounts</option>
                            <option value="percentage" {{ old('split_type') == 'percentage' ? 'selected' : '' }}>Split by Percentages</option>
                        </select>
                    </div>

                    <div id="splitContainer" class="flex-1 overflow-y-auto space-y-3">
                        @foreach($group->memberships as $index => $membership)
                            <div class="flex items-center justify-between p-2 rounded-lg bg-gray-50 border border-gray-200">
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" id="user_{{ $membership->user_id }}" value="{{ $membership->user_id }}" checked onchange="updateSplits()" class="split-checkbox rounded text-indigo-600">
                                    <label for="user_{{ $membership->user_id }}" class="text-sm font-medium text-gray-800">{{ $membership->user->name }}</label>
                                </div>
                                <div class="split-input-group hidden flex items-center gap-2">
                                    <!-- Inputs will be injected via JS based on the split type -->
                                </div>
                                <div class="split-display text-sm font-bold text-gray-700">₹0.00</div>
                            </div>
                        @endforeach
                    </div>

                    <div id="splitSummary" class="mt-4 pt-4 border-t text-sm font-semibold flex justify-between">
                        <span class="text-gray-600">Remaining to split:</span>
                        <span id="remainingAmount" class="text-green-600">₹0.00</span>
                    </div>

                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('groups.expenses.index', $group) }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition">Cancel</a>
                <button type="submit" id="submitBtn" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition shadow-sm font-medium">Add Group Expense</button>
            </div>
            
            <div id="hiddenInputs"></div>
        </form>
    </div>

    @push('scripts')
    <script>
        const members = @json($group->memberships->pluck('user.name', 'user_id'));
        
        function updateSplits() {
            const splitType = document.getElementById('split_type').value;
            const amount = parseFloat(document.getElementById('amount').value) || 0;
            const containers = document.querySelectorAll('#splitContainer > div');
            const hiddenInputsContainer = document.getElementById('hiddenInputs');
            hiddenInputsContainer.innerHTML = ''; // basic reset

            let activeUsers = [];
            containers.forEach(container => {
                const cb = container.querySelector('.split-checkbox');
                if (cb.checked) {
                    activeUsers.push(cb.value);
                }
            });

            const summaryEl = document.getElementById('splitSummary');
            
            if (activeUsers.length === 0) {
                document.getElementById('remainingAmount').textContent = `₹${amount.toFixed(2)}`;
                document.getElementById('remainingAmount').className = 'text-red-600';
                return;
            }

            let currentSum = 0;

            containers.forEach(container => {
                const userId = container.querySelector('.split-checkbox').value;
                const isActive = activeUsers.includes(userId);
                const inputGroup = container.querySelector('.split-input-group');
                const display = container.querySelector('.split-display');
                
                inputGroup.innerHTML = '';
                
                if (!isActive) {
                    display.textContent = '₹0.00';
                    inputGroup.classList.add('hidden');
                    return;
                }

                if (splitType === 'equal') {
                    inputGroup.classList.add('hidden');
                    const splitVal = amount / activeUsers.length;
                    display.textContent = '₹' + splitVal.toFixed(2);
                    
                    hiddenInputsContainer.innerHTML += `<input type="hidden" name="splits[]" value="${userId}">`;
                    currentSum += splitVal;
                } 
                else if (splitType === 'exact') {
                    inputGroup.classList.remove('hidden');
                    const inputId = `exact_${userId}`;
                    let existingVal = container.getAttribute('data-exact') || '';
                    inputGroup.innerHTML = `
                        <span class="text-xs text-gray-500">₹</span>
                        <input type="number" step="0.01" class="w-20 text-sm p-1 border rounded exact-input" id="${inputId}" value="${existingVal}" oninput="document.getElementById('user_${userId}').closest('div.border').setAttribute('data-exact', this.value); calcRemaining();">
                    `;
                    display.textContent = '';
                    
                    hiddenInputsContainer.innerHTML += `
                        <input type="hidden" name="splits[${userId}][user_id]" value="${userId}">
                        <input type="hidden" name="splits[${userId}][amount]" id="hidden_exact_${userId}" value="${existingVal}">
                    `;
                }
                else if (splitType === 'percentage') {
                    inputGroup.classList.remove('hidden');
                    const inputId = `pct_${userId}`;
                    let existingVal = container.getAttribute('data-pct') || '';
                    inputGroup.innerHTML = `
                        <input type="number" step="0.01" max="100" class="w-16 text-sm p-1 border rounded pct-input" id="${inputId}" value="${existingVal}" oninput="document.getElementById('user_${userId}').closest('div.border').setAttribute('data-pct', this.value); calcRemaining();">
                        <span class="text-xs text-gray-500">%</span>
                    `;
                    
                    let calcAmount = (parseFloat(existingVal) || 0) / 100 * amount;
                    display.textContent = '₹' + calcAmount.toFixed(2);
                    
                    hiddenInputsContainer.innerHTML += `
                        <input type="hidden" name="splits[${userId}][user_id]" value="${userId}">
                        <input type="hidden" name="splits[${userId}][percentage]" id="hidden_pct_${userId}" value="${existingVal}">
                    `;
                }
            });

            if (splitType === 'equal') {
                summaryEl.style.display = 'none';
            } else {
                summaryEl.style.display = 'flex';
                calcRemaining();
            }
        }

        function calcRemaining() {
            const splitType = document.getElementById('split_type').value;
            const amount = parseFloat(document.getElementById('amount').value) || 0;
            let sum = 0;

            if (splitType === 'exact') {
                document.querySelectorAll('.exact-input').forEach(input => {
                    let val = parseFloat(input.value) || 0;
                    sum += val;
                    let userId = input.id.replace('exact_', '');
                    let hidden = document.getElementById(`hidden_exact_${userId}`);
                    if (hidden) hidden.value = val;
                });
                
                let remaining = amount - sum;
                document.getElementById('remainingAmount').textContent = `₹${Math.abs(remaining).toFixed(2)} ${remaining < -0.01 ? '(Over)' : ''}`;
                document.getElementById('remainingAmount').className = Math.abs(remaining) <= 0.01 ? 'text-green-600' : 'text-red-600';
            } 
            else if (splitType === 'percentage') {
                document.querySelectorAll('.pct-input').forEach(input => {
                    let val = parseFloat(input.value) || 0;
                    sum += val;
                    let userId = input.id.replace('pct_', '');
                    let hidden = document.getElementById(`hidden_pct_${userId}`);
                    if (hidden) hidden.value = val;
                });
                
                let remaining = 100 - sum;
                document.getElementById('remainingAmount').textContent = `${Math.abs(remaining).toFixed(2)}% ${remaining < -0.01 ? '(Over)' : 'remaining'}`;
                document.getElementById('remainingAmount').className = Math.abs(remaining) <= 0.01 ? 'text-green-600' : 'text-red-600';
                
                // Update displays
                document.querySelectorAll('.pct-input').forEach(input => {
                    let val = parseFloat(input.value) || 0;
                    let calcAmount = (val / 100) * amount;
                    input.closest('.flex').querySelector('.split-display').textContent = '₹' + calcAmount.toFixed(2);
                });
            }
        }
        
        // Initial setup
        updateSplits();
        
        // Validation on submit
        document.getElementById('expenseForm').addEventListener('submit', function(e) {
            const splitType = document.getElementById('split_type').value;
            const amount = parseFloat(document.getElementById('amount').value) || 0;
            
            if (splitType === 'exact') {
                let sum = 0;
                document.querySelectorAll('.exact-input').forEach(i => sum += (parseFloat(i.value) || 0));
                if (Math.abs(amount - sum) > 0.01) {
                    e.preventDefault();
                    alert(`Exact amounts must sum up to the total amount (₹${amount}). Currently at ₹${sum.toFixed(2)}.`);
                }
            } else if (splitType === 'percentage') {
                let sum = 0;
                document.querySelectorAll('.pct-input').forEach(i => sum += (parseFloat(i.value) || 0));
                if (Math.abs(100 - sum) > 0.01) {
                    e.preventDefault();
                    alert(`Percentages must sum up to exactly 100%. Currently at ${sum.toFixed(2)}%.`);
                }
            }
        });
    </script>
    @endpush
</x-app-layout>
