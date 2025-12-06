@extends('layouts.app')

@section('title', 'Add Debt/Receivable')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="py-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">Add Debt/Receivable</h1>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <form method="POST" action="{{ route('debts.store') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
                        <select name="type" required class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-white">
                            <option value="payable">Payable (Utang - I owe)</option>
                            <option value="receivable">Receivable (Piutang - They owe me)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Contact Name</label>
                        <input type="text" name="contact_name" required class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Contact Phone</label>
                        <input type="text" name="contact_phone" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Contact Email</label>
                        <input type="email" name="contact_email" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Amount</label>
                        <input type="number" name="initial_amount" step="0.01" min="0.01" required class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Due Date</label>
                        <input type="date" name="due_date" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                        <textarea name="description" rows="3" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-white"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Account (Optional)</label>
                        <select name="account_id" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-white">
                            <option value="">None</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}">{{ $account->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex gap-4">
                        <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md">Create</button>
                        <a href="{{ route('debts.index') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 rounded-md">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection





