<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\Budget;
use App\Models\Debt;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get and validate user timezone
     */
    private function getUserTimezone($user)
    {
        // Use helper function if available, otherwise validate manually
        if (function_exists('getValidTimezone')) {
            return getValidTimezone($user->timezone ?? null);
        }
        
        // Fallback: manual validation
        $timezone = $user->timezone ?? config('app.timezone', 'Asia/Jakarta');
        try {
            new \DateTimeZone($timezone);
            return $timezone;
        } catch (\Exception $e) {
            return config('app.timezone', 'Asia/Jakarta');
        }
    }
    
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Summary filters (account and category)
        $summaryAccountId = $request->get('summary_account_id');
        $summaryCategoryId = $request->get('summary_category_id');
        
        // Total balance (excluding hidden accounts)
        // If account filter is set, only show balance for that account
        $totalBalanceQuery = Account::where('user_id', $user->id)
            ->where('is_hidden', false);
        
        if ($summaryAccountId) {
            $totalBalanceQuery->where('id', $summaryAccountId);
        }
        
        $totalBalance = $totalBalanceQuery->sum('balance');

        // Get user timezone with validation
        $userTimezone = $this->getUserTimezone($user);
        
        // Month and Year filter (from month picker in navbar)
        $monthYear = $request->get('month_year');
        $selectedMonth = Carbon::now($userTimezone)->month;
        $selectedYear = Carbon::now($userTimezone)->year;
        
        if ($monthYear) {
            $selectedDate = Carbon::parse($monthYear . '-01')->setTimezone($userTimezone);
            $selectedMonth = $selectedDate->month;
            $selectedYear = $selectedDate->year;
            $fromDate = $selectedDate->copy()->startOfMonth();
            $toDate = $selectedDate->copy()->endOfMonth();
        } else {
            $fromDate = Carbon::now($userTimezone)->startOfMonth();
            $toDate = Carbon::now($userTimezone)->endOfMonth();
        }

        // Report filters (date range & type) - these override month_year if provided
        $reportFrom = $request->get('report_from');
        $reportTo = $request->get('report_to');
        $reportType = $request->get('report_type', 'all'); // all, income, expense, transfer

        if ($reportFrom) {
            $fromDate = Carbon::parse($reportFrom)->setTimezone($userTimezone)->startOfDay();
        }
        if ($reportTo) {
            $toDate = Carbon::parse($reportTo)->setTimezone($userTimezone)->endOfDay();
        }

        // Monthly income and expense (respect filters)
        if ($reportType !== 'all' && $reportType !== Transaction::TYPE_INCOME) {
            $monthlyIncome = 0;
        } else {
            $incomeQuery = Transaction::where('user_id', $user->id)
                ->where('type', Transaction::TYPE_INCOME)
                ->whereBetween('date', [$fromDate, $toDate]);
            
            // Apply account filter if set
            if ($summaryAccountId) {
                $incomeQuery->where('account_id', $summaryAccountId);
            }
            
            // Apply category filter if set
            if ($summaryCategoryId) {
                $incomeQuery->where('category_id', $summaryCategoryId);
            }
            
            $monthlyIncome = $incomeQuery->sum('amount');
        }

        if ($reportType !== 'all' && $reportType !== Transaction::TYPE_EXPENSE) {
            $monthlyExpense = 0;
        } else {
            $expenseQuery = Transaction::where('user_id', $user->id)
                ->where('type', Transaction::TYPE_EXPENSE)
                ->whereBetween('date', [$fromDate, $toDate]);
            
            // Apply account filter if set
            if ($summaryAccountId) {
                $expenseQuery->where('account_id', $summaryAccountId);
            }
            
            // Apply category filter if set
            if ($summaryCategoryId) {
                $expenseQuery->where('category_id', $summaryCategoryId);
            }
            
            $monthlyExpense = $expenseQuery->sum('amount');
        }

        // Recent transactions - filter by selected month/year
        $recentTransactions = Transaction::where('user_id', $user->id)
            ->whereBetween('date', [$fromDate, $toDate])
            ->with(['account', 'category'])
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Today's transactions - only show if selected month is current month
        // Use user's timezone if available
        $today = Carbon::today($userTimezone);
        $todayTransactions = collect();
        if (!$monthYear || ($selectedMonth == Carbon::now($userTimezone)->month && $selectedYear == Carbon::now($userTimezone)->year)) {
            // Use whereDate with the date string to ensure timezone is handled correctly
            $todayDateStr = $today->format('Y-m-d');
            $todayTransactions = Transaction::where('user_id', $user->id)
                ->whereDate('date', $todayDateStr)
                ->with(['account', 'category', 'fromAccount', 'toAccount'])
                ->orderBy('created_at', 'desc')
                ->get();
        }

        // Budget status - filter by selected month/year
        $budgets = Budget::where('user_id', $user->id)
            ->where('month', $selectedMonth)
            ->where('year', $selectedYear)
            ->with('category')
            ->get();

        // Overdue debts
        $overdueDebts = Debt::where('user_id', $user->id)
            ->where('is_paid', false)
            ->where('due_date', '<', Carbon::now($userTimezone))
            ->count();

        // Expense by category (respect report filters)
        if ($reportType === 'all' || $reportType === Transaction::TYPE_EXPENSE) {
            $expenseByCategory = Transaction::where('user_id', $user->id)
                ->where('type', Transaction::TYPE_EXPENSE)
                ->whereBetween('date', [$fromDate, $toDate])
                ->select('category_id', DB::raw('SUM(amount) as total'))
                ->groupBy('category_id')
                ->with('category')
                ->get();
        } else {
            $expenseByCategory = collect();
        }

        // Income by category (respect report filters)
        if ($reportType === 'all' || $reportType === Transaction::TYPE_INCOME) {
            $incomeByCategory = Transaction::where('user_id', $user->id)
                ->where('type', Transaction::TYPE_INCOME)
                ->whereBetween('date', [$fromDate, $toDate])
                ->select('category_id', DB::raw('SUM(amount) as total'))
                ->groupBy('category_id')
                ->with('category')
                ->get();
        } else {
            $incomeByCategory = collect();
        }

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

        // All transactions for transactions & reports detailed view with optional filters
        $allTransactionsQuery = Transaction::where('user_id', $user->id)
            ->with(['account', 'category']);

        // Search filter (Transactions tab)
        if ($request->filled('transaction_search')) {
            $search = $request->get('transaction_search');
            $allTransactionsQuery->where(function ($q) use ($search) {
                $q->where('description', 'like', '%' . $search . '%')
                  ->orWhere('notes', 'like', '%' . $search . '%');
            });
        }

        // Category filter (Transactions tab)
        if ($request->filled('transaction_category_id')) {
            $allTransactionsQuery->where('category_id', $request->get('transaction_category_id'));
        }

        // Report filters (date range & type) should also affect Detailed Transactions table
        if (isset($fromDate, $toDate)) {
            $allTransactionsQuery->whereBetween('date', [$fromDate, $toDate]);
        }

        if (!empty($reportType) && $reportType !== 'all') {
            $allTransactionsQuery->where('type', $reportType);
        }

        // Get total count before applying limit
        $totalTransactionsCount = $allTransactionsQuery->count();
        
        // Calculate total income and expense from filtered transactions
        $totalDetailedIncome = (clone $allTransactionsQuery)
            ->where('type', Transaction::TYPE_INCOME)
            ->sum('amount');
        
        $totalDetailedExpense = (clone $allTransactionsQuery)
            ->where('type', Transaction::TYPE_EXPENSE)
            ->sum('amount');
        
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

        // Paid debts history
        $paidReceivables = Debt::where('user_id', $user->id)
            ->where('type', Debt::TYPE_RECEIVABLE)
            ->where('is_paid', true)
            ->with(['account', 'payments'])
            ->orderBy('paid_at', 'desc')
            ->limit(10)
            ->get();
        
        $paidPayables = Debt::where('user_id', $user->id)
            ->where('type', Debt::TYPE_PAYABLE)
            ->where('is_paid', true)
            ->with(['account', 'payments'])
            ->orderBy('paid_at', 'desc')
            ->limit(10)
            ->get();

        // Calculate total remaining budget
        $totalRemainingBudget = $budgets->sum(function($budget) {
            return max(0, $budget->amount - $budget->spent);
        });

        // Reports data - transfer total (respect filters)
        if ($reportType !== 'all' && $reportType !== Transaction::TYPE_TRANSFER) {
            $totalTransfer = 0;
        } else {
            $totalTransfer = Transaction::where('user_id', $user->id)
                ->where('type', Transaction::TYPE_TRANSFER)
                ->whereBetween('date', [$fromDate, $toDate])
                ->sum('amount');
        }

        // Cash flow data based on period type - filtered by selected month/year
        $periodType = $request->get('period', 'weekly'); // daily, weekly, monthly, yearly
        
        $cashFlowData = [];
        $maxCashFlow = 1;
        
        // Use selected month/year for cash flow calculations
        $selectedDate = $monthYear ? Carbon::parse($monthYear . '-01')->setTimezone($userTimezone) : Carbon::now($userTimezone);
        
        switch ($periodType) {
            case 'daily':
                // For daily period, show only today's data
                // If month/year filter is set and it's not the current month, use the last day of that month
                // Otherwise, use today
                $today = Carbon::now($userTimezone);
                if ($monthYear) {
                    $selectedMonthYear = $selectedDate->format('Y-m');
                    $currentMonthYear = $today->format('Y-m');
                    
                    if ($selectedMonthYear === $currentMonthYear) {
                        // Selected month is current month, use today
                        $date = $today;
                    } else {
                        // Selected month is different, use the last day of that month
                        $date = $selectedDate->copy()->endOfMonth();
                    }
                } else {
                    // No month/year filter, use today
                    $date = $today;
                }
                
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
                    'label' => $date->format('d M Y'),
                    'income' => $dayIncome,
                    'expense' => $dayExpense,
                    'net' => $dayNet,
                ];
                break;
                
            case 'weekly':
                // For weekly period, show only current week's data
                $today = Carbon::now($userTimezone);
                if ($monthYear) {
                    // If month/year is selected, use the current week that contains today
                    // But ensure it's within the selected month
                    $weekStart = $today->copy()->startOfWeek();
                    $weekEnd = $today->copy()->endOfWeek();
                    $selectedMonthStart = $selectedDate->copy()->startOfMonth();
                    $selectedMonthEnd = $selectedDate->copy()->endOfMonth();
                    
                    // Adjust week boundaries to be within selected month
                    if ($weekStart->lt($selectedMonthStart)) {
                        $weekStart = $selectedMonthStart->copy();
                    }
                    if ($weekEnd->gt($selectedMonthEnd)) {
                        $weekEnd = $selectedMonthEnd->copy();
                    }
                } else {
                    // No month/year filter, use current week
                    $weekStart = $today->copy()->startOfWeek();
                    $weekEnd = $today->copy()->endOfWeek();
                }
                
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
                    'label' => $weekStart->format('d M') . ' - ' . $weekEnd->format('d M Y'),
                    'income' => $weekIncome,
                    'expense' => $weekExpense,
                    'net' => $weekNet,
                ];
                break;
                
            case 'monthly':
                // For monthly period, show only selected month's data (or current month if no filter)
                $monthStart = $selectedDate->copy()->startOfMonth();
                $monthEnd = $selectedDate->copy()->endOfMonth();
                
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
                break;
                
            case 'yearly':
                // For yearly period, show only selected year's data (or current year if no filter)
                $selectedYear = $selectedDate->year;
                $yearStart = Carbon::create($selectedYear, 1, 1)->startOfYear()->setTimezone($userTimezone);
                $yearEnd = Carbon::create($selectedYear, 12, 31)->endOfYear()->setTimezone($userTimezone);
                
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

        $userCurrency = $user->currency ?? 'IDR';
        $currencySymbol = $userCurrency === 'USD' ? '$' : 'Rp';
        
        // Check and create notifications based on user preferences
        $this->checkAndCreateNotifications($user, $budgets, $monthlyExpense);
        
        // Get unread notifications
        $unreadNotifications = Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        $unreadCount = $unreadNotifications->count();
        
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
            'totalTransactionsCount',
            'totalDetailedIncome',
            'totalDetailedExpense',
            'receivables',
            'payables',
            'paidReceivables',
            'paidPayables',
            'totalRemainingBudget',
            'totalTransfer',
            'cashFlowData',
            'maxCashFlow',
            'periodType',
            'filterCategories',
            'reportFrom',
            'reportTo',
            'reportType',
            'userCurrency',
            'currencySymbol',
            'unreadNotifications',
            'unreadCount',
            'selectedMonth',
            'selectedYear',
            'monthYear',
            'user',
            'userTimezone',
        ));
    }

    private function checkAndCreateNotifications($user, $budgets, $monthlyExpense)
    {
        // Check budget notifications (if user has enabled budget notifications)
        if ($user->notify_budget ?? true) {
            foreach ($budgets as $budget) {
                if (!$budget->category) {
                    continue; // Skip if category is missing
                }
                
                $usagePercent = $budget->amount > 0 ? ($budget->spent / $budget->amount) * 100 : 0;
                
                // Check if budget exceeds 80%
                if ($usagePercent >= 80) {
                    // Check if notification already exists for this budget this month
                    $userTz = $this->getUserTimezone($user);
                    $now = Carbon::now($userTz);
                    $existingNotification = Notification::where('user_id', $user->id)
                        ->where('type', 'budget')
                        ->whereJsonContains('data->budget_id', $budget->id)
                        ->whereMonth('created_at', $now->month)
                        ->whereYear('created_at', $now->year)
                        ->first();
                    
                    if (!$existingNotification) {
                        Notification::create([
                            'user_id' => $user->id,
                            'type' => 'budget',
                            'title' => 'Peringatan Budget',
                            'message' => "Budget untuk kategori {$budget->category->name} telah mencapai " . number_format($usagePercent, 1) . "% dari total budget.",
                            'data' => [
                                'budget_id' => $budget->id,
                                'category_name' => $budget->category->name,
                                'usage_percent' => $usagePercent,
                                'spent' => $budget->spent,
                                'amount' => $budget->amount,
                            ],
                        ]);
                    }
                }
            }
        }
    }

    public function getNotifications(Request $request)
    {
        $user = auth()->user();
        
        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
        
        $unreadCount = Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();
        
        return response()->json([
            'success' => true,
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    public function markAsRead(Request $request, $id)
    {
        $user = auth()->user();
        
        $notification = Notification::where('user_id', $user->id)
            ->findOrFail($id);
        
        $notification->read_at = now();
        $notification->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Notifikasi ditandai sebagai sudah dibaca',
        ]);
    }

    public function markAllAsRead(Request $request)
    {
        $user = auth()->user();
        
        Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
        
        return response()->json([
            'success' => true,
            'message' => 'Semua notifikasi ditandai sebagai sudah dibaca',
        ]);
    }
}

