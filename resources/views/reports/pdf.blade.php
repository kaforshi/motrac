<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Financial Report - {{ $dateFrom }} to {{ $dateTo }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #333;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .summary {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        .summary-row {
            display: table-row;
        }
        .summary-cell {
            display: table-cell;
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }
        .summary-cell strong {
            display: block;
            font-size: 14px;
            margin-bottom: 5px;
        }
        .summary-cell.income {
            background-color: #d4edda;
        }
        .summary-cell.expense {
            background-color: #f8d7da;
        }
        .summary-cell.net {
            background-color: #d1ecf1;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .category-section {
            margin-bottom: 30px;
        }
        .category-section h3 {
            margin-bottom: 10px;
            color: #333;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #666;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Financial Report</h1>
        <p><strong>Period:</strong> {{ \Carbon\Carbon::parse($dateFrom)->format('F d, Y') }} - {{ \Carbon\Carbon::parse($dateTo)->format('F d, Y') }}</p>
        <p><strong>Generated:</strong> {{ now()->format('F d, Y H:i:s') }}</p>
        <p><strong>User:</strong> {{ $user->name }}</p>
    </div>

    <div class="summary">
        <div class="summary-row">
            <div class="summary-cell income">
                <strong>Total Income</strong>
                <span>Rp {{ number_format($totalIncome, 0, ',', '.') }}</span>
            </div>
            <div class="summary-cell expense">
                <strong>Total Expense</strong>
                <span>Rp {{ number_format($totalExpense, 0, ',', '.') }}</span>
            </div>
            <div class="summary-cell net">
                <strong>Net Amount</strong>
                <span>Rp {{ number_format($netAmount, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    @if($expenseByCategory->count() > 0)
    <div class="category-section">
        <h3>Expense by Category</h3>
        <table>
            <thead>
                <tr>
                    <th>Category</th>
                    <th class="text-center">Transactions</th>
                    <th class="text-right">Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($expenseByCategory as $item)
                    <tr>
                        <td>{{ $item['category'] }}</td>
                        <td class="text-center">{{ $item['count'] }}</td>
                        <td class="text-right">Rp {{ number_format($item['total'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if($incomeByCategory->count() > 0)
    <div class="category-section">
        <h3>Income by Category</h3>
        <table>
            <thead>
                <tr>
                    <th>Category</th>
                    <th class="text-center">Transactions</th>
                    <th class="text-right">Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($incomeByCategory as $item)
                    <tr>
                        <td>{{ $item['category'] }}</td>
                        <td class="text-center">{{ $item['count'] }}</td>
                        <td class="text-right">Rp {{ number_format($item['total'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="category-section">
        <h3>Detailed Transactions</h3>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th>Category</th>
                    <th>Account</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $transaction)
                    <tr>
                        <td>{{ $transaction->date->format('M d, Y') }}</td>
                        <td>{{ ucfirst($transaction->type) }}</td>
                        <td>{{ $transaction->description }}</td>
                        <td>{{ $transaction->category->name ?? '-' }}</td>
                        <td>
                            @if($transaction->type === 'transfer')
                                {{ $transaction->fromAccount->name ?? '-' }} → {{ $transaction->toAccount->name ?? '-' }}
                            @else
                                {{ $transaction->account->name ?? '-' }}
                            @endif
                        </td>
                        <td class="text-right">
                            {{ $transaction->type === 'income' ? '+' : ($transaction->type === 'expense' ? '-' : '') }}Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">No transactions found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>Generated by Motrac - Money Tracker Application</p>
        <p>This is an automated report. For questions, please contact support.</p>
    </div>
</body>
</html>








