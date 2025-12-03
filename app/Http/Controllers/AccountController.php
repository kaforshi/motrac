<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountReconciliation;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index()
    {
        $accounts = Account::where('user_id', auth()->id())
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        return view('accounts.index', compact('accounts'));
    }

    public function create()
    {
        return view('accounts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:cash,bank,ewallet,liability,investment',
            'initial_balance' => 'nullable|numeric',
            'currency' => 'nullable|string|max:3',
            'notes' => 'nullable|string',
        ]);

        $account = Account::create([
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'type' => $validated['type'],
            'balance' => $validated['initial_balance'] ?? 0,
            'initial_balance' => $validated['initial_balance'] ?? 0,
            'currency' => $validated['currency'] ?? 'IDR',
            'notes' => $validated['notes'],
        ]);

        return redirect()->route('accounts.index')->with('success', 'Account created successfully.');
    }

    public function edit($id)
    {
        $account = Account::where('user_id', auth()->id())->findOrFail($id);
        return view('accounts.edit', compact('account'));
    }

    public function update(Request $request, $id)
    {
        $account = Account::where('user_id', auth()->id())->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:cash,bank,ewallet,liability,investment',
            'currency' => 'nullable|string|max:3',
            'is_hidden' => 'boolean',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $account->update($validated);

        return redirect()->route('accounts.index')->with('success', 'Account updated successfully.');
    }

    public function destroy($id)
    {
        $account = Account::where('user_id', auth()->id())->findOrFail($id);
        $account->delete();

        return redirect()->route('accounts.index')->with('success', 'Account deleted successfully.');
    }

    public function reconcile(Request $request, $id)
    {
        $account = Account::where('user_id', auth()->id())->findOrFail($id);

        $validated = $request->validate([
            'actual_balance' => 'required|numeric',
            'notes' => 'nullable|string',
        ]);

        $difference = $validated['actual_balance'] - $account->balance;

        AccountReconciliation::create([
            'account_id' => $account->id,
            'reconciled_balance' => $account->balance,
            'actual_balance' => $validated['actual_balance'],
            'difference' => $difference,
            'reconciled_at' => now(),
            'notes' => $validated['notes'],
        ]);

        // Update account balance
        $account->balance = $validated['actual_balance'];
        $account->save();

        return redirect()->route('accounts.index')->with('success', 'Account reconciled successfully.');
    }
}


