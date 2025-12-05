@extends('layouts.app')

@section('title', 'Accounts')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="py-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Accounts</h1>
            <a href="{{ route('accounts.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                Add Account
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($accounts as $account)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $account->name }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 capitalize">{{ $account->type }}</p>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('accounts.edit', $account->id) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-500">Edit</a>
                            <form method="POST" action="{{ route('accounts.destroy', $account->id) }}" class="inline" onsubmit="return confirm('Are you sure?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-500">Delete</button>
                            </form>
                        </div>
                    </div>
                    <div class="mb-4">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Balance</div>
                        <div class="text-2xl font-bold amount-display {{ $account->balance < 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }}">
                            {{ $account->currency }} {{ number_format($account->balance, 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="showReconcileModal({{ $account->id }}, '{{ $account->name }}', {{ $account->balance }})" class="flex-1 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 px-3 py-2 rounded text-sm">
                            Reconcile
                        </button>
                        @if($account->is_hidden)
                            <span class="text-xs text-gray-500 dark:text-gray-400">Hidden</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Reconcile Modal -->
<div id="reconcileModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-96">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Reconcile Account</h3>
        <form method="POST" id="reconcileForm">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Account</label>
                <input type="text" id="reconcileAccountName" readonly class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-white">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Current Balance</label>
                <input type="text" id="reconcileCurrentBalance" readonly class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-white">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Actual Balance</label>
                <input type="number" name="actual_balance" step="0.01" required class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-white">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                <textarea name="notes" rows="3" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-white"></textarea>
            </div>
            <div class="flex gap-4">
                <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md">Reconcile</button>
                <button type="button" onclick="document.getElementById('reconcileModal').classList.add('hidden')" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 rounded-md">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function showReconcileModal(accountId, accountName, currentBalance) {
    document.getElementById('reconcileForm').action = `/accounts/${accountId}/reconcile`;
    document.getElementById('reconcileAccountName').value = accountName;
    document.getElementById('reconcileCurrentBalance').value = 'Rp ' + currentBalance.toLocaleString('id-ID');
    document.getElementById('reconcileModal').classList.remove('hidden');
}
</script>
@endsection



