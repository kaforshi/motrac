<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Account;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
// PDF will be loaded dynamically

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Get filter parameters with defaults
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));
        $type = $request->get('type', 'all');
        
        // Initialize variables
        $transactions = collect();
        $totalIncome = 0;
        $totalExpense = 0;
        $totalTransfer = 0;
        $netAmount = 0;
        $expenseByCategory = collect();
        $incomeByCategory = collect();
        
        // Build query
        $query = Transaction::where('user_id', $user->id)
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->with(['account', 'category', 'fromAccount', 'toAccount'])
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc');
        
        if ($type !== 'all') {
            $query->where('type', $type);
        }
        
        $transactions = $query->get();
        
        // Calculate totals
        $totalIncome = $transactions->where('type', Transaction::TYPE_INCOME)->sum('amount');
        $totalExpense = $transactions->where('type', Transaction::TYPE_EXPENSE)->sum('amount');
        $totalTransfer = $transactions->where('type', Transaction::TYPE_TRANSFER)->sum('amount');
        $netAmount = $totalIncome - $totalExpense;
        
        // Group by category for expense breakdown
        $expenseByCategory = $transactions->where('type', Transaction::TYPE_EXPENSE)
            ->groupBy('category_id')
            ->map(function($group) {
                return [
                    'category' => $group->first()->category->name ?? 'Uncategorized',
                    'total' => $group->sum('amount'),
                    'count' => $group->count(),
                ];
            })
            ->sortByDesc('total')
            ->values();
        
        // Group by category for income breakdown
        $incomeByCategory = $transactions->where('type', Transaction::TYPE_INCOME)
            ->groupBy('category_id')
            ->map(function($group) {
                return [
                    'category' => $group->first()->category->name ?? 'Uncategorized',
                    'total' => $group->sum('amount'),
                    'count' => $group->count(),
                ];
            })
            ->sortByDesc('total')
            ->values();
        
        return view('reports.index', compact(
            'transactions',
            'totalIncome',
            'totalExpense',
            'totalTransfer',
            'netAmount',
            'expenseByCategory',
            'incomeByCategory',
            'dateFrom',
            'dateTo',
            'type'
        ));
    }

    public function trends(Request $request)
    {
        $user = auth()->user();
        $months = $request->get('months', 6);
        
        $startDate = Carbon::now()->subMonths($months)->startOfMonth();
        
        $income = Transaction::where('user_id', $user->id)
            ->where('type', Transaction::TYPE_INCOME)
            ->where('date', '>=', $startDate)
            ->select(DB::raw('DATE_FORMAT(date, "%Y-%m") as month'), DB::raw('SUM(amount) as total'))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $expense = Transaction::where('user_id', $user->id)
            ->where('type', Transaction::TYPE_EXPENSE)
            ->where('date', '>=', $startDate)
            ->select(DB::raw('DATE_FORMAT(date, "%Y-%m") as month'), DB::raw('SUM(amount) as total'))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return response()->json([
            'income' => $income,
            'expense' => $expense,
        ]);
    }

    public function export(Request $request)
    {
        $user = auth()->user();
        $query = Transaction::where('user_id', $user->id)
            ->with(['account', 'category']);

        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        $transactions = $query->orderBy('date', 'desc')->get();

        $data = $transactions->map(function($transaction) {
            return [
                'Date' => $transaction->date->format('Y-m-d'),
                'Type' => ucfirst($transaction->type),
                'Account' => $transaction->account->name ?? '-',
                'Category' => $transaction->category->name ?? '-',
                'Amount' => $transaction->amount,
                'Description' => $transaction->description,
                'Notes' => $transaction->notes ?? '',
            ];
        });

        $filename = 'transactions_' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            if ($data->count() > 0) {
                fputcsv($file, array_keys($data->first()));
                foreach ($data as $row) {
                    fputcsv($file, $row);
                }
            } else {
                fputcsv($file, ['Date', 'Type', 'Account', 'Category', 'Amount', 'Description', 'Notes']);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf(Request $request)
    {
        $user = auth()->user();
        
        // Get filter parameters
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->format('Y-m-d'));
        $type = $request->get('type', 'all');
        
        // Build query
        $query = Transaction::where('user_id', $user->id)
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->with(['account', 'category', 'fromAccount', 'toAccount'])
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc');
        
        if ($type !== 'all') {
            $query->where('type', $type);
        }
        
        $transactions = $query->get();
        
        // Calculate totals
        $totalIncome = $transactions->where('type', Transaction::TYPE_INCOME)->sum('amount');
        $totalExpense = $transactions->where('type', Transaction::TYPE_EXPENSE)->sum('amount');
        $totalTransfer = $transactions->where('type', Transaction::TYPE_TRANSFER)->sum('amount');
        $netAmount = $totalIncome - $totalExpense;
        
        // Group by category
        $expenseByCategory = $transactions->where('type', Transaction::TYPE_EXPENSE)
            ->groupBy('category_id')
            ->map(function($group) {
                return [
                    'category' => $group->first()->category->name ?? 'Uncategorized',
                    'total' => $group->sum('amount'),
                    'count' => $group->count(),
                ];
            })
            ->sortByDesc('total')
            ->values();
        
        $incomeByCategory = $transactions->where('type', Transaction::TYPE_INCOME)
            ->groupBy('category_id')
            ->map(function($group) {
                return [
                    'category' => $group->first()->category->name ?? 'Uncategorized',
                    'total' => $group->sum('amount'),
                    'count' => $group->count(),
                ];
            })
            ->sortByDesc('total')
            ->values();
        
        $data = [
            'user' => $user,
            'transactions' => $transactions,
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'totalTransfer' => $totalTransfer,
            'netAmount' => $netAmount,
            'expenseByCategory' => $expenseByCategory,
            'incomeByCategory' => $incomeByCategory,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'type' => $type,
        ];
        
        try {
            if (class_exists('\Barryvdh\DomPDF\Facade\Pdf')) {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.pdf', $data);
                $filename = 'financial_report_' . date('Y-m-d') . '.pdf';
                return $pdf->download($filename);
            } else {
                // Fallback: return HTML view if PDF library not available
                return view('reports.pdf', $data);
            }
        } catch (\Exception $e) {
            // Fallback: return HTML view if PDF library not available
            return view('reports.pdf', $data);
        }
    }
}

