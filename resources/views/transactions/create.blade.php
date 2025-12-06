@extends('layouts.app')

@section('title', 'Add Transaction')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="py-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">Add Transaction</h1>

        <!-- Templates -->
        @if($templates->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Templates</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                @foreach($templates as $template)
                    <form method="POST" action="{{ route('templates.use', $template->id) }}" class="inline">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded text-sm">
                            {{ $template->name }}
                        </button>
                    </form>
                @endforeach
            </div>
        </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Transaction Form -->
            <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <form method="POST" action="{{ route('transactions.store') }}" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
                            <select name="type" id="transactionType" required class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-white">
                                <option value="income">Income</option>
                                <option value="expense">Expense</option>
                                <option value="transfer">Transfer</option>
                            </select>
                        </div>

                        <div id="singleAccountField">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Account</label>
                            <select name="account_id" id="account_id" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-white">
                                <option value="">Select account</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="transferAccountsField" style="display: none;">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">From Account</label>
                                    <select name="from_account_id" id="from_account_id" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-white">
                                        <option value="">Select account</option>
                                        @foreach($accounts as $account)
                                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">To Account</label>
                                    <select name="to_account_id" id="to_account_id" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-white">
                                        <option value="">Select account</option>
                                        @foreach($accounts as $account)
                                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category</label>
                            <select name="category_id" id="category_id" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-white">
                                <option value="">Select category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Amount</label>
                            <div class="flex gap-2">
                                <input type="number" name="amount" id="amount" step="0.01" min="0.01" required class="flex-1 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-white">
                                <button type="button" onclick="document.getElementById('calculatorModal').classList.remove('hidden')" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 rounded text-sm">
                                    Calculator
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                            <input type="text" name="description" required class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date</label>
                            <input type="date" name="date" value="{{ date('Y-m-d') }}" required class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                            <textarea name="notes" rows="3" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-white"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Receipt (Photo)</label>
                            <input type="file" name="receipt" accept="image/*" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-white">
                        </div>

                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="is_split" id="isSplit" class="rounded border-gray-300 dark:border-gray-700">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Split Transaction</span>
                            </label>
                        </div>

                        <div id="splitTransactions" style="display: none;">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Split Transactions</h3>
                            <div id="splitItems"></div>
                            <button type="button" onclick="addSplitItem()" class="mt-2 px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 rounded text-sm">
                                Add Split Item
                            </button>
                        </div>

                        <div class="flex gap-4">
                            <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md font-medium">
                                Save Transaction
                            </button>
                            <a href="{{ route('transactions.index') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 rounded-md font-medium">
                                Cancel
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Calculator Modal -->
            <div id="calculatorModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-80">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Calculator</h3>
                        <button onclick="document.getElementById('calculatorModal').classList.add('hidden')" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                            ✕
                        </button>
                    </div>
                    <input type="text" id="calculatorDisplay" readonly class="w-full mb-4 px-4 py-2 bg-gray-100 dark:bg-gray-700 rounded text-right text-2xl font-mono">
                    <div class="grid grid-cols-4 gap-2">
                        <button type="button" data-calc="clear" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded">C</button>
                        <button type="button" data-calc="backspace" class="px-4 py-2 bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-500 rounded">⌫</button>
                        <button type="button" data-calc="/" class="px-4 py-2 bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-500 rounded">/</button>
                        <button type="button" data-calc="*" class="px-4 py-2 bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-500 rounded">×</button>
                        <button type="button" data-calc="7" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 rounded">7</button>
                        <button type="button" data-calc="8" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 rounded">8</button>
                        <button type="button" data-calc="9" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 rounded">9</button>
                        <button type="button" data-calc="-" class="px-4 py-2 bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-500 rounded">-</button>
                        <button type="button" data-calc="4" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 rounded">4</button>
                        <button type="button" data-calc="5" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 rounded">5</button>
                        <button type="button" data-calc="6" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 rounded">6</button>
                        <button type="button" data-calc="+" class="px-4 py-2 bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-500 rounded">+</button>
                        <button type="button" data-calc="1" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 rounded">1</button>
                        <button type="button" data-calc="2" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 rounded">2</button>
                        <button type="button" data-calc="3" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 rounded">3</button>
                        <button type="button" data-calc="=" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded row-span-2">=</button>
                        <button type="button" data-calc="0" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 rounded col-span-2">0</button>
                        <button type="button" data-calc="." class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 rounded">.</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let splitItemCount = 0;
function addSplitItem() {
    splitItemCount++;
    const div = document.createElement('div');
    div.className = 'mb-4 p-4 border border-gray-300 dark:border-gray-600 rounded';
    div.innerHTML = `
        <div class="grid grid-cols-2 gap-4 mb-2">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category</label>
                <select name="split_transactions[${splitItemCount}][category_id]" required class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-white">
                    <option value="">Select category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Amount</label>
                <input type="number" name="split_transactions[${splitItemCount}][amount]" step="0.01" min="0.01" required class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-white">
            </div>
        </div>
        <div class="mb-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
            <input type="text" name="split_transactions[${splitItemCount}][description]" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-white">
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="text-red-600 dark:text-red-400 text-sm">Remove</button>
    `;
    document.getElementById('splitItems').appendChild(div);
}

document.getElementById('transactionType').addEventListener('change', function() {
    const type = this.value;
    if (type === 'transfer') {
        document.getElementById('singleAccountField').style.display = 'none';
        document.getElementById('transferAccountsField').style.display = 'block';
        document.getElementById('account_id').removeAttribute('required');
        document.getElementById('from_account_id').setAttribute('required', 'required');
        document.getElementById('to_account_id').setAttribute('required', 'required');
    } else {
        document.getElementById('singleAccountField').style.display = 'block';
        document.getElementById('transferAccountsField').style.display = 'none';
        document.getElementById('account_id').setAttribute('required', 'required');
        document.getElementById('from_account_id').removeAttribute('required');
        document.getElementById('to_account_id').removeAttribute('required');
    }
});

document.getElementById('isSplit').addEventListener('change', function() {
    document.getElementById('splitTransactions').style.display = this.checked ? 'block' : 'none';
});
</script>
@endsection





