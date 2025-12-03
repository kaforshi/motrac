<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Account;
use App\Models\Category;
use App\Models\TransactionTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Transaction::where('user_id', $user->id)
            ->with(['account', 'category', 'fromAccount', 'toAccount']);

        // Filters
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('account_id')) {
            $query->where(function($q) use ($request) {
                $q->where('account_id', $request->account_id)
                  ->orWhere('from_account_id', $request->account_id)
                  ->orWhere('to_account_id', $request->account_id);
            });
        }

        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $transactions = $query->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $accounts = Account::where('user_id', $user->id)
            ->where('is_active', true)
            ->get();

        $categories = Category::where('user_id', $user->id)
            ->where('is_active', true)
            ->get();

        return view('transactions.index', compact('transactions', 'accounts', 'categories'));
    }

    public function create()
    {
        $user = auth()->user();
        $accounts = Account::where('user_id', $user->id)
            ->where('is_active', true)
            ->get();

        $categories = Category::where('user_id', $user->id)
            ->where('is_active', true)
            ->get();

        $templates = TransactionTemplate::where('user_id', $user->id)->get();

        return view('transactions.create', compact('accounts', 'categories', 'templates'));
    }

    public function getFormData()
    {
        try {
            $userId = auth()->id();

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                    'accounts' => [],
                    'categories' => [],
                ], 401);
            }

            $accounts = Account::where('user_id', $userId)
                ->where('is_active', true)
                ->get();

            $categories = Category::where('user_id', $userId)
                ->where('is_active', true)
                ->get();

            return response()->json([
                'success' => true,
                'accounts' => $accounts,
                'categories' => $categories,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Error in getFormData: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data form.',
                'accounts' => [],
                'categories' => [],
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        
        // Base validation rules
        $rules = [
            'type' => 'required|in:income,expense,transfer',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'date' => 'required|date',
            'notes' => 'nullable|string',
            'receipt' => 'nullable|image|max:2048',
            'split_transactions' => 'nullable|array',
        ];

        // Add account validation based on type
        if ($request->type === 'transfer') {
            $rules['from_account_id'] = [
                'required',
                Rule::exists('accounts', 'id')->where('user_id', $user->id),
            ];
            $rules['to_account_id'] = [
                'required',
                Rule::exists('accounts', 'id')->where('user_id', $user->id),
            ];
            // Ensure account_id is not validated for transfer
            $rules['account_id'] = 'nullable';
        } else {
            $rules['account_id'] = [
                'required',
                Rule::exists('accounts', 'id')->where('user_id', $user->id),
            ];
            // Ensure transfer fields are not validated for income/expense
            $rules['from_account_id'] = 'nullable';
            $rules['to_account_id'] = 'nullable';
        }

        // Category validation
        $rules['category_id'] = [
            'nullable',
            Rule::exists('categories', 'id')->where('user_id', $user->id),
        ];

        try {
            $validated = $request->validate($rules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }

        DB::beginTransaction();
        try {
            $user = auth()->user();

            // Handle receipt upload
            $receiptPath = null;
            if ($request->hasFile('receipt')) {
                $receiptPath = $request->file('receipt')->store('receipts', 'public');
            }

            // Handle split transactions
            if ($request->has('split_transactions') && count($request->split_transactions) > 0) {
                $parentTransaction = Transaction::create([
                    'user_id' => $user->id,
                    'account_id' => $validated['account_id'] ?? null,
                    'category_id' => $validated['category_id'] ?? null,
                    'type' => $validated['type'],
                    'amount' => array_sum(array_column($request->split_transactions, 'amount')),
                    'description' => $validated['description'],
                    'date' => $validated['date'],
                    'notes' => $validated['notes'],
                    'receipt_path' => $receiptPath,
                    'is_split' => true,
                ]);

                foreach ($request->split_transactions as $split) {
                    Transaction::create([
                        'user_id' => $user->id,
                        'account_id' => $validated['account_id'] ?? null,
                        'category_id' => $split['category_id'],
                        'type' => $validated['type'],
                        'amount' => $split['amount'],
                        'description' => $split['description'] ?? $validated['description'],
                        'date' => $validated['date'],
                        'notes' => $split['notes'] ?? null,
                        'parent_transaction_id' => $parentTransaction->id,
                        'is_split' => false,
                    ]);
                }

                $this->updateAccountBalance($validated['type'], $validated['account_id'] ?? null, array_sum(array_column($request->split_transactions, 'amount')));
            } else {
                // Regular transaction
                $transaction = Transaction::create([
                    'user_id' => $user->id,
                    'account_id' => $validated['account_id'] ?? null,
                    'from_account_id' => $validated['from_account_id'] ?? null,
                    'to_account_id' => $validated['to_account_id'] ?? null,
                    'category_id' => $validated['category_id'] ?? null,
                    'type' => $validated['type'],
                    'amount' => $validated['amount'],
                    'description' => $validated['description'],
                    'date' => $validated['date'],
                    'notes' => $validated['notes'],
                    'receipt_path' => $receiptPath,
                ]);

                $this->updateAccountBalance($validated['type'], $validated['account_id'] ?? $validated['from_account_id'] ?? null, $validated['amount'], $validated['to_account_id'] ?? null);
            }

            DB::commit();
            
            // Return JSON response for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                $transaction->load(['account', 'category', 'fromAccount', 'toAccount']);
                return response()->json([
                    'success' => true,
                    'message' => 'Transaction created successfully.',
                    'transaction' => $transaction
                ]);
            }
            
            return redirect()->route('transactions.index')->with('success', 'Transaction created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Return JSON response for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create transaction: ' . $e->getMessage(),
                    'errors' => $e->getMessage()
                ], 422);
            }
            
            return back()->withErrors(['error' => 'Failed to create transaction: ' . $e->getMessage()])->withInput();
        }
    }

    private function updateAccountBalance($type, $accountId, $amount, $toAccountId = null)
    {
        if ($type === Transaction::TYPE_INCOME && $accountId) {
            $account = Account::find($accountId);
            $account->balance += $amount;
            $account->save();
        } elseif ($type === Transaction::TYPE_EXPENSE && $accountId) {
            $account = Account::find($accountId);
            $account->balance -= $amount;
            $account->save();
        } elseif ($type === Transaction::TYPE_TRANSFER && $accountId && $toAccountId) {
            $fromAccount = Account::find($accountId);
            $fromAccount->balance -= $amount;
            $fromAccount->save();

            $toAccount = Account::find($toAccountId);
            $toAccount->balance += $amount;
            $toAccount->save();
        }
    }

    public function destroy($id)
    {
        $transaction = Transaction::where('user_id', auth()->id())->findOrFail($id);
        
        // Reverse balance changes
        if ($transaction->type === Transaction::TYPE_INCOME && $transaction->account_id) {
            $account = Account::find($transaction->account_id);
            $account->balance -= $transaction->amount;
            $account->save();
        } elseif ($transaction->type === Transaction::TYPE_EXPENSE && $transaction->account_id) {
            $account = Account::find($transaction->account_id);
            $account->balance += $transaction->amount;
            $account->save();
        } elseif ($transaction->type === Transaction::TYPE_TRANSFER) {
            if ($transaction->from_account_id) {
                $fromAccount = Account::find($transaction->from_account_id);
                $fromAccount->balance += $transaction->amount;
                $fromAccount->save();
            }
            if ($transaction->to_account_id) {
                $toAccount = Account::find($transaction->to_account_id);
                $toAccount->balance -= $transaction->amount;
                $toAccount->save();
            }
        }

        // Delete receipt if exists
        if ($transaction->receipt_path) {
            Storage::disk('public')->delete($transaction->receipt_path);
        }

        $transaction->delete();

        return redirect()->route('transactions.index')->with('success', 'Transaction deleted successfully.');
    }
}

