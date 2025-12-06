<?php

namespace App\Http\Controllers;

use App\Models\TransactionTemplate;
use App\Models\Transaction;
use App\Models\Account;
use App\Models\Category;
use Illuminate\Http\Request;

class TransactionTemplateController extends Controller
{
    public function index()
    {
        $templates = TransactionTemplate::where('user_id', auth()->id())
            ->with(['account', 'category'])
            ->get();

        return view('templates.index', compact('templates'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'account_id' => 'nullable|exists:accounts,id',
            'category_id' => 'nullable|exists:categories,id',
            'type' => 'required|in:income,expense,transfer',
            'amount' => 'nullable|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        TransactionTemplate::create([
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'account_id' => $validated['account_id'] ?? null,
            'category_id' => $validated['category_id'] ?? null,
            'type' => $validated['type'],
            'amount' => $validated['amount'] ?? null,
            'description' => $validated['description'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('templates.index')->with('success', 'Template created successfully.');
    }

    public function useTemplate($id)
    {
        $template = TransactionTemplate::where('user_id', auth()->id())->findOrFail($id);
        
        $transaction = Transaction::create([
            'user_id' => auth()->id(),
            'account_id' => $template->account_id,
            'category_id' => $template->category_id,
            'type' => $template->type,
            'amount' => $template->amount ?? 0,
            'description' => $template->description ?? $template->name,
            'date' => now(),
            'notes' => $template->notes,
        ]);

        // Update account balance
        if ($template->account_id) {
            $account = Account::find($template->account_id);
            if ($template->type === Transaction::TYPE_INCOME) {
                $account->balance += $template->amount ?? 0;
            } elseif ($template->type === Transaction::TYPE_EXPENSE) {
                $account->balance -= $template->amount ?? 0;
            }
            $account->save();
        }

        return redirect()->route('transactions.index')->with('success', 'Transaction created from template.');
    }

    public function destroy($id)
    {
        $template = TransactionTemplate::where('user_id', auth()->id())->findOrFail($id);
        $template->delete();

        return redirect()->route('templates.index')->with('success', 'Template deleted successfully.');
    }
}





