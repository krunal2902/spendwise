<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Alert Rules') }}
            </h2>
            <button onclick="document.getElementById('createRuleModal').classList.remove('hidden')" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 text-sm font-medium">
                + Create Rule
            </button>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <p class="text-sm text-gray-500 mb-6">Configure automated notifications to stay on top of your financial thresholds.</p>

                    @if($rules->isEmpty())
                        <div class="text-center py-8 text-gray-500">
                            No alert rules configured. Create one to get started.
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($rules as $rule)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-5 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                    <div class="flex items-start gap-4">
                                        <!-- Icon based on type -->
                                        <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 {{ $rule->is_active ? 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900 dark:text-indigo-400' : 'bg-gray-100 text-gray-400 dark:bg-gray-700' }}">
                                            @if($rule->type === 'budget_threshold')
                                                <i class="fas fa-chart-pie"></i>
                                            @elseif($rule->type === 'low_balance')
                                                <i class="fas fa-arrow-down"></i>
                                            @elseif($rule->type === 'large_expense')
                                                <i class="fas fa-exclamation-triangle"></i>
                                            @endif
                                        </div>
                                        
                                        <div>
                                            <h4 class="font-bold text-gray-800 dark:text-white capitalize">
                                                {{ str_replace('_', ' ', $rule->type) }}
                                            </h4>
                                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                                @if($rule->type === 'budget_threshold')
                                                    Alert me when a budget reaches <strong>{{ $rule->conditions['threshold_percent'] ?? 0 }}%</strong> of its limit.
                                                @elseif($rule->type === 'low_balance')
                                                    Alert me when an account drops below <strong>{{ number_format($rule->conditions['amount'] ?? 0, 2) }}</strong>.
                                                @elseif($rule->type === 'large_expense')
                                                    Alert me when a single expense exceeds <strong>{{ number_format($rule->conditions['amount'] ?? 0, 2) }}</strong>.
                                                @endif
                                            </p>
                                            @if($rule->last_triggered_at)
                                                <p class="text-xs text-gray-400 mt-2">Last triggered: {{ $rule->last_triggered_at->diffForHumans() }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center gap-3">
                                        <!-- Toggle Status -->
                                        <form action="{{ route('alert-rules.toggle', $rule) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-2xl {{ $rule->is_active ? 'text-emerald-500' : 'text-gray-300 dark:text-gray-600' }}">
                                                <i class="fas {{ $rule->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                                            </button>
                                        </form>

                                        <!-- Delete Rule -->
                                        <form action="{{ route('alert-rules.destroy', $rule) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this rule?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 p-2">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Create Rule Modal -->
    <div id="createRuleModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white dark:bg-gray-800 dark:border-gray-700">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Create Alert Rule</h3>
                    <button onclick="document.getElementById('createRuleModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form action="{{ route('alert-rules.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-4">
                        <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Alert Type</label>
                        <select name="type" id="type" required onchange="toggleConditionFields()" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">Select a rule type...</option>
                            <option value="budget_threshold">Budget Threshold %</option>
                            <option value="low_balance">Low Account Balance</option>
                            <option value="large_expense">Large Individual Expense</option>
                        </select>
                    </div>

                    <div id="threshold_field" class="hidden mb-4">
                        <label for="threshold_percent" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Percentage Threshold (%)</label>
                        <input type="number" name="conditions[threshold_percent]" id="threshold_percent" min="1" max="200" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="e.g., 80">
                        <p class="text-xs text-gray-500 mt-1">Alerts when budget spending reaches this percentage.</p>
                    </div>

                    <div id="amount_field" class="hidden mb-4">
                        <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Amount Threshold</label>
                        <input type="number" step="0.01" name="conditions[amount]" id="amount" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="e.g., 500">
                        <p class="text-xs text-gray-500 mt-1" id="amount_help_text"></p>
                    </div>

                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" onclick="document.getElementById('createRuleModal').classList.add('hidden')" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Save Rule</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function toggleConditionFields() {
            const type = document.getElementById('type').value;
            const thresholdField = document.getElementById('threshold_field');
            const amountField = document.getElementById('amount_field');
            const amountHelp = document.getElementById('amount_help_text');
            
            thresholdField.classList.add('hidden');
            amountField.classList.add('hidden');
            
            if (type === 'budget_threshold') {
                thresholdField.classList.remove('hidden');
                document.getElementById('threshold_percent').required = true;
                document.getElementById('amount').required = false;
            } else if (type === 'low_balance') {
                amountField.classList.remove('hidden');
                amountHelp.innerText = 'Alerts if an account balance drops below this amount.';
                document.getElementById('amount').required = true;
                document.getElementById('threshold_percent').required = false;
            } else if (type === 'large_expense') {
                amountField.classList.remove('hidden');
                amountHelp.innerText = 'Alerts when a single expense exceeds this amount.';
                document.getElementById('amount').required = true;
                document.getElementById('threshold_percent').required = false;
            }
        }
    </script>
</x-app-layout>
