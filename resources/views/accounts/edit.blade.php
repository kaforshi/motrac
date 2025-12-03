@extends('layouts.app')

@section('title', 'Edit Account')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="py-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">Edit Account</h1>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <form method="POST" action="{{ route('accounts.update', $account->id) }}">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name</label>
                        <input type="text" name="name" value="{{ $account->name }}" required class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
                        <select name="type" required class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-white">
                            <option value="cash" {{ $account->type === 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="bank" {{ $account->type === 'bank' ? 'selected' : '' }}>Bank Account</option>
                            <option value="ewallet" {{ $account->type === 'ewallet' ? 'selected' : '' }}>E-Wallet</option>
                            <option value="liability" {{ $account->type === 'liability' ? 'selected' : '' }}>Liability</option>
                            <option value="investment" {{ $account->type === 'investment' ? 'selected' : '' }}>Investment</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Currency</label>
                        <input type="text" name="currency" value="{{ $account->currency }}" maxlength="3" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" name="is_hidden" value="1" {{ $account->is_hidden ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-700">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Hide from daily balance</span>
                        </label>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" name="is_active" value="1" {{ $account->is_active ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-700">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Active</span>
                        </label>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                        <textarea name="notes" rows="3" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-white">{{ $account->notes }}</textarea>
                    </div>
                    <div class="flex gap-4">
                        <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md">Update Account</button>
                        <a href="{{ route('accounts.index') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 rounded-md">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

