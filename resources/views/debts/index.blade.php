@extends('layouts.app')

@section('title', 'Debts & Receivables')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="py-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Debts & Receivables</h1>
            <a href="{{ route('debts.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                Add Debt/Receivable
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Payables -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Payables (Utang)</h2>
                <div class="space-y-4">
                    @foreach($debts->where('type', 'payable') as $debt)
                        <div class="p-4 border border-gray-200 dark:border-gray-700 rounded {{ $debt->isOverdue() ? 'border-red-500' : '' }}">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ $debt->contact_name }}</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $debt->description }}</p>
                                </div>
                                <form method="POST" action="{{ route('debts.destroy', $debt->id) }}" class="inline" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 dark:text-red-400 text-sm">Delete</button>
                                </form>
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                <div>Initial: Rp {{ number_format($debt->initial_amount, 0, ',', '.') }}</div>
                                <div>Remaining: Rp {{ number_format($debt->current_amount, 0, ',', '.') }}</div>
                                @if($debt->due_date)
                                    <div class="{{ $debt->isOverdue() ? 'text-red-600 dark:text-red-400 font-semibold' : '' }}">
                                        Due: {{ $debt->due_date->format('M d, Y') }}
                                    </div>
                                @endif
                            </div>
                            @if(!$debt->is_paid)
                                <button onclick="showPaymentModal({{ $debt->id }}, {{ $debt->current_amount }})" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded text-sm">
                                    Record Payment
                                </button>
                            @else
                                <span class="text-green-600 dark:text-green-400 text-sm font-semibold">Paid</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Receivables -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Receivables (Piutang)</h2>
                <div class="space-y-4">
                    @foreach($debts->where('type', 'receivable') as $debt)
                        <div class="p-4 border border-gray-200 dark:border-gray-700 rounded {{ $debt->isOverdue() ? 'border-red-500' : '' }}">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ $debt->contact_name }}</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $debt->description }}</p>
                                </div>
                                <form method="POST" action="{{ route('debts.destroy', $debt->id) }}" class="inline" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 dark:text-red-400 text-sm">Delete</button>
                                </form>
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                <div>Initial: Rp {{ number_format($debt->initial_amount, 0, ',', '.') }}</div>
                                <div>Remaining: Rp {{ number_format($debt->current_amount, 0, ',', '.') }}</div>
                                @if($debt->due_date)
                                    <div class="{{ $debt->isOverdue() ? 'text-red-600 dark:text-red-400 font-semibold' : '' }}">
                                        Due: {{ $debt->due_date->format('M d, Y') }}
                                    </div>
                                @endif
                            </div>
                            @if(!$debt->is_paid)
                                <button onclick="showPaymentModal({{ $debt->id }}, {{ $debt->current_amount }})" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded text-sm">
                                    Record Payment
                                </button>
                            @else
                                <span class="text-green-600 dark:text-green-400 text-sm font-semibold">Paid</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div id="paymentModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-96">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Record Payment</h3>
        <form method="POST" id="paymentForm">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Amount</label>
                <input type="number" name="amount" id="paymentAmount" step="0.01" min="0.01" required class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-white">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Payment Date</label>
                <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-white">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                <textarea name="notes" rows="3" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-white"></textarea>
            </div>
            <div class="mb-4">
                <label class="flex items-center">
                    <input type="checkbox" name="create_transaction" value="1" class="rounded border-gray-300 dark:border-gray-700">
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Create transaction</span>
                </label>
            </div>
            <div id="transactionAccountField" style="display: none;" class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Account</label>
                <select name="account_id" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-white">
                    <option value="">Select account</option>
                    @foreach(\App\Models\Account::where('user_id', auth()->id())->where('is_active', true)->get() as $account)
                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-4">
                <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md">Record</button>
                <button type="button" onclick="document.getElementById('paymentModal').classList.add('hidden')" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 rounded-md">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function showPaymentModal(debtId, maxAmount) {
    document.getElementById('paymentForm').action = `/debts/${debtId}/payment`;
    document.getElementById('paymentAmount').max = maxAmount;
    document.getElementById('paymentAmount').value = maxAmount;
    document.getElementById('paymentModal').classList.remove('hidden');
}

document.querySelector('[name="create_transaction"]').addEventListener('change', function() {
    document.getElementById('transactionAccountField').style.display = this.checked ? 'block' : 'none';
});
</script>
@endsection



