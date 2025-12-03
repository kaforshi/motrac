<?php

namespace App\Http\Controllers;

use App\Models\Debt;
use App\Models\DebtPayment;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DebtController extends Controller
{
    public function index()
    {
        $debts = Debt::where('user_id', auth()->id())
            ->with(['account', 'payments'])
            ->orderBy('due_date', 'asc')
            ->get();

        return view('debts.index', compact('debts'));
    }

    public function create()
    {
        $accounts = Account::where('user_id', auth()->id())
            ->where('is_active', true)
            ->get();

        return view('debts.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:payable,receivable',
            'account_id' => 'nullable|exists:accounts,id',
            'contact_name' => 'required|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'initial_amount' => 'required|numeric|min:0.01',
            'due_date' => 'nullable|date',
            'description' => 'nullable|string',
        ]);

        $debt = Debt::create([
            'user_id' => auth()->id(),
            'type' => $validated['type'],
            'account_id' => $validated['account_id'] ?? null,
            'contact_name' => $validated['contact_name'],
            'contact_phone' => $validated['contact_phone'] ?? null,
            'contact_email' => $validated['contact_email'] ?? null,
            'initial_amount' => $validated['initial_amount'],
            'current_amount' => $validated['initial_amount'],
            'due_date' => $validated['due_date'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('debts.index')->with('success', 'Debt created successfully.');
    }

    public function addPayment(Request $request, $id)
    {
        $debt = Debt::where('user_id', auth()->id())->findOrFail($id);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $debt->current_amount,
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
            'create_transaction' => 'boolean',
            'account_id' => 'required_if:create_transaction,1|exists:accounts,id',
        ]);

        DB::beginTransaction();
        try {
            $payment = DebtPayment::create([
                'debt_id' => $debt->id,
                'amount' => $validated['amount'],
                'payment_date' => $validated['payment_date'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $debt->current_amount -= $validated['amount'];
            if ($debt->current_amount <= 0) {
                $debt->is_paid = true;
                $debt->paid_at = now();
                $debt->current_amount = 0;
            }
            $debt->save();

            // Create transaction if requested
            if ($request->boolean('create_transaction')) {
                $transaction = \App\Models\Transaction::create([
                    'user_id' => auth()->id(),
                    'account_id' => $validated['account_id'],
                    'type' => $debt->type === Debt::TYPE_PAYABLE ? 'expense' : 'income',
                    'amount' => $validated['amount'],
                    'description' => "Payment for debt: {$debt->contact_name}",
                    'date' => $validated['payment_date'],
                    'notes' => $validated['notes'] ?? null,
                ]);

                $payment->transaction_id = $transaction->id;
                $payment->save();

                // Update account balance
                $account = Account::find($validated['account_id']);
                if ($debt->type === Debt::TYPE_PAYABLE) {
                    $account->balance -= $validated['amount'];
                } else {
                    $account->balance += $validated['amount'];
                }
                $account->save();
            }

            DB::commit();
            return redirect()->route('debts.index')->with('success', 'Payment recorded successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to record payment: ' . $e->getMessage()])->withInput();
        }
    }

    public function destroy($id)
    {
        $debt = Debt::where('user_id', auth()->id())->findOrFail($id);
        $debt->delete();

        return redirect()->route('debts.index')->with('success', 'Debt deleted successfully.');
    }
}

