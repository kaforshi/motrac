@extends('layouts.app')

@section('title', 'Budgets')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="py-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Budgets - {{ date('F Y') }}</h1>
            <div class="flex gap-2">
                <form method="POST" action="{{ route('budgets.rollover') }}" class="inline">
                    @csrf
                    <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        Process Rollover
                    </button>
                </form>
                <button onclick="showAddBudgetModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    Add Budget
                </button>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <div class="p-6">
                @if($budgets->count() > 0)
                    <div class="space-y-6">
                        @foreach($budgets as $budget)
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <div>
                                        <h3 class="font-semibold text-gray-900 dark:text-white">{{ $budget->category->name }}</h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            Spent: Rp {{ number_format($budget->spent, 0, ',', '.') }} / 
                                            Budget: Rp {{ number_format($budget->amount, 0, ',', '.') }}
                                            @if($budget->rollover_amount > 0)
                                                (+ Rollover: Rp {{ number_format($budget->rollover_amount, 0, ',', '.') }})
                                            @endif
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-lg font-bold {{ $budget->status === 'danger' ? 'text-red-600 dark:text-red-400' : ($budget->status === 'warning' ? 'text-yellow-600 dark:text-yellow-400' : 'text-green-600 dark:text-green-400') }}">
                                            {{ number_format($budget->percentage, 1) }}%
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            Remaining: Rp {{ number_format($budget->remaining, 0, ',', '.') }}
                                        </div>
                                    </div>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4">
                                    <div class="h-4 rounded-full {{ $budget->status === 'danger' ? 'bg-red-600' : ($budget->status === 'warning' ? 'bg-yellow-500' : 'bg-green-600') }}" style="width: {{ min(100, $budget->percentage) }}%"></div>
                                </div>
                                <div class="mt-2 flex gap-2">
                                    <form method="POST" action="{{ route('budgets.destroy', $budget->id) }}" class="inline" onsubmit="return confirm('Are you sure?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 dark:text-red-400 text-sm">Delete</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-center text-gray-500 dark:text-gray-400 py-8">No budgets set for this month</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Add Budget Modal -->
<div id="addBudgetModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-96">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Add Budget</h3>
        <form method="POST" action="{{ route('budgets.store') }}">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category</label>
                <select name="category_id" required class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-white">
                    <option value="">Select category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Amount</label>
                <input type="number" name="amount" step="0.01" min="0.01" required class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-white">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Month</label>
                <input type="number" name="month" value="{{ $currentMonth }}" min="1" max="12" required class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-white">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Year</label>
                <input type="number" name="year" value="{{ $currentYear }}" min="2020" required class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-white">
            </div>
            <div class="mb-4">
                <label class="flex items-center">
                    <input type="checkbox" name="rollover_enabled" value="1" class="rounded border-gray-300 dark:border-gray-700">
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Enable rollover</span>
                </label>
            </div>
            <div class="flex gap-4">
                <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md">Add Budget</button>
                <button type="button" onclick="document.getElementById('addBudgetModal').classList.add('hidden')" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 rounded-md">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function showAddBudgetModal() {
    document.getElementById('addBudgetModal').classList.remove('hidden');
}
</script>
@endsection




