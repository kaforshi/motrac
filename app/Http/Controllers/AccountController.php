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
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'type' => 'required|in:cash,bank,ewallet,liability,investment',
                'initial_balance' => 'nullable|numeric',
                'currency' => 'nullable|string|max:3',
                'notes' => 'nullable|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }
            throw $e;
        }

        $account = Account::create([
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'type' => $validated['type'],
            'balance' => $validated['initial_balance'] ?? 0,
            'initial_balance' => $validated['initial_balance'] ?? 0,
            'currency' => $validated['currency'] ?? 'IDR',
            'notes' => $validated['notes'] ?? null,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Account created successfully.',
                'account' => $account,
            ]);
        }

        return redirect()->route('accounts.index')->with('success', 'Account created successfully.');
    }

    public function edit($id)
    {
        $account = Account::where('user_id', auth()->id())->findOrFail($id);
        return view('accounts.edit', compact('account'));
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $account = Account::where('user_id', $user->id)->findOrFail($id);

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'type' => 'required|in:cash,bank,ewallet,liability,investment',
                'currency' => 'nullable|string|max:3',
                'is_hidden' => 'nullable',
                'is_active' => 'nullable',
                'notes' => 'nullable|string',
                'initial_balance' => 'nullable|numeric',
                'balance' => 'nullable|numeric',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }
            throw $e;
        }

        // Normalisasi checkbox (jika tidak dikirim dianggap false)
        // Checkbox yang dicentang akan mengirim "on" atau "1", yang tidak dicentang tidak dikirim sama sekali
        $validated['is_hidden'] = $request->has('is_hidden') && ($request->input('is_hidden') === 'on' || $request->input('is_hidden') === '1' || $request->input('is_hidden') === true || $request->input('is_hidden') === 1);
        $validated['is_active'] = $request->has('is_active') && ($request->input('is_active') === 'on' || $request->input('is_active') === '1' || $request->input('is_active') === true || $request->input('is_active') === 1);

        // Update balance if provided (for editing)
        if ($request->has('balance')) {
            $validated['balance'] = $request->input('balance');
        } elseif ($request->has('initial_balance')) {
            // If balance not provided but initial_balance is, use initial_balance for balance
            $validated['balance'] = $request->input('initial_balance');
        }

        // Update initial_balance if provided
        if ($request->has('initial_balance')) {
            $validated['initial_balance'] = $request->input('initial_balance');
        }

        $account->update($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Account updated successfully.',
                'account' => $account,
            ]);
        }

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


