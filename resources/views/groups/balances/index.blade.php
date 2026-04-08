<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('groups.show', $group) }}" class="text-gray-400 hover:text-gray-600"><i class="fas fa-arrow-left"></i></a>
            <h2 class="text-xl font-bold text-gray-800">{{ $group->name }} - Balances & Settlements</h2>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left Column: Summaries & Balances --}}
        <div class="lg:col-span-2 space-y-6">
            
            {{-- Member Net Balances --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-semibold text-gray-800 mb-4 border-b pb-2">Member Balances</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($netBalances as $userId => $balance)
                        @php $user = $members[$userId] ?? null; @endphp
                        @if($user)
                            <div class="flex items-center justify-between p-3 rounded-lg border {{ $balance > 0 ? 'bg-green-50 border-green-100' : ($balance < 0 ? 'bg-red-50 border-red-100' : 'bg-gray-50 border-gray-100') }}">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br {{ $balance > 0 ? 'from-green-400 to-green-600' : ($balance < 0 ? 'from-red-400 to-red-600' : 'from-gray-400 to-gray-600') }} flex items-center justify-center text-white font-bold">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                                        <p class="text-xs text-gray-500">
                                            @if($balance > 0.01) Gets Back
                                            @elseif($balance < -0.01) Owes
                                            @else Settled Up
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-bold {{ $balance > 0 ? 'text-green-600' : ($balance < 0 ? 'text-red-600' : 'text-gray-500') }}">
                                        {{ $balance > 0 ? '+' : '' }}₹{{ number_format(abs($balance), 2) }}
                                    </p>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            {{-- Suggested Settlements (Simplify Debts) --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4 border-b pb-2">
                    <h3 class="font-semibold text-gray-800">Suggested Settlements</h3>
                    <span class="text-xs px-2 py-1 bg-indigo-100 text-indigo-800 rounded-full">Debt Simplified</span>
                </div>
                
                @if(empty($activeSuggested))
                    <div class="text-center py-6 text-gray-500 text-sm">
                        <i class="fas fa-check-circle text-green-500 text-3xl mb-2"></i>
                        <p>Everyone is settled up.</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($activeSuggested as $suggestion)
                            @if($suggestion['from_user'] && $suggestion['to_user'])
                                <div class="flex items-center justify-between p-3 rounded-lg border border-gray-100 shadow-sm">
                                    <div class="flex items-center gap-3">
                                        <span class="text-sm font-medium text-gray-800">{{ $suggestion['from_user']->name }}</span>
                                        <i class="fas fa-arrow-right text-gray-300"></i>
                                        <span class="text-sm font-medium text-gray-800">{{ $suggestion['to_user']->name }}</span>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <span class="font-bold text-gray-900">₹{{ number_format($suggestion['amount'], 2) }}</span>
                                        
                                        {{-- Quick Settle Button --}}
                                        <button type="button" 
                                                onclick="openSettleModal({{ $suggestion['from_user']->id }}, {{ $suggestion['to_user']->id }}, {{ $suggestion['amount'] }})"
                                                class="px-2 py-1 bg-indigo-50 text-indigo-700 text-xs rounded hover:bg-indigo-100 font-medium">
                                            Record Payment
                                        </button>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
            
            {{-- Settlement History --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-semibold text-gray-800 mb-4 border-b pb-2">Recent Payments</h3>
                
                @if($recentSettlements->isEmpty())
                    <p class="text-sm text-gray-500 py-4 text-center">No payments recorded yet.</p>
                @else
                    <div class="space-y-3">
                        @foreach($recentSettlements as $settlement)
                            <div class="flex items-center justify-between p-3 rounded-lg border border-gray-100">
                                <div>
                                    <p class="text-sm font-medium">
                                        <span class="text-gray-900">{{ $settlement->paidBy->name }}</span>
                                        <span class="text-gray-500 font-normal">paid</span>
                                        <span class="text-gray-900">{{ $settlement->paidTo->name }}</span>
                                    </p>
                                    <p class="text-xs text-gray-500">{{ $settlement->settled_at->format('M d, Y') }} • {{ $settlement->notes ?? 'No notes' }}</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="font-bold text-green-600">₹{{ number_format($settlement->amount, 2) }}</span>
                                    <form method="POST" action="{{ route('groups.settlements.destroy', [$group, $settlement]) }}" onsubmit="return confirm('Delete this record? It will reverse the balance adjustment.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-600"><i class="fas fa-trash text-xs"></i></button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>

        {{-- Right Column: Record Custom Settlement --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sticky top-6">
                <h3 class="font-semibold text-gray-800 mb-4 border-b pb-2">Record a Payment</h3>
                
                <form method="POST" action="{{ route('groups.settlements.store', $group) }}">
                    @csrf
                    
                    <div class="space-y-4">
                        <div>
                            <x-input-label for="paid_by" :value="__('Who Paid?')" />
                            <select id="paid_by" name="paid_by" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                                <option value="">Select person</option>
                                @foreach($members as $member)
                                    <option value="{{ $member->id }}">{{ $member->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('paid_by')" class="mt-1" />
                        </div>

                        <div>
                            <x-input-label for="paid_to" :value="__('Who Received?')" />
                            <select id="paid_to" name="paid_to" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                                <option value="">Select person</option>
                                @foreach($members as $member)
                                    <option value="{{ $member->id }}">{{ $member->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('paid_to')" class="mt-1" />
                        </div>

                        <div>
                            <x-input-label for="amount" :value="__('Amount (₹)')" />
                            <x-text-input id="amount" name="amount" type="number" step="0.01" class="mt-1 block w-full text-sm" required />
                            <x-input-error :messages="$errors->get('amount')" class="mt-1" />
                        </div>

                        <div>
                            <x-input-label for="settled_at" :value="__('Date')" />
                            <x-text-input id="settled_at" name="settled_at" type="date" class="mt-1 block w-full text-sm" value="{{ date('Y-m-d') }}" required />
                            <x-input-error :messages="$errors->get('settled_at')" class="mt-1" />
                        </div>

                        <div>
                            <x-input-label for="notes" :value="__('Notes (Optional)')" />
                            <x-text-input id="notes" name="notes" type="text" class="mt-1 block w-full text-sm" placeholder="e.g., Bank transfer" />
                            <x-input-error :messages="$errors->get('notes')" class="mt-1" />
                        </div>

                        <button type="submit" class="w-full py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition shadow-sm font-medium mt-2">
                            Record Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        function openSettleModal(fromUser, toUser, amount) {
            document.getElementById('paid_by').value = fromUser;
            document.getElementById('paid_to').value = toUser;
            document.getElementById('amount').value = amount;
            document.getElementById('notes').value = "Settled balance";
            
            // Nice highlight effect to show form was populated
            const formContainer = document.querySelector('form').parentElement;
            formContainer.classList.add('ring-2', 'ring-indigo-500', 'transition', 'scale-[1.02]');
            setTimeout(() => {
                formContainer.classList.remove('ring-2', 'ring-indigo-500', 'scale-[1.02]');
            }, 500);
        }
    </script>
    @endpush
</x-app-layout>
