<?php

namespace App\Http\Controllers;

use App\Models\Debt;
use App\Models\DebtPayment;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DebtController extends Controller
{
    public function show($id)
    {
        $debt = Debt::where('user_id', auth()->id())
            ->with(['account', 'payments.transaction'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'debt' => $debt
        ]);
    }

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
        try {
            $validated = $request->validate([
                'type' => 'required|in:payable,receivable',
                'account_id' => [
                    'nullable',
                    'exists:accounts,id',
                    function ($attribute, $value, $fail) {
                        if ($value) {
                            $account = Account::find($value);
                            if ($account && $account->user_id !== auth()->id()) {
                                $fail('The selected account is invalid.');
                            }
                        }
                    },
                ],
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

            $debt->load('account');

            // Return JSON response for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $validated['type'] === 'receivable' ? 'Piutang berhasil ditambahkan.' : 'Utang berhasil dicatat.',
                    'debt' => $debt
                ]);
            }

            return redirect()->route('debts.index')->with('success', 'Debt created successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Return JSON response for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            // Return JSON response for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menambahkan data: ' . $e->getMessage()
                ], 500);
            }
            return back()->withErrors(['error' => 'Failed to create debt: ' . $e->getMessage()])->withInput();
        }
    }

    public function addPayment(Request $request, $id)
    {
        try {
            $debt = Debt::where('user_id', auth()->id())->findOrFail($id);

            $maxAmount = $debt->current_amount > 0 ? $debt->current_amount : 999999999;
            
            $rules = [
                'amount' => ['required', 'numeric', 'min:0.01', 'max:' . $maxAmount],
                'payment_date' => 'required|date',
                'notes' => 'nullable|string',
            ];
            
            // Only validate account_id if create_transaction is checked
            if ($request->has('create_transaction') && $request->boolean('create_transaction')) {
                $rules['account_id'] = [
                    'required',
                    'exists:accounts,id',
                    function ($attribute, $value, $fail) {
                        if ($value) {
                            $account = Account::find($value);
                            if ($account && $account->user_id !== auth()->id()) {
                                $fail('The selected account is invalid.');
                            }
                        }
                    },
                ];
            }
            
            $validated = $request->validate($rules);

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

                $payment->load('transaction');
                $debt->refresh();

                // Return JSON response for AJAX requests
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Pembayaran berhasil dicatat.',
                        'payment' => $payment,
                        'debt' => $debt
                    ]);
                }

                return redirect()->route('debts.index')->with('success', 'Payment recorded successfully.');
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Return JSON response for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            // Return JSON response for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mencatat pembayaran: ' . $e->getMessage()
                ], 500);
            }
            return back()->withErrors(['error' => 'Failed to record payment: ' . $e->getMessage()])->withInput();
        }
    }

    public function markAsPaid(Request $request, $id)
    {
        try {
            $debt = Debt::where('user_id', auth()->id())
                ->where('type', Debt::TYPE_RECEIVABLE)
                ->findOrFail($id);

            if ($debt->is_paid) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Piutang ini sudah ditandai sebagai lunas.'
                    ], 400);
                }
                return back()->withErrors(['error' => 'Piutang ini sudah ditandai sebagai lunas.']);
            }

            $validated = $request->validate([
                'account_id' => [
                    'required',
                    'exists:accounts,id',
                    function ($attribute, $value, $fail) {
                        $account = Account::find($value);
                        if ($account && $account->user_id !== auth()->id()) {
                            $fail('The selected account is invalid.');
                        }
                    },
                ],
                'create_transaction' => 'sometimes|boolean',
            ]);

            DB::beginTransaction();
            try {
                // Mark debt as paid
                $debt->is_paid = true;
                $debt->paid_at = now();
                $debt->current_amount = 0;
                $debt->save();

                // Create transaction if requested (default: true)
                $createTransaction = $request->has('create_transaction') ? $request->boolean('create_transaction') : true;
                
                if ($createTransaction) {
                    $transaction = \App\Models\Transaction::create([
                        'user_id' => auth()->id(),
                        'account_id' => $validated['account_id'],
                        'type' => 'income',
                        'amount' => $debt->initial_amount, // Use initial amount for full payment
                        'description' => "Pembayaran piutang dari {$debt->contact_name}",
                        'date' => now(),
                        'notes' => $debt->description,
                    ]);

                    // Update account balance
                    $account = Account::find($validated['account_id']);
                    $account->balance += $debt->initial_amount;
                    $account->save();
                }

                DB::commit();

                // Return JSON response for AJAX requests
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Piutang berhasil ditandai sebagai lunas' . ($createTransaction ? ' dan transaksi berhasil dibuat.' : '.'),
                        'debt' => $debt
                    ]);
                }

                return redirect()->route('debts.index')->with('success', 'Piutang berhasil ditandai sebagai lunas.');
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Return JSON response for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            // Return JSON response for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menandai piutang sebagai lunas: ' . $e->getMessage()
                ], 500);
            }
            return back()->withErrors(['error' => 'Failed to mark as paid: ' . $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $debt = Debt::where('user_id', auth()->id())->findOrFail($id);

            $validated = $request->validate([
                'type' => 'required|in:payable,receivable',
                'account_id' => [
                    'nullable',
                    'exists:accounts,id',
                    function ($attribute, $value, $fail) {
                        if ($value) {
                            $account = Account::find($value);
                            if ($account && $account->user_id !== auth()->id()) {
                                $fail('The selected account is invalid.');
                            }
                        }
                    },
                ],
                'contact_name' => 'required|string|max:255',
                'contact_phone' => 'nullable|string|max:20',
                'contact_email' => 'nullable|email|max:255',
                'initial_amount' => 'required|numeric|min:0.01',
                'due_date' => 'nullable|date',
                'description' => 'nullable|string',
            ]);

            // Update current_amount if initial_amount changed
            if ($validated['initial_amount'] != $debt->initial_amount) {
                $difference = $validated['initial_amount'] - $debt->initial_amount;
                $debt->current_amount += $difference;
                // Ensure current_amount doesn't go negative
                if ($debt->current_amount < 0) {
                    $debt->current_amount = 0;
                }
            }

            $debt->update([
                'type' => $validated['type'],
                'account_id' => $validated['account_id'] ?? null,
                'contact_name' => $validated['contact_name'],
                'contact_phone' => $validated['contact_phone'] ?? null,
                'contact_email' => $validated['contact_email'] ?? null,
                'initial_amount' => $validated['initial_amount'],
                'current_amount' => $debt->current_amount,
                'due_date' => $validated['due_date'] ?? null,
                'description' => $validated['description'] ?? null,
            ]);

            $debt->load('account');

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $validated['type'] === 'receivable' ? 'Piutang berhasil diperbarui.' : 'Utang berhasil diperbarui.',
                    'debt' => $debt
                ]);
            }

            return redirect()->route('debts.index')->with('success', 'Debt updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memperbarui data: ' . $e->getMessage()
                ], 500);
            }
            return back()->withErrors(['error' => 'Failed to update debt: ' . $e->getMessage()])->withInput();
        }
    }

    public function destroy(Request $request, $id)
    {
        $debt = Debt::where('user_id', auth()->id())->findOrFail($id);
        $debt->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Debt deleted successfully.',
            ]);
        }

        return redirect()->route('debts.index')->with('success', 'Debt deleted successfully.');
    }

    public function createReminder(Request $request, $id)
    {
        try {
            $debt = Debt::where('user_id', auth()->id())->findOrFail($id);

            $validated = $request->validate([
                'reminder_date' => 'required|date',
                'reminder_time' => 'nullable|string',
                'notes' => 'nullable|string',
            ]);

            // Generate Google Calendar link
            $reminderDate = \Carbon\Carbon::parse($validated['reminder_date']);
            if ($validated['reminder_time']) {
                $reminderDateTime = \Carbon\Carbon::parse($validated['reminder_date'] . ' ' . $validated['reminder_time']);
            } else {
                $reminderDateTime = $reminderDate->copy()->setTime(9, 0); // Default 9 AM
            }

            $endDateTime = $reminderDateTime->copy()->addHour(); // 1 hour duration

            $title = "Tagih Piutang: {$debt->contact_name}";
            $description = "Tagih piutang dari {$debt->contact_name}\n";
            $description .= "Jumlah: Rp " . number_format($debt->current_amount, 0, ',', '.') . "\n";
            if ($debt->due_date) {
                $description .= "Jatuh tempo: " . $debt->due_date->format('d M Y') . "\n";
            }
            if ($debt->description) {
                $description .= "Catatan: {$debt->description}\n";
            }
            if ($validated['notes']) {
                $description .= "Reminder: {$validated['notes']}";
            }

            // Format dates for Google Calendar (YYYYMMDDTHHMMSS)
            // Google Calendar will use browser's timezone
            $startDateStr = $reminderDateTime->format('Ymd\THis');
            $endDateStr = $endDateTime->format('Ymd\THis');

            // Create Google Calendar URL
            $googleCalendarUrl = 'https://calendar.google.com/calendar/render?action=TEMPLATE';
            $googleCalendarUrl .= '&text=' . urlencode($title);
            $googleCalendarUrl .= '&dates=' . $startDateStr . '/' . $endDateStr;
            $googleCalendarUrl .= '&details=' . urlencode($description);
            $googleCalendarUrl .= '&location=' . urlencode('');

            // Return JSON response for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Reminder berhasil dibuat. Membuka Google Calendar...',
                    'calendar_url' => $googleCalendarUrl
                ]);
            }

            return redirect($googleCalendarUrl);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Return JSON response for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            // Return JSON response for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal membuat reminder: ' . $e->getMessage()
                ], 500);
            }
            return back()->withErrors(['error' => 'Failed to create reminder: ' . $e->getMessage()]);
        }
    }
}


