<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard Lengkap - Motrac</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        primary: '#10B981', // Emerald 500
                        secondary: '#3B82F6', // Blue 500
                        dark: '#1E293B',
                    }
                }
            }
        }
    </script>
    
    <style>
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        
        /* CSS Chart Utilities */
        /* Expense Gradient: Rose, Amber, Purple, Slate */
        .conic-expense {
            background: conic-gradient(
                #F43F5E 0% 40%, 
                #F59E0B 40% 70%, 
                #8B5CF6 70% 90%, 
                #64748B 90% 100%
            );
            border-radius: 50%;
        }
        /* Income Gradient: Emerald, Blue, Cyan */
        .conic-income {
            background: conic-gradient(
                #10B981 0% 60%, 
                #3B82F6 60% 85%, 
                #06B6D4 85% 100%
            );
            border-radius: 50%;
        }
        .hide-scroll::-webkit-scrollbar { display: none; }
        .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="font-sans text-slate-800 bg-gray-50 flex h-screen overflow-hidden">

    <!-- ================= Sidebar ================= -->
    <aside class="w-64 bg-white border-r border-gray-200 hidden md:flex flex-col z-10 transition-all duration-300">
        <!-- Logo -->
        <a href="{{ route('dashboard') }}" class="h-16 flex items-center px-6 border-b border-gray-100 hover:bg-gray-50 transition">
            <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center text-white font-bold mr-2">
                <i class="fa-solid fa-wallet"></i>
            </div>
            <span class="text-lg font-bold text-dark">Motrac</span>
        </a>

        <!-- Menu -->
        <div class="flex-1 overflow-y-auto py-6 px-4 space-y-1">
            <p class="px-2 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Menu Utama</p>
            
            <button onclick="switchView('dashboard', this)" class="nav-item w-full flex items-center gap-3 px-3 py-2.5 bg-emerald-50 text-emerald-700 rounded-lg font-medium transition text-left">
                <i class="fa-solid fa-house w-5 text-center"></i> Dashboard
            </button>
            
            <button onclick="switchView('transactions', this)" class="nav-item w-full flex items-center gap-3 px-3 py-2.5 text-gray-600 hover:bg-gray-50 hover:text-dark rounded-lg font-medium transition group text-left">
                <i class="fa-solid fa-list-ul w-5 text-center group-hover:text-primary"></i> Transaksi
            </button>
            
            <button onclick="switchView('wallets', this)" class="nav-item w-full flex items-center gap-3 px-3 py-2.5 text-gray-600 hover:bg-gray-50 hover:text-dark rounded-lg font-medium transition group text-left">
                <i class="fa-solid fa-wallet w-5 text-center group-hover:text-primary"></i> Dompet
            </button>

            <!-- Menu Kategori Baru -->
            <button onclick="switchView('categories', this)" class="nav-item w-full flex items-center gap-3 px-3 py-2.5 text-gray-600 hover:bg-gray-50 hover:text-dark rounded-lg font-medium transition group text-left">
                <i class="fa-solid fa-layer-group w-5 text-center group-hover:text-primary"></i> Kategori
            </button>
            
            <button onclick="switchView('reports', this)" class="nav-item w-full flex items-center gap-3 px-3 py-2.5 text-gray-600 hover:bg-gray-50 hover:text-dark rounded-lg font-medium transition group text-left">
                <i class="fa-solid fa-chart-pie w-5 text-center group-hover:text-primary"></i> Laporan
            </button>

            <p class="px-2 text-xs font-semibold text-gray-400 uppercase tracking-wider mt-6 mb-2">Planning</p>
            
            <button onclick="switchView('budget', this)" class="nav-item w-full flex items-center gap-3 px-3 py-2.5 text-gray-600 hover:bg-gray-50 hover:text-dark rounded-lg font-medium transition group text-left">
                <i class="fa-solid fa-bullseye w-5 text-center group-hover:text-primary"></i> Budget
            </button>
            
            <button onclick="switchView('debts', this)" class="nav-item w-full flex items-center gap-3 px-3 py-2.5 text-gray-600 hover:bg-gray-50 hover:text-dark rounded-lg font-medium transition group text-left">
                <i class="fa-solid fa-hand-holding-dollar w-5 text-center group-hover:text-primary"></i> Utang & Piutang
            </button>
        </div>

        <!-- User Footer -->
        <div class="p-4 border-t border-gray-100">
            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <button type="submit" class="flex items-center gap-2 text-sm text-red-500 hover:text-red-700 font-medium w-full px-2 py-2 rounded hover:bg-red-50 transition">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i> Keluar
                </button>
            </form>
        </div>
    </aside>

    <!-- ================= Main Content Wrapper ================= -->
    <main class="flex-1 flex flex-col h-screen overflow-hidden relative">
        
        <!-- Top Header (Sticky) -->
        <header class="bg-white h-16 border-b border-gray-200 flex-shrink-0 px-4 sm:px-8 flex items-center justify-between z-20">
            <div class="flex items-center gap-4">
                <button class="md:hidden text-gray-500 hover:text-dark"><i class="fa-solid fa-bars text-xl"></i></button>
                <div>
                    <h2 class="text-lg font-bold text-dark" id="page-title">Dashboard Overview</h2>
                    <p class="text-xs text-gray-400 hidden sm:block">Halo {{ auth()->user()->name }}, kelola keuanganmu dengan bijak.</p>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <select class="hidden sm:block bg-gray-50 border border-gray-200 text-sm rounded-lg px-3 py-1.5 focus:outline-none focus:border-primary">
                    <option>{{ \Carbon\Carbon::now()->format('F Y') }}</option>
                    <option>{{ \Carbon\Carbon::now()->subMonth()->format('F Y') }}</option>
                </select>
                <button class="w-9 h-9 rounded-full bg-gray-50 text-gray-500 hover:bg-gray-100 flex items-center justify-center transition" title="Privacy Mode" onclick="togglePrivacy(this)">
                    <i class="fa-regular fa-eye"></i>
                </button>
                <div class="relative">
                    <button class="w-9 h-9 rounded-full bg-gray-50 text-gray-500 hover:bg-gray-100 flex items-center justify-center transition">
                        <i class="fa-regular fa-bell"></i>
                    </button>
                    <span class="absolute top-0 right-0 w-2.5 h-2.5 bg-red-500 border-2 border-white rounded-full"></span>
                </div>
                <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=10B981&color=fff" class="w-9 h-9 rounded-full border border-gray-200 cursor-pointer">
            </div>
        </header>

        <!-- Scrollable Content Area -->
        <div class="flex-1 overflow-y-auto p-4 sm:p-8 space-y-8 bg-gray-50 relative">

            <!-- VIEW 1: DASHBOARD (Default) -->
            <div id="view-dashboard" class="content-section animate-fade-in">
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 relative overflow-hidden group">
                        <div class="absolute right-0 top-0 p-4 opacity-5 group-hover:opacity-10 transition"><i class="fa-solid fa-wallet text-6xl text-blue-500"></i></div>
                        <p class="text-sm text-gray-500 font-medium mb-1">Total Saldo</p>
                        <h3 class="text-2xl font-bold text-dark sensitive-data">Rp {{ number_format($totalBalance, 0, ',', '.') }}</h3>
                        <div class="flex items-center gap-1 mt-2 text-xs text-gray-400">
                            @if($monthlyIncome > 0)
                                <span class="text-emerald-500 bg-emerald-50 px-1.5 py-0.5 rounded font-semibold">
                                    +{{ number_format((($monthlyIncome - $monthlyExpense) / max($monthlyIncome, 1)) * 100, 1) }}%
                                </span>
                            @endif
                            dari bulan lalu
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <div class="flex items-center gap-3 mb-4"><div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600"><i class="fa-solid fa-arrow-down"></i></div><span class="text-sm font-medium text-gray-500">Pemasukan</span></div>
                        <h3 class="text-2xl font-bold text-emerald-600 sensitive-data">Rp {{ number_format($monthlyIncome, 0, ',', '.') }}</h3>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <div class="flex items-center gap-3 mb-4"><div class="w-10 h-10 rounded-full bg-rose-100 flex items-center justify-center text-rose-600"><i class="fa-solid fa-arrow-up"></i></div><span class="text-sm font-medium text-gray-500">Pengeluaran</span></div>
                        <h3 class="text-2xl font-bold text-rose-600 sensitive-data">Rp {{ number_format($monthlyExpense, 0, ',', '.') }}</h3>
                    </div>
                </div>

                <div class="grid lg:grid-cols-3 gap-8">
                    <!-- Cash Flow Chart -->
                    <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="font-bold text-lg text-dark" id="chart-title">
                                @if(isset($periodType))
                                    @if($periodType === 'daily')
                                        Arus Kas Harian
                                    @elseif($periodType === 'monthly')
                                        Arus Kas Bulanan
                                    @elseif($periodType === 'yearly')
                                        Arus Kas Tahunan
                                    @else
                                        Arus Kas Mingguan
                                    @endif
                                @else
                                    Arus Kas Mingguan
                                @endif
                            </h3>
                            <select id="period-selector" onchange="changePeriod(this.value)" class="bg-gray-50 border border-gray-200 text-sm rounded-lg px-3 py-1.5 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20">
                                <option value="daily" {{ (isset($periodType) && $periodType === 'daily') ? 'selected' : '' }}>Harian</option>
                                <option value="weekly" {{ (!isset($periodType) || $periodType === 'weekly') ? 'selected' : '' }}>Mingguan</option>
                                <option value="monthly" {{ (isset($periodType) && $periodType === 'monthly') ? 'selected' : '' }}>Bulanan</option>
                                <option value="yearly" {{ (isset($periodType) && $periodType === 'yearly') ? 'selected' : '' }}>Tahunan</option>
                            </select>
                        </div>
                        <div class="flex items-center justify-center py-6" id="cash-flow-chart" style="min-height: 280px; width: 100%;">
                            @php
                                $chartData = isset($cashFlowData) && is_array($cashFlowData) && count($cashFlowData) > 0 ? $cashFlowData : [];
                                
                                // Calculate total income and expense for the period
                                $totalIncome = 0;
                                $totalExpense = 0;
                                $totalNet = 0;
                                
                                foreach($chartData as $period) {
                                    $totalIncome += isset($period['income']) ? $period['income'] : 0;
                                    $totalExpense += isset($period['expense']) ? $period['expense'] : 0;
                                    $totalNet += isset($period['net']) ? $period['net'] : 0;
                                }
                                
                                $totalAmount = $totalIncome + $totalExpense;
                                $incomePercent = $totalAmount > 0 ? ($totalIncome / $totalAmount) * 100 : 0;
                                $expensePercent = $totalAmount > 0 ? ($totalExpense / $totalAmount) * 100 : 0;
                            @endphp
                            @if(count($chartData) > 0 && $totalAmount > 0)
                                <div class="flex flex-col items-center gap-8 w-full">
                                    <!-- Circle Chart -->
                                    <div class="relative" style="width: 280px; height: 280px;">
                                        <div class="relative w-full h-full" style="background: conic-gradient(
                                            #10B981 0deg {{ $incomePercent * 3.6 }}deg,
                                            #EF4444 {{ $incomePercent * 3.6 }}deg {{ ($incomePercent + $expensePercent) * 3.6 }}deg,
                                            #E5E7EB {{ ($incomePercent + $expensePercent) * 3.6 }}deg 360deg
                                        ); border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);">
                                            <div class="absolute inset-0 m-auto bg-white rounded-full flex flex-col items-center justify-center shadow-inner" style="width: 160px; height: 160px;">
                                                <span class="text-xs text-gray-400 font-medium mb-1">Net Cash Flow</span>
                                                <span class="font-bold text-lg {{ $totalNet >= 0 ? 'text-emerald-500' : 'text-rose-500' }} sensitive-data">
                                                    {{ $totalNet >= 0 ? '+' : '' }}Rp {{ number_format(abs($totalNet), 0, ',', '.') }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Legend -->
                                    <div class="flex flex-wrap justify-center gap-6 w-full max-w-md">
                                        <div class="flex items-center gap-3 bg-emerald-50 px-4 py-3 rounded-xl border border-emerald-100 flex-1 min-w-[140px]">
                                            <div class="w-5 h-5 rounded-full bg-emerald-500 shadow-sm"></div>
                                            <div class="flex-1">
                                                <p class="text-[11px] text-gray-500 font-medium mb-0.5">Pemasukan</p>
                                                <p class="font-bold text-sm text-dark sensitive-data">Rp {{ number_format($totalIncome, 0, ',', '.') }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-3 bg-rose-50 px-4 py-3 rounded-xl border border-rose-100 flex-1 min-w-[140px]">
                                            <div class="w-5 h-5 rounded-full bg-rose-500 shadow-sm"></div>
                                            <div class="flex-1">
                                                <p class="text-[11px] text-gray-500 font-medium mb-0.5">Pengeluaran</p>
                                                <p class="font-bold text-sm text-dark sensitive-data">Rp {{ number_format($totalExpense, 0, ',', '.') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <!-- Empty state -->
                                <div class="flex flex-col items-center gap-4">
                                    <div class="relative" style="width: 280px; height: 280px;">
                                        <div class="relative w-full h-full bg-gray-100 rounded-full flex items-center justify-center shadow-inner">
                                            <div class="absolute inset-0 m-auto bg-white rounded-full flex flex-col items-center justify-center" style="width: 160px; height: 160px;">
                                                <i class="fa-solid fa-chart-pie text-gray-300 text-2xl mb-2"></i>
                                                <span class="text-xs text-gray-400 font-medium">Tidak ada data</span>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-400 font-medium">Belum ada transaksi untuk periode ini</p>
                                </div>
                            @endif
                        </div>
                    </div>
                    <!-- Quick Budget -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <h3 class="font-bold text-dark mb-4">Pantauan Budget</h3>
                        <div class="space-y-6">
                            @forelse($budgets->take(2) as $budget)
                                @php
                                    $percentage = min(100, ($budget->spent / max($budget->amount, 1)) * 100);
                                    $colorClass = $percentage >= 90 ? 'bg-orange-500' : ($percentage >= 70 ? 'bg-yellow-500' : 'bg-blue-500');
                                @endphp
                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <span class="font-medium text-gray-600">{{ $budget->category->name ?? 'N/A' }}</span>
                                        <span class="text-xs {{ $percentage >= 90 ? 'text-orange-500' : ($percentage >= 70 ? 'text-yellow-500' : 'text-emerald-500') }} font-bold">{{ number_format($percentage, 0) }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-2">
                                        <div class="{{ $colorClass }} h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-gray-400 text-center">Belum ada budget</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- VIEW 2: TRANSAKSI -->
            <div id="view-transactions" class="content-section hidden">
                <div class="bg-white p-4 rounded-xl border border-gray-200 mb-6 flex flex-col sm:flex-row gap-4 justify-between items-center">
                    <div class="relative w-full sm:w-64">
                        <input type="text" placeholder="Cari transaksi..." class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-primary">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-gray-400 text-xs"></i>
                    </div>
                    <div class="flex gap-2 w-full sm:w-auto">
                        <select class="px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm"><option>Semua Kategori</option><option>Makanan</option><option>Transport</option></select>
                        <a href="{{ route('transactions.index') }}" class="bg-primary text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-emerald-600 transition"><i class="fa-solid fa-download mr-1"></i> Export</a>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                    @php
                        $groupedTransactions = $recentTransactions->groupBy(function($transaction) {
                            return $transaction->date->format('Y-m-d');
                        });
                    @endphp
                    @forelse($groupedTransactions as $date => $transactions)
                        <div class="bg-gray-50 px-6 py-3 border-b border-gray-100 flex justify-between items-center">
                            <span class="text-xs font-bold text-gray-500 uppercase">{{ $transactions->first()->date->format('d M') }}</span>
                            <span class="text-xs font-bold {{ $transactions->sum(function($t) { return $t->type === 'income' ? $t->amount : -$t->amount; }) >= 0 ? 'text-emerald-500' : 'text-rose-500' }} sensitive-data">
                                {{ $transactions->sum(function($t) { return $t->type === 'income' ? $t->amount : -$t->amount; }) >= 0 ? '+' : '' }}Rp {{ number_format(abs($transactions->sum(function($t) { return $t->type === 'income' ? $t->amount : -$t->amount; })), 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="divide-y divide-gray-100">
                            @foreach($transactions as $transaction)
                                <a href="{{ route('transactions.index') }}" class="p-4 flex items-center justify-between hover:bg-gray-50 transition cursor-pointer block">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-full {{ $transaction->type === 'income' ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600' }} flex items-center justify-center">
                                            <i class="fa-solid {{ $transaction->type === 'income' ? 'fa-arrow-down' : 'fa-arrow-up' }}"></i>
                                        </div>
                                        <div>
                                            <p class="font-medium text-dark text-sm">{{ $transaction->description }}</p>
                                            <p class="text-xs text-gray-400">Dompet: {{ $transaction->account->name ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                    <span class="font-bold {{ $transaction->type === 'income' ? 'text-emerald-500' : 'text-rose-500' }} text-sm sensitive-data">
                                        {{ $transaction->type === 'income' ? '+' : '-' }}Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    @empty
                        <div class="p-8 text-center text-gray-400">
                            <p>Belum ada transaksi</p>
                            <a href="{{ route('transactions.create') }}" class="text-primary hover:underline mt-2 inline-block">Tambah transaksi pertama</a>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- VIEW 3: DOMPET (Wallets) -->
            <div id="view-wallets" class="content-section hidden">
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Add Wallet Button -->
                    <a href="{{ route('accounts.create') }}" class="border-2 border-dashed border-gray-300 rounded-2xl p-6 flex flex-col items-center justify-center text-gray-400 hover:border-primary hover:text-primary transition h-48 bg-gray-50 hover:bg-white">
                        <i class="fa-solid fa-plus text-3xl mb-2"></i>
                        <span class="font-medium">Tambah Dompet Baru</span>
                    </a>

                    @php
                        $accounts = \App\Models\Account::where('user_id', auth()->id())->get();
                    @endphp
                    @foreach($accounts as $account)
                        @php
                            $colorMap = [
                                'bank' => ['bg' => '#3B82F6', 'text' => '#3B82F6', 'light' => '#DBEAFE'],
                                'cash' => ['bg' => '#10B981', 'text' => '#10B981', 'light' => '#D1FAE5'],
                                'ewallet' => ['bg' => '#8B5CF6', 'text' => '#8B5CF6', 'light' => '#EDE9FE'],
                            ];
                            $colors = $colorMap[$account->type] ?? $colorMap['bank'];
                            $iconMap = [
                                'bank' => 'university',
                                'cash' => 'money-bill-wave',
                                'ewallet' => 'wallet',
                            ];
                            $icon = $iconMap[$account->type] ?? 'wallet';
                        @endphp
                        <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm h-48 flex flex-col justify-between relative">
                            <div class="w-1.5 h-full absolute left-0 top-0 rounded-l-2xl" style="background-color: {{ $colors['bg'] }};"></div>
                            <div class="flex justify-between items-start pl-2">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center text-xl" style="background-color: {{ $colors['light'] }}; color: {{ $colors['text'] }};">
                                        <i class="fa-solid fa-{{ $icon }}"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-dark">{{ $account->name }}</h3>
                                        <p class="text-xs text-gray-400">{{ ucfirst($account->type) }}</p>
                                    </div>
                                </div>
                                <a href="{{ route('accounts.edit', $account->id) }}" class="text-gray-300 hover:text-dark"><i class="fa-solid fa-ellipsis-vertical"></i></a>
                            </div>
                            <div class="pl-2">
                                <p class="text-2xl font-bold text-dark sensitive-data">Rp {{ number_format($account->balance, 0, ',', '.') }}</p>
                                <p class="text-xs text-gray-400 mt-1">Updated: {{ $account->updated_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- VIEW 7: CATEGORIES -->
            <div id="view-categories" class="content-section hidden">
                <div class="flex justify-between items-end mb-6">
                    <div>
                        <h3 class="text-xl font-bold text-dark">Atur Kategori</h3>
                        <p class="text-sm text-gray-500">Sesuaikan label pengeluaran dan pemasukan Anda.</p>
                    </div>
                    <a href="{{ route('categories.index') }}" class="bg-primary hover:bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center gap-2">
                        <i class="fa-solid fa-plus"></i> Kategori Baru
                    </a>
                </div>

                <div class="grid lg:grid-cols-2 gap-8">
                    @php
                        $expenseCategories = \App\Models\Category::where('user_id', auth()->id())->where('type', 'expense')->get();
                        $incomeCategories = \App\Models\Category::where('user_id', auth()->id())->where('type', 'income')->get();
                    @endphp
                    <!-- Expense Categories -->
                    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
                        <div class="bg-rose-50 p-4 border-b border-rose-100 flex items-center justify-between">
                            <h4 class="font-bold text-rose-700 flex items-center gap-2"><i class="fa-solid fa-arrow-up"></i> Pengeluaran</h4>
                            <span class="text-xs bg-white text-rose-500 px-2 py-1 rounded font-bold">{{ $expenseCategories->count() }} Kategori</span>
                        </div>
                        <div class="p-4 grid grid-cols-2 sm:grid-cols-3 gap-3">
                            @forelse($expenseCategories->take(6) as $category)
                                @php
                                    $colorMap = ['orange', 'blue', 'purple', 'teal', 'yellow', 'gray'];
                                    $color = $category->color ?? $colorMap[($loop->index % count($colorMap))];
                                @endphp
                                <div class="flex flex-col items-center justify-center p-3 rounded-xl border border-gray-100 hover:border-rose-200 hover:bg-rose-50 cursor-pointer transition group">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center mb-2 group-hover:scale-110 transition" style="background-color: {{ $category->color ?? '#F97316' }}20; color: {{ $category->color ?? '#F97316' }};">
                                        <i class="fa-solid fa-{{ $category->icon ?? 'tag' }}"></i>
                                    </div>
                                    <span class="text-xs font-medium text-gray-600 group-hover:text-rose-700">{{ $category->name }}</span>
                                </div>
                            @empty
                                <p class="col-span-3 text-center text-gray-400 text-sm py-4">Belum ada kategori</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Income Categories -->
                    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
                        <div class="bg-emerald-50 p-4 border-b border-emerald-100 flex items-center justify-between">
                            <h4 class="font-bold text-emerald-700 flex items-center gap-2"><i class="fa-solid fa-arrow-down"></i> Pemasukan</h4>
                            <span class="text-xs bg-white text-emerald-500 px-2 py-1 rounded font-bold">{{ $incomeCategories->count() }} Kategori</span>
                        </div>
                        <div class="p-4 grid grid-cols-2 sm:grid-cols-3 gap-3">
                            @forelse($incomeCategories->take(6) as $category)
                                <div class="flex flex-col items-center justify-center p-3 rounded-xl border border-gray-100 hover:border-emerald-200 hover:bg-emerald-50 cursor-pointer transition group">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center mb-2 group-hover:scale-110 transition" style="background-color: {{ $category->color ?? '#10B981' }}20; color: {{ $category->color ?? '#10B981' }};">
                                        <i class="fa-solid fa-{{ $category->icon ?? 'tag' }}"></i>
                                    </div>
                                    <span class="text-xs font-medium text-gray-600 group-hover:text-emerald-700">{{ $category->name }}</span>
                                </div>
                            @empty
                                <p class="col-span-3 text-center text-gray-400 text-sm py-4">Belum ada kategori</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- VIEW 4: LAPORAN (Reports - UPDATED) -->
            <div id="view-reports" class="content-section hidden">
                <div class="flex flex-col space-y-6">
                    <!-- Filters & Quick Actions -->
                    <div class="bg-white p-5 rounded-xl border border-gray-200 flex flex-col lg:flex-row justify-between items-start lg:items-end gap-4 shadow-sm">
                        <div class="flex flex-wrap gap-4 w-full lg:w-auto">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Dari Tanggal</label>
                                <input type="date" value="{{ \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }}" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Sampai Tanggal</label>
                                <input type="date" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Tipe Transaksi</label>
                                <select class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5">
                                    <option selected>Semua Tipe</option>
                                    <option value="income">Pemasukan</option>
                                    <option value="expense">Pengeluaran</option>
                                    <option value="transfer">Transfer</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex gap-2 w-full lg:w-auto">
                            <a href="{{ route('reports.calendar') }}" class="flex-1 lg:flex-none flex items-center justify-center gap-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 px-4 py-2.5 rounded-lg text-sm font-medium transition">
                                <i class="fa-regular fa-calendar"></i> Kalender
                            </a>
                            <a href="{{ route('reports.export') }}" class="flex-1 lg:flex-none flex items-center justify-center gap-2 bg-emerald-50 border border-emerald-100 hover:bg-emerald-100 text-emerald-700 px-4 py-2.5 rounded-lg text-sm font-medium transition">
                                <i class="fa-solid fa-file-csv"></i> CSV
                            </a>
                            <a href="{{ route('reports.exportPdf') }}" class="flex-1 lg:flex-none flex items-center justify-center gap-2 bg-rose-50 border border-rose-100 hover:bg-rose-100 text-rose-700 px-4 py-2.5 rounded-lg text-sm font-medium transition">
                                <i class="fa-solid fa-file-pdf"></i> PDF
                            </a>
                        </div>
                    </div>

                    <!-- Summary Cards Row -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Total Income -->
                        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                            <p class="text-xs text-gray-500 font-medium uppercase mb-1">Total Income</p>
                            <h3 class="text-xl font-bold text-emerald-600 sensitive-data">Rp {{ number_format($monthlyIncome, 0, ',', '.') }}</h3>
                            <span class="text-[10px] text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded mt-1 inline-block"><i class="fa-solid fa-arrow-trend-up"></i> Current Month</span>
                        </div>
                        <!-- Total Expense -->
                        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                            <p class="text-xs text-gray-500 font-medium uppercase mb-1">Total Expense</p>
                            <h3 class="text-xl font-bold text-rose-600 sensitive-data">Rp {{ number_format($monthlyExpense, 0, ',', '.') }}</h3>
                            <span class="text-[10px] text-rose-600 bg-rose-50 px-1.5 py-0.5 rounded mt-1 inline-block"><i class="fa-solid fa-arrow-trend-down"></i> Current Month</span>
                        </div>
                        <!-- Total Transfer -->
                        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                            <p class="text-xs text-gray-500 font-medium uppercase mb-1">Total Transfer</p>
                            <h3 class="text-xl font-bold text-blue-600 sensitive-data">Rp {{ number_format($totalTransfer ?? 0, 0, ',', '.') }}</h3>
                            <span class="text-[10px] text-gray-400 mt-1 inline-block">Internal mutations</span>
                        </div>
                        <!-- Net Amount -->
                        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm bg-gradient-to-br from-emerald-50 to-white">
                            <p class="text-xs text-gray-500 font-medium uppercase mb-1">Net Amount</p>
                            <h3 class="text-xl font-bold text-dark sensitive-data">Rp {{ number_format($monthlyIncome - $monthlyExpense, 0, ',', '.') }}</h3>
                            <span class="text-[10px] {{ ($monthlyIncome - $monthlyExpense) >= 0 ? 'text-emerald-600' : 'text-rose-600' }} font-bold mt-1 inline-block">{{ ($monthlyIncome - $monthlyExpense) >= 0 ? 'Healthy Cashflow' : 'Deficit' }}</span>
                        </div>
                    </div>

                    <!-- Category Charts Row -->
                    <div class="grid lg:grid-cols-2 gap-6">
                        <!-- Income by Category -->
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col items-center">
                            <h3 class="font-bold text-dark self-start mb-6 border-l-4 border-emerald-500 pl-3">Income by Category</h3>
                            @if($incomeByCategory->count() > 0)
                                @php
                                    $totalIncomeCat = $incomeByCategory->sum('total');
                                @endphp
                                <div class="flex flex-col sm:flex-row items-center gap-8 w-full justify-center">
                                    <div class="relative w-40 h-40 conic-income shadow-lg flex-shrink-0">
                                        <div class="absolute inset-0 m-auto w-24 h-24 bg-white rounded-full flex flex-col items-center justify-center">
                                            <span class="text-[10px] text-gray-400">Total</span>
                                            <span class="font-bold text-sm text-dark sensitive-data">{{ number_format($totalIncomeCat / 1000000, 1) }} Jt</span>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 gap-3 text-sm w-full max-w-xs">
                                        @foreach($incomeByCategory->take(3) as $item)
                                            @php
                                                $percentage = $totalIncomeCat > 0 ? ($item->total / $totalIncomeCat) * 100 : 0;
                                            @endphp
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-2">
                                                    <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                                                    <span class="text-gray-600">{{ $item->category->name ?? 'N/A' }} ({{ number_format($percentage, 0) }}%)</span>
                                                </div>
                                                <span class="font-bold text-dark sensitive-data">{{ number_format($item->total / 1000000, 1) }}jt</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <p class="text-gray-400 text-sm py-8">Belum ada data pemasukan</p>
                            @endif
                        </div>

                        <!-- Expense by Category -->
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col items-center">
                            <h3 class="font-bold text-dark self-start mb-6 border-l-4 border-rose-500 pl-3">Expense by Category</h3>
                            @if($expenseByCategory->count() > 0)
                                @php
                                    $totalExpenseCat = $expenseByCategory->sum('total');
                                @endphp
                                <div class="flex flex-col sm:flex-row items-center gap-8 w-full justify-center">
                                    <div class="relative w-40 h-40 conic-expense shadow-lg flex-shrink-0">
                                        <div class="absolute inset-0 m-auto w-24 h-24 bg-white rounded-full flex flex-col items-center justify-center">
                                            <span class="text-[10px] text-gray-400">Total</span>
                                            <span class="font-bold text-sm text-dark sensitive-data">{{ number_format($totalExpenseCat / 1000000, 1) }} Jt</span>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 gap-3 text-sm w-full max-w-xs">
                                        @foreach($expenseByCategory->take(4) as $item)
                                            @php
                                                $percentage = $totalExpenseCat > 0 ? ($item->total / $totalExpenseCat) * 100 : 0;
                                            @endphp
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-2">
                                                    <span class="w-3 h-3 rounded-full bg-rose-500"></span>
                                                    <span class="text-gray-600">{{ $item->category->name ?? 'N/A' }} ({{ number_format($percentage, 0) }}%)</span>
                                                </div>
                                                <span class="font-bold text-dark sensitive-data">{{ number_format($item->total / 1000000, 1) }}jt</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <p class="text-gray-400 text-sm py-8">Belum ada data pengeluaran</p>
                            @endif
                        </div>
                    </div>

                    <!-- Detailed Transactions Table -->
                    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
                        <div class="p-4 border-b border-gray-100">
                            <h3 class="font-bold text-dark">Detailed Transactions</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-gray-50 text-xs text-gray-500 uppercase">
                                        <th class="px-6 py-3 font-medium">Tanggal</th>
                                        <th class="px-6 py-3 font-medium">Deskripsi</th>
                                        <th class="px-6 py-3 font-medium">Kategori</th>
                                        <th class="px-6 py-3 font-medium">Tipe</th>
                                        <th class="px-6 py-3 font-medium text-right">Nominal</th>
                                    </tr>
                                </thead>
                                <tbody class="text-sm divide-y divide-gray-100">
                                    @forelse($allTransactions->take(10) as $transaction)
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="px-6 py-4 text-gray-500">{{ $transaction->date->format('d M Y') }}</td>
                                            <td class="px-6 py-4 font-medium text-dark">{{ $transaction->description }}</td>
                                            <td class="px-6 py-4">
                                                <span class="px-2 py-1 rounded text-xs font-bold {{ $transaction->type === 'income' ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600' }}">
                                                    {{ $transaction->category->name ?? 'N/A' }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 {{ $transaction->type === 'income' ? 'text-emerald-500' : ($transaction->type === 'expense' ? 'text-rose-500' : 'text-blue-500') }} text-xs font-bold uppercase">
                                                {{ ucfirst($transaction->type) }}
                                            </td>
                                            <td class="px-6 py-4 text-right font-bold {{ $transaction->type === 'income' ? 'text-emerald-600' : ($transaction->type === 'expense' ? 'text-rose-600' : 'text-gray-600') }} sensitive-data">
                                                {{ $transaction->type === 'income' ? '+' : ($transaction->type === 'expense' ? '-' : '') }}Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-8 text-center text-gray-400">Belum ada transaksi</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="p-4 border-t border-gray-100 text-center">
                            <a href="{{ route('reports.index') }}" class="text-sm text-primary font-medium hover:underline">Load More Records</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- VIEW 5: BUDGET -->
            <div id="view-budget" class="content-section hidden">
                <div class="flex justify-between items-end mb-6">
                    <div>
                        <h3 class="text-xl font-bold text-dark">Budget Planner</h3>
                        <p class="text-sm text-gray-500">Sisa budget total: <span class="text-emerald-600 font-bold sensitive-data">Rp {{ number_format($totalRemainingBudget ?? 0, 0, ',', '.') }}</span></p>
                    </div>
                    <a href="{{ route('budgets.index') }}" class="bg-dark text-white px-4 py-2 rounded-lg text-sm hover:bg-gray-800 transition">+ Buat Baru</a>
                </div>

                <div class="space-y-4">
                    @forelse($budgets as $budget)
                        @php
                            $percentage = min(100, ($budget->spent / max($budget->amount, 1)) * 100);
                            $remaining = max(0, $budget->amount - $budget->spent);
                            $isCritical = $percentage >= 90;
                            $isWarning = $percentage >= 70 && $percentage < 90;
                        @endphp
                        <div class="bg-white p-5 rounded-xl border {{ $isCritical ? 'border-rose-200' : 'border-gray-200' }} shadow-sm relative overflow-hidden">
                            <div class="flex justify-between items-start mb-2 relative z-10">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background-color: {{ $budget->category->color ?? '#F97316' }}20; color: {{ $budget->category->color ?? '#F97316' }};">
                                        <i class="fa-solid fa-{{ $budget->category->icon ?? 'tag' }}"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-dark">{{ $budget->category->name ?? 'N/A' }}</h4>
                                        <p class="text-xs {{ $isCritical ? 'text-rose-500' : ($isWarning ? 'text-yellow-500' : 'text-emerald-500') }} font-bold">
                                            {{ $isCritical ? 'Overspending risk!' : ($isWarning ? 'Warning' : 'Aman terkendali') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-gray-400">Sisa</p>
                                    <p class="font-bold text-dark sensitive-data">Rp {{ number_format($remaining, 0, ',', '.') }}</p>
                                </div>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-3 mt-2 relative z-10">
                                @php
                                    $progressColor = $isCritical ? '#EF4444' : ($isWarning ? '#EAB308' : '#3B82F6');
                                @endphp
                                <div class="h-3 rounded-full flex items-center justify-end pr-2 text-[8px] text-white font-bold" style="width: {{ $percentage }}%; background-color: {{ $progressColor }};">{{ number_format($percentage, 0) }}%</div>
                            </div>
                            <p class="text-xs text-gray-400 mt-2 relative z-10">Terpakai <span class="sensitive-data">Rp {{ number_format($budget->spent, 0, ',', '.') }}</span> dari Rp {{ number_format($budget->amount, 0, ',', '.') }}</p>
                        </div>
                    @empty
                        <div class="bg-white p-8 rounded-xl border border-gray-200 text-center">
                            <p class="text-gray-400 mb-4">Belum ada budget yang dibuat</p>
                            <a href="{{ route('budgets.index') }}" class="text-primary hover:underline">Buat budget pertama</a>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- VIEW 6: UTANG (Debts) -->
            <div id="view-debts" class="content-section hidden">
                <div class="grid md:grid-cols-2 gap-6">
                    <!-- Column: Piutang (Orang berutang ke saya) -->
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                        <div class="bg-emerald-50 p-4 border-b border-emerald-100 flex justify-between items-center">
                            <h3 class="font-bold text-emerald-800">Piutang (Uang Saya)</h3>
                            <span class="bg-white text-emerald-600 text-xs px-2 py-1 rounded font-bold sensitive-data">
                                Total: Rp {{ number_format($receivables->sum('current_amount'), 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="divide-y divide-gray-100">
                            @forelse($receivables as $debt)
                                <div class="p-4 flex justify-between items-center hover:bg-gray-50">
                                    <div>
                                        <p class="font-bold text-dark">{{ $debt->contact_name }}</p>
                                        <p class="text-xs text-gray-400">
                                            Jatuh tempo: {{ $debt->due_date ? $debt->due_date->format('d M Y') : 'Tidak ditentukan' }}
                                            @if($debt->due_date && $debt->due_date->isPast())
                                                <span class="text-red-500 font-bold"> (Lewat {{ $debt->due_date->diffForHumans() }})</span>
                                            @endif
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-emerald-600 sensitive-data">Rp {{ number_format($debt->current_amount, 0, ',', '.') }}</p>
                                        <button class="text-[10px] {{ $debt->due_date && $debt->due_date->isPast() ? 'text-red-500' : 'text-blue-500' }} hover:underline font-bold">
                                            {{ $debt->due_date && $debt->due_date->isPast() ? 'Tagih!' : 'Ingatkan' }}
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <div class="p-8 text-center text-gray-400">
                                    <p>Belum ada piutang</p>
                                </div>
                            @endforelse
                        </div>
                        <a href="{{ route('debts.create') }}" class="block w-full py-3 text-sm text-gray-500 hover:text-emerald-600 border-t border-gray-100 transition text-center">+ Tambah Piutang</a>
                    </div>

                    <!-- Column: Utang (Saya berutang ke orang) -->
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                        <div class="bg-rose-50 p-4 border-b border-rose-100 flex justify-between items-center">
                            <h3 class="font-bold text-rose-800">Utang Saya</h3>
                            <span class="bg-white text-rose-600 text-xs px-2 py-1 rounded font-bold sensitive-data">
                                Total: Rp {{ number_format($payables->sum('current_amount'), 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="divide-y divide-gray-100">
                            @forelse($payables as $debt)
                                <div class="p-4 flex justify-between items-center hover:bg-gray-50">
                                    <div>
                                        <p class="font-bold text-dark">{{ $debt->contact_name }}</p>
                                        <p class="text-xs text-gray-400">
                                            {{ $debt->description ?? 'Utang' }}
                                            @if($debt->payments->count() > 0)
                                                ({{ $debt->payments->count() }} pembayaran)
                                            @endif
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-rose-600 sensitive-data">Rp {{ number_format($debt->current_amount, 0, ',', '.') }}</p>
                                        <a href="{{ route('debts.index') }}" class="text-[10px] text-gray-500 hover:underline">Bayar Cicilan</a>
                                    </div>
                                </div>
                            @empty
                                <div class="p-8 text-center text-gray-400">
                                    <p>Belum ada utang</p>
                                </div>
                            @endforelse
                        </div>
                        <a href="{{ route('debts.create') }}" class="block w-full py-3 text-sm text-gray-500 hover:text-rose-600 border-t border-gray-100 transition text-center">+ Catat Utang Baru</a>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <!-- Floating Action Button (Global) -->
    <a href="{{ route('transactions.create') }}" class="fixed bottom-8 right-8 bg-primary hover:bg-emerald-600 text-white w-14 h-14 rounded-full shadow-lg shadow-emerald-300 flex items-center justify-center text-2xl transition transform hover:scale-110 z-50">
        <i class="fa-solid fa-plus"></i>
    </a>

    <!-- JavaScript Interactions -->
    <script>
        // 1. Navigation Logic (SPA Switcher)
        function switchView(viewId, btnElement) {
            // Hide all sections
            document.querySelectorAll('.content-section').forEach(el => {
                el.classList.add('hidden');
            });
            
            // Show target section
            const targetSection = document.getElementById('view-' + viewId);
            if (targetSection) {
                targetSection.classList.remove('hidden');
            }

            // Update Sidebar Styles
            document.querySelectorAll('.nav-item').forEach(el => {
                // Reset style to inactive
                el.className = 'nav-item w-full flex items-center gap-3 px-3 py-2.5 text-gray-600 hover:bg-gray-50 hover:text-dark rounded-lg font-medium transition group text-left';
                const icon = el.querySelector('i');
                if(icon) icon.className = icon.className.replace('text-center', 'text-center group-hover:text-primary');
            });

            // Set Active Style
            if(btnElement) {
                btnElement.className = 'nav-item w-full flex items-center gap-3 px-3 py-2.5 bg-emerald-50 text-emerald-700 rounded-lg font-medium transition text-left';
                // Update Title
                const text = btnElement.innerText.trim();
                document.getElementById('page-title').innerText = text;
            }
        }

        // 2. Privacy Toggle Logic
        let isHidden = false;
        function togglePrivacy(btn) {
            const sensitiveElements = document.querySelectorAll('.sensitive-data');
            const icon = btn.querySelector('i');
            
            isHidden = !isHidden;

            if(isHidden) {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
                sensitiveElements.forEach(el => {
                    el.dataset.original = el.innerText;
                    el.innerText = '•••••••';
                    el.classList.add('blur-sm');
                });
            } else {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
                sensitiveElements.forEach(el => {
                    if(el.dataset.original) {
                        el.innerText = el.dataset.original;
                    }
                    el.classList.remove('blur-sm');
                });
            }
        }

        // 3. Change Period for Cash Flow Chart
        function changePeriod(period) {
            const titles = {
                'daily': 'Arus Kas Harian',
                'weekly': 'Arus Kas Mingguan',
                'monthly': 'Arus Kas Bulanan',
                'yearly': 'Arus Kas Tahunan'
            };
            
            document.getElementById('chart-title').innerText = titles[period] || 'Arus Kas';
            
            // Reload page with new period parameter
            const url = new URL(window.location.href);
            url.searchParams.set('period', period);
            window.location.href = url.toString();
        }
    </script>
</body>
</html>
