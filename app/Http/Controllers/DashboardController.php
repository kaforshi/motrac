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
    public function index(Request $request)
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

        // Today's transactions
        $today = Carbon::today();
        $todayTransactions = Transaction::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->with(['account', 'category', 'fromAccount', 'toAccount'])
            ->orderBy('created_at', 'desc')
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

        // All active categories for transactions filter dropdown
        $filterCategories = \App\Models\Category::where('user_id', $user->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // All transactions for transactions view with optional filters
        $allTransactionsQuery = Transaction::where('user_id', $user->id)
            ->with(['account', 'category']);

        // Search filter
        if ($request->filled('transaction_search')) {
            $search = $request->get('transaction_search');
            $allTransactionsQuery->where(function ($q) use ($search) {
                $q->where('description', 'like', '%' . $search . '%')
                  ->orWhere('notes', 'like', '%' . $search . '%');
            });
        }

        // Category filter
        if ($request->filled('transaction_category_id')) {
            $allTransactionsQuery->where('category_id', $request->get('transaction_category_id'));
        }

        $allTransactions = $allTransactionsQuery
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

        // Cash flow data based on period type
        $periodType = $request->get('period', 'weekly'); // daily, weekly, monthly, yearly
        
        $cashFlowData = [];
        $maxCashFlow = 1;
        
        switch ($periodType) {
            case 'daily':
                // Last 7 days
                for ($i = 6; $i >= 0; $i--) {
                    $date = Carbon::now()->subDays($i);
                    $dayStart = $date->copy()->startOfDay();
                    $dayEnd = $date->copy()->endOfDay();
                    
                    $dayIncome = Transaction::where('user_id', $user->id)
                        ->where('type', Transaction::TYPE_INCOME)
                        ->whereBetween('date', [$dayStart, $dayEnd])
                        ->sum('amount');
                    
                    $dayExpense = Transaction::where('user_id', $user->id)
                        ->where('type', Transaction::TYPE_EXPENSE)
                        ->whereBetween('date', [$dayStart, $dayEnd])
                        ->sum('amount');
                    
                    $dayNet = $dayIncome - $dayExpense;
                    $cashFlowData[] = [
                        'date' => $date,
                        'label' => $date->format('d M'),
                        'income' => $dayIncome,
                        'expense' => $dayExpense,
                        'net' => $dayNet,
                    ];
                }
                break;
                
            case 'weekly':
                // Last 7 weeks
                for ($i = 6; $i >= 0; $i--) {
                    $weekStart = Carbon::now()->subWeeks($i)->startOfWeek();
                    $weekEnd = Carbon::now()->subWeeks($i)->endOfWeek();
                    
                    $weekIncome = Transaction::where('user_id', $user->id)
                        ->where('type', Transaction::TYPE_INCOME)
                        ->whereBetween('date', [$weekStart, $weekEnd])
                        ->sum('amount');
                    
                    $weekExpense = Transaction::where('user_id', $user->id)
                        ->where('type', Transaction::TYPE_EXPENSE)
                        ->whereBetween('date', [$weekStart, $weekEnd])
                        ->sum('amount');
                    
                    $weekNet = $weekIncome - $weekExpense;
                    $cashFlowData[] = [
                        'date' => $weekStart,
                        'label' => 'Minggu ' . $weekStart->format('d M'),
                        'income' => $weekIncome,
                        'expense' => $weekExpense,
                        'net' => $weekNet,
                    ];
                }
                break;
                
            case 'monthly':
                // Last 7 months
                for ($i = 6; $i >= 0; $i--) {
                    $monthStart = Carbon::now()->subMonths($i)->startOfMonth();
                    $monthEnd = Carbon::now()->subMonths($i)->endOfMonth();
                    
                    $monthIncome = Transaction::where('user_id', $user->id)
                        ->where('type', Transaction::TYPE_INCOME)
                        ->whereBetween('date', [$monthStart, $monthEnd])
                        ->sum('amount');
                    
                    $monthExpense = Transaction::where('user_id', $user->id)
                        ->where('type', Transaction::TYPE_EXPENSE)
                        ->whereBetween('date', [$monthStart, $monthEnd])
                        ->sum('amount');
                    
                    $monthNet = $monthIncome - $monthExpense;
                    $cashFlowData[] = [
                        'date' => $monthStart,
                        'label' => $monthStart->format('M Y'),
                        'income' => $monthIncome,
                        'expense' => $monthExpense,
                        'net' => $monthNet,
                    ];
                }
                break;
                
            case 'yearly':
                // Last 7 years
                for ($i = 6; $i >= 0; $i--) {
                    $yearStart = Carbon::now()->subYears($i)->startOfYear();
                    $yearEnd = Carbon::now()->subYears($i)->endOfYear();
                    
                    $yearIncome = Transaction::where('user_id', $user->id)
                        ->where('type', Transaction::TYPE_INCOME)
                        ->whereBetween('date', [$yearStart, $yearEnd])
                        ->sum('amount');
                    
                    $yearExpense = Transaction::where('user_id', $user->id)
                        ->where('type', Transaction::TYPE_EXPENSE)
                        ->whereBetween('date', [$yearStart, $yearEnd])
                        ->sum('amount');
                    
                    $yearNet = $yearIncome - $yearExpense;
                    $cashFlowData[] = [
                        'date' => $yearStart,
                        'label' => $yearStart->format('Y'),
                        'income' => $yearIncome,
                        'expense' => $yearExpense,
                        'net' => $yearNet,
                    ];
                }
                break;
        }
        
        // Find max value for scaling
        if (count($cashFlowData) > 0) {
            $maxCashFlow = max(
                abs(collect($cashFlowData)->min('net')),
                abs(collect($cashFlowData)->max('net')),
                1
            );
        }

        return view('dashboard', compact(
            'totalBalance',
            'monthlyIncome',
            'monthlyExpense',
            'recentTransactions',
            'todayTransactions',
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
            'totalTransfer',
            'cashFlowData',
            'maxCashFlow',
            'periodType',
            'filterCategories'
        ));
    }
}

