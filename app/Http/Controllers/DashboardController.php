<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\Budget;
use App\Models\Debt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Total balance (excluding hidden accounts)
        $totalBalance = Account::where('user_id', $user->id)
            ->where('is_hidden', false)
            ->sum('balance');

        // Monthly income and expense
        $currentMonth = Carbon::now()->startOfMonth();
        $monthlyIncome = Transaction::where('user_id', $user->id)
            ->where('type', Transaction::TYPE_INCOME)
            ->where('date', '>=', $currentMonth)
            ->sum('amount');

        $monthlyExpense = Transaction::where('user_id', $user->id)
            ->where('type', Transaction::TYPE_EXPENSE)
            ->where('date', '>=', $currentMonth)
            ->sum('amount');

        // Recent transactions
        $recentTransactions = Transaction::where('user_id', $user->id)
            ->with(['account', 'category'])
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Budget status
        $budgets = Budget::where('user_id', $user->id)
            ->where('month', Carbon::now()->month)
            ->where('year', Carbon::now()->year)
            ->with('category')
            ->get();

        // Overdue debts
        $overdueDebts = Debt::where('user_id', $user->id)
            ->where('is_paid', false)
            ->where('due_date', '<', Carbon::now())
            ->count();

        // Expense by category (current month)
        $expenseByCategory = Transaction::where('user_id', $user->id)
            ->where('type', Transaction::TYPE_EXPENSE)
            ->where('date', '>=', $currentMonth)
            ->select('category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('category_id')
            ->with('category')
            ->get();

        // Income by category (current month)
        $incomeByCategory = Transaction::where('user_id', $user->id)
            ->where('type', Transaction::TYPE_INCOME)
            ->where('date', '>=', $currentMonth)
            ->select('category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('category_id')
            ->with('category')
            ->get();

        // Accounts for wallets view
        $accounts = Account::where('user_id', $user->id)
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();

        // Categories for categories view
        $expenseCategories = \App\Models\Category::where('user_id', $user->id)
            ->where('type', \App\Models\Category::TYPE_EXPENSE)
            ->where('is_active', true)
            ->get();
        
        $incomeCategories = \App\Models\Category::where('user_id', $user->id)
            ->where('type', \App\Models\Category::TYPE_INCOME)
            ->where('is_active', true)
            ->get();

        // All transactions for transactions view
        $allTransactions = Transaction::where('user_id', $user->id)
            ->with(['account', 'category'])
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        // Debts for debts view
        $receivables = Debt::where('user_id', $user->id)
            ->where('type', Debt::TYPE_RECEIVABLE)
            ->where('is_paid', false)
            ->with(['account', 'payments'])
            ->orderBy('due_date', 'asc')
            ->get();
        
        $payables = Debt::where('user_id', $user->id)
            ->where('type', Debt::TYPE_PAYABLE)
            ->where('is_paid', false)
            ->with(['account', 'payments'])
            ->orderBy('due_date', 'asc')
            ->get();

        // Calculate total remaining budget
        $totalRemainingBudget = $budgets->sum(function($budget) {
            return max(0, $budget->amount - $budget->spent);
        });

        // Reports data (current month)
        $totalTransfer = Transaction::where('user_id', $user->id)
            ->where('type', Transaction::TYPE_TRANSFER)
            ->where('date', '>=', $currentMonth)
            ->sum('amount');

        return view('dashboard', compact(
            'totalBalance',
            'monthlyIncome',
            'monthlyExpense',
            'recentTransactions',
            'budgets',
            'overdueDebts',
            'expenseByCategory',
            'incomeByCategory',
            'accounts',
            'expenseCategories',
            'incomeCategories',
            'allTransactions',
            'receivables',
            'payables',
            'totalRemainingBudget',
            'totalTransfer'
        ));
    }
}

