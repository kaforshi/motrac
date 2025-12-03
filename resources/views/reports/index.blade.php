@extends('layouts.app')

@section('title', 'Reports')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="py-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">Reports & Analytics</h1>

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
            <form method="GET" action="{{ route('reports.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">From Date</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">To Date</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
                    <select name="type" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-white">
                        <option value="all" {{ $type === 'all' ? 'selected' : '' }}>All</option>
                        <option value="income" {{ $type === 'income' ? 'selected' : '' }}>Income</option>
                        <option value="expense" {{ $type === 'expense' ? 'selected' : '' }}>Expense</option>
                        <option value="transfer" {{ $type === 'transfer' ? 'selected' : '' }}>Transfer</option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md">Filter</button>
                    <a href="{{ route('reports.index') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 rounded-md">Reset</a>
                </div>
            </form>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Income</div>
                <div class="mt-2 text-2xl font-semibold text-green-600 dark:text-green-400 amount-display">
                    Rp {{ number_format($totalIncome, 0, ',', '.') }}
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Expense</div>
                <div class="mt-2 text-2xl font-semibold text-red-600 dark:text-red-400 amount-display">
                    Rp {{ number_format($totalExpense, 0, ',', '.') }}
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Transfer</div>
                <div class="mt-2 text-2xl font-semibold text-blue-600 dark:text-blue-400 amount-display">
                    Rp {{ number_format($totalTransfer, 0, ',', '.') }}
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Net Amount</div>
                <div class="mt-2 text-2xl font-semibold amount-display {{ $netAmount >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                    Rp {{ number_format($netAmount, 0, ',', '.') }}
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Trend Chart -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Trend Analysis</h2>
                <canvas id="trendChart" width="400" height="200"></canvas>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Actions</h2>
                <div class="space-y-3">
                    <a href="{{ route('reports.export') }}?{{ http_build_query(request()->all()) }}" class="block w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-center">
                        Export to CSV
                    </a>
                    <a href="{{ route('reports.exportPdf') }}?{{ http_build_query(request()->all()) }}" class="block w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded text-center">
                        Export to PDF
                    </a>
                </div>
            </div>
        </div>

        <!-- Expense by Category -->
        @if($expenseByCategory->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Expense by Category</h2>
            <div class="space-y-3">
                @foreach($expenseByCategory as $item)
                    <div class="flex justify-between items-center">
                        <div>
                            <div class="font-medium text-gray-900 dark:text-white">{{ $item['category'] }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $item['count'] }} transactions</div>
                        </div>
                        <div class="text-lg font-semibold text-red-600 dark:text-red-400 amount-display">
                            Rp {{ number_format($item['total'], 0, ',', '.') }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Income by Category -->
        @if($incomeByCategory->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Income by Category</h2>
            <div class="space-y-3">
                @foreach($incomeByCategory as $item)
                    <div class="flex justify-between items-center">
                        <div>
                            <div class="font-medium text-gray-900 dark:text-white">{{ $item['category'] }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $item['count'] }} transactions</div>
                        </div>
                        <div class="text-lg font-semibold text-green-600 dark:text-green-400 amount-display">
                            Rp {{ number_format($item['total'], 0, ',', '.') }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Detailed Transactions -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Detailed Transactions</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Account</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($transactions as $transaction)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $transaction->date->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $transaction->type === 'income' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : ($transaction->type === 'expense' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200') }}">
                                        {{ ucfirst($transaction->type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                    {{ $transaction->description }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $transaction->category->name ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    @if($transaction->type === 'transfer')
                                        {{ $transaction->fromAccount->name ?? '-' }} → {{ $transaction->toAccount->name ?? '-' }}
                                    @else
                                        {{ $transaction->account->name ?? '-' }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right amount-display {{ $transaction->type === 'income' ? 'text-green-600 dark:text-green-400' : ($transaction->type === 'expense' ? 'text-red-600 dark:text-red-400' : 'text-blue-600 dark:text-blue-400') }}">
                                    {{ $transaction->type === 'income' ? '+' : ($transaction->type === 'expense' ? '-' : '') }}Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">No transactions found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
fetch('/reports/trends?months=6')
    .then(response => response.json())
    .then(data => {
        const ctx = document.getElementById('trendChart').getContext('2d');
        const months = [...new Set([...data.income.map(i => i.month), ...data.expense.map(e => e.month)])].sort();
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: 'Income',
                    data: months.map(m => data.income.find(i => i.month === m)?.total || 0),
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                }, {
                    label: 'Expense',
                    data: months.map(m => data.expense.find(e => e.month === m)?.total || 0),
                    borderColor: 'rgb(239, 68, 68)',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    });
</script>
@endsection
