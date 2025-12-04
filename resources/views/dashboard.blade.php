<!DOCTYPE html>
<html lang="id" class="{{ auth()->user() && auth()->user()->dark_mode ? 'dark' : '' }}">
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
            darkMode: 'class',
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
        
        /* Ensure nominal colors are visible in dark mode */
        .dark h3.income-amount.sensitive-data,
        .dark h3.text-emerald-600.sensitive-data.income-amount,
        .dark #income-amount-card {
            color: rgb(16, 185, 129) !important; /* emerald-500 - lebih gelap dan kontras */
        }
        .dark h3.expense-amount.sensitive-data,
        .dark h3.text-rose-600.sensitive-data.expense-amount,
        .dark #expense-amount-card {
            color: rgb(225, 29, 72) !important; /* rose-500 - lebih gelap dan kontras */
        }
        
        /* Force background darker for all summary cards in dashboard */
        .dark #view-dashboard .grid.grid-cols-1.md\:grid-cols-3 .bg-white {
            background-color: rgb(55, 65, 81) !important; /* gray-700 */
            border-color: rgb(75, 85, 99) !important; /* gray-600 */
        }
        
        /* Ensure text is visible on darker background */
        .dark #view-dashboard .grid.grid-cols-1.md\:grid-cols-3 .text-gray-500 {
            color: rgb(209, 213, 219) !important; /* gray-300 */
        }
        
        /* Cash Flow Chart Cards - Force dark mode styles */
        .dark .cash-flow-income-card {
            background-color: rgb(6, 78, 59) !important; /* emerald-900 */
            border-color: rgb(5, 150, 105) !important; /* emerald-600 */
        }
        .dark .cash-flow-expense-card {
            background-color: rgb(127, 29, 29) !important; /* rose-900 */
            border-color: rgb(225, 29, 72) !important; /* rose-500 */
        }
        .dark .cash-flow-income-amount {
            color: rgb(16, 185, 129) !important; /* emerald-500 */
        }
        .dark .cash-flow-expense-amount {
            color: rgb(225, 29, 72) !important; /* rose-500 */
        }
        .dark .cash-flow-income-card p,
        .dark .cash-flow-expense-card p {
            color: rgb(209, 213, 219) !important; /* gray-300 */
        }
    </style>
</head>
<body class="font-sans text-slate-800 dark:text-gray-100 bg-gray-50 dark:bg-gray-900 flex h-screen overflow-hidden">

    <!-- ================= Sidebar ================= -->
    <aside class="w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 hidden md:flex flex-col z-10 transition-all duration-300">
        <!-- Logo -->
        <a href="{{ route('dashboard') }}" class="h-16 flex items-center px-6 border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
            <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center text-white font-bold mr-2">
                <i class="fa-solid fa-wallet"></i>
            </div>
            <span class="text-lg font-bold text-dark dark:text-white">Motrac</span>
        </a>

        <!-- Menu -->
        <div class="flex-1 overflow-y-auto py-6 px-4 space-y-1">
            <p class="px-2 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-2">Menu Utama</p>
            
            <button id="nav-dashboard" onclick="switchView('dashboard', this)" class="nav-item w-full flex items-center gap-3 px-3 py-2.5 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 rounded-lg font-medium transition text-left">
                <i class="fa-solid fa-house w-5 text-center"></i> Dashboard
            </button>
            
            <button id="nav-transactions" onclick="switchView('transactions', this)" class="nav-item w-full flex items-center gap-3 px-3 py-2.5 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-dark dark:hover:text-white rounded-lg font-medium transition group text-left">
                <i class="fa-solid fa-list-ul w-5 text-center group-hover:text-primary"></i> Transaksi
            </button>
            
            <button onclick="switchView('wallets', this)" class="nav-item w-full flex items-center gap-3 px-3 py-2.5 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-dark dark:hover:text-white rounded-lg font-medium transition group text-left">
                <i class="fa-solid fa-wallet w-5 text-center group-hover:text-primary"></i> Dompet
            </button>

            <!-- Menu Kategori Baru -->
            <button onclick="switchView('categories', this)" class="nav-item w-full flex items-center gap-3 px-3 py-2.5 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-dark dark:hover:text-white rounded-lg font-medium transition group text-left">
                <i class="fa-solid fa-layer-group w-5 text-center group-hover:text-primary"></i> Kategori
            </button>
            
            <button id="nav-reports" onclick="switchView('reports', this)" class="nav-item w-full flex items-center gap-3 px-3 py-2.5 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-dark dark:hover:text-white rounded-lg font-medium transition group text-left">
                <i class="fa-solid fa-chart-pie w-5 text-center group-hover:text-primary"></i> Laporan
            </button>

            <p class="px-2 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mt-6 mb-2">Planning</p>
            
            <button onclick="switchView('budget', this)" class="nav-item w-full flex items-center gap-3 px-3 py-2.5 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-dark dark:hover:text-white rounded-lg font-medium transition group text-left">
                <i class="fa-solid fa-bullseye w-5 text-center group-hover:text-primary"></i> Budget
            </button>
            
            <button onclick="switchView('debts', this)" class="nav-item w-full flex items-center gap-3 px-3 py-2.5 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-dark dark:hover:text-white rounded-lg font-medium transition group text-left">
                <i class="fa-solid fa-hand-holding-dollar w-5 text-center group-hover:text-primary"></i> Utang & Piutang
            </button>
        </div>

        <!-- User Footer -->
        <div class="p-4 border-t border-gray-100 dark:border-gray-700">
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
        <header class="bg-white dark:bg-gray-800 h-16 border-b border-gray-200 dark:border-gray-700 flex-shrink-0 px-4 sm:px-8 flex items-center justify-between z-20">
            <div class="flex items-center gap-4">
                <button class="md:hidden text-gray-500 dark:text-gray-300 hover:text-dark dark:hover:text-white"><i class="fa-solid fa-bars text-xl"></i></button>
                <div>
                    <h2 class="text-lg font-bold text-dark dark:text-white" id="page-title">Dashboard Overview</h2>
                    <p class="text-xs text-gray-400 dark:text-gray-400 hidden sm:block">Halo {{ auth()->user()->name }}, kelola keuanganmu dengan bijak.</p>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <input type="month" id="monthYearPicker" value="{{ request('month_year', \Carbon\Carbon::now()->format('Y-m')) }}" onchange="changeMonthYear(this.value)" class="hidden sm:block bg-white dark:bg-gray-700 text-dark dark:text-white border border-gray-200 dark:border-gray-600 text-sm rounded-lg px-3 py-1.5 focus:outline-none focus:border-primary cursor-pointer">
                <button class="w-9 h-9 rounded-full bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 flex items-center justify-center transition" title="Privacy Mode" onclick="togglePrivacy(this)">
                    <i class="fa-regular fa-eye"></i>
                </button>
                <div class="relative">
                    <button onclick="toggleNotificationDropdown()" class="w-9 h-9 rounded-full bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 flex items-center justify-center transition relative">
                        <i class="fa-regular fa-bell"></i>
                        @if(isset($unreadCount) && $unreadCount > 0)
                            <span class="absolute top-0 right-0 w-2.5 h-2.5 bg-red-500 border-2 border-white dark:border-gray-800 rounded-full"></span>
                        @endif
                    </button>
                    <!-- Notification Dropdown -->
                    <div id="notificationDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 z-50 max-h-96 overflow-y-auto">
                        <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                            <h3 class="font-bold text-dark dark:text-white">Notifikasi</h3>
                            @if(isset($unreadCount) && $unreadCount > 0)
                                <button onclick="markAllNotificationsAsRead()" class="text-xs text-primary hover:underline">Tandai semua sudah dibaca</button>
                            @endif
                        </div>
                        <div id="notificationList" class="divide-y divide-gray-200 dark:divide-gray-700">
                            @if(isset($unreadNotifications) && $unreadNotifications->count() > 0)
                                @foreach($unreadNotifications as $notification)
                                    <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer notification-item {{ $notification->read_at ? '' : 'bg-blue-50 dark:bg-blue-900/20' }}" data-notification-id="{{ $notification->id }}" onclick="markNotificationAsRead({{ $notification->id }})">
                                        <div class="flex items-start gap-3">
                                            <div class="flex-shrink-0 mt-1">
                                                @if($notification->type === 'budget')
                                                    <i class="fa-solid fa-exclamation-triangle text-orange-500"></i>
                                                @elseif($notification->type === 'security')
                                                    <i class="fa-solid fa-shield-halved text-blue-500"></i>
                                                @else
                                                    <i class="fa-regular fa-bell text-gray-500"></i>
                                                @endif
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-semibold text-dark dark:text-white">{{ $notification->title }}</p>
                                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">{{ $notification->message }}</p>
                                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                                            </div>
                                            @if(!$notification->read_at)
                                                <span class="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full mt-2"></span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="p-8 text-center text-gray-400 dark:text-gray-500">
                                    <i class="fa-regular fa-bell text-3xl mb-2"></i>
                                    <p class="text-sm">Tidak ada notifikasi</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <a href="{{ route('profile') }}">
                    <img src="{{ auth()->user()->photo ? \Illuminate\Support\Facades\Storage::url(auth()->user()->photo) : 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name) . '&background=10B981&color=fff' }}" class="w-9 h-9 rounded-full border border-gray-200 dark:border-gray-600 cursor-pointer hover:ring-2 hover:ring-primary transition object-cover" alt="Profile Photo">
                </a>
            </div>
        </header>

        <!-- Scrollable Content Area -->
        <div class="flex-1 overflow-y-auto p-4 sm:p-8 space-y-8 bg-gray-50 dark:bg-gray-900 relative">

            <!-- VIEW 1: DASHBOARD (Default) -->
            <div id="view-dashboard" class="content-section animate-fade-in">
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white dark:bg-gray-700 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-600 relative overflow-hidden group">
                        <div class="absolute right-0 top-0 p-4 opacity-5 group-hover:opacity-10 transition"><i class="fa-solid fa-wallet text-6xl text-blue-500"></i></div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium mb-1">Total Saldo</p>
                        <h3 class="text-2xl font-bold text-dark dark:text-white sensitive-data">{{ $currencySymbol }} {{ $userCurrency === 'USD' ? number_format($totalBalance, 2, '.', ',') : number_format($totalBalance, 0, ',', '.') }}</h3>
                        <div class="flex items-center gap-1 mt-2 text-xs text-gray-400">
                            @if($monthlyIncome > 0)
                                <span class="text-emerald-500 bg-emerald-50 px-1.5 py-0.5 rounded font-semibold">
                                    +{{ number_format((($monthlyIncome - $monthlyExpense) / max($monthlyIncome, 1)) * 100, 1) }}%
                                </span>
                            @endif
                            dari bulan lalu
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-700 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-600">
                        <div class="flex items-center gap-3 mb-4"><div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400"><i class="fa-solid fa-arrow-down"></i></div><span class="text-sm font-medium text-gray-500 dark:text-gray-300">Pemasukan</span></div>
                        <h3 class="text-2xl font-bold text-emerald-600 sensitive-data income-amount" id="income-amount-card" style="{{ auth()->user() && auth()->user()->dark_mode ? 'color: rgb(16, 185, 129) !important;' : '' }}">{{ $currencySymbol }} {{ $userCurrency === 'USD' ? number_format($monthlyIncome, 2, '.', ',') : number_format($monthlyIncome, 0, ',', '.') }}</h3>
                    </div>
                    <div class="bg-white dark:bg-gray-700 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-600">
                        <div class="flex items-center gap-3 mb-4"><div class="w-10 h-10 rounded-full bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center text-rose-600 dark:text-rose-400"><i class="fa-solid fa-arrow-up"></i></div><span class="text-sm font-medium text-gray-500 dark:text-gray-300">Pengeluaran</span></div>
                        <h3 class="text-2xl font-bold text-rose-600 sensitive-data expense-amount" id="expense-amount-card" style="{{ auth()->user() && auth()->user()->dark_mode ? 'color: rgb(225, 29, 72) !important;' : '' }}">{{ $currencySymbol }} {{ $userCurrency === 'USD' ? number_format($monthlyExpense, 2, '.', ',') : number_format($monthlyExpense, 0, ',', '.') }}</h3>
                    </div>
                </div>

                <div class="grid lg:grid-cols-3 gap-8">
                    <!-- Cash Flow Chart -->
                    <div class="lg:col-span-2 bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="font-bold text-lg text-dark dark:text-white" id="chart-title">
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
                            <select id="period-selector" onchange="changePeriod(this.value)" class="bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-dark dark:text-white text-sm rounded-lg px-3 py-1.5 focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20">
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
                                            <div class="absolute inset-0 m-auto bg-white dark:bg-gray-800 rounded-full flex flex-col items-center justify-center shadow-inner" style="width: 160px; height: 160px;">
                                                <span class="text-xs text-gray-400 font-medium mb-1">Net Cash Flow</span>
                                                <span class="font-bold text-lg {{ $totalNet >= 0 ? 'text-emerald-500' : 'text-rose-500' }} sensitive-data">
                                                    {{ $totalNet >= 0 ? '+' : '' }}{{ $currencySymbol }} {{ $userCurrency === 'USD' ? number_format(abs($totalNet), 2, '.', ',') : number_format(abs($totalNet), 0, ',', '.') }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Legend -->
                                    <div class="flex flex-wrap justify-center gap-6 w-full max-w-md">
                                        <div class="flex items-center gap-3 px-4 py-3 rounded-xl flex-1 min-w-[140px] cash-flow-income-card" style="{{ auth()->user() && auth()->user()->dark_mode ? 'background-color: rgb(6, 78, 59) !important; border: 1px solid rgb(5, 150, 105) !important;' : 'background-color: rgb(236, 253, 245); border: 1px solid rgb(209, 250, 229);' }}">
                                            <div class="w-5 h-5 rounded-full bg-emerald-500 shadow-sm"></div>
                                            <div class="flex-1">
                                                <p class="text-[11px] font-medium mb-0.5" style="{{ auth()->user() && auth()->user()->dark_mode ? 'color: rgb(209, 213, 219) !important;' : 'color: rgb(107, 114, 128);' }}">Pemasukan</p>
                                                <p class="font-bold text-sm sensitive-data cash-flow-income-amount" style="{{ auth()->user() && auth()->user()->dark_mode ? 'color: rgb(16, 185, 129) !important;' : 'color: rgb(17, 24, 39);' }}">{{ $currencySymbol }} {{ $userCurrency === 'USD' ? number_format($totalIncome, 2, '.', ',') : number_format($totalIncome, 0, ',', '.') }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-3 px-4 py-3 rounded-xl flex-1 min-w-[140px] cash-flow-expense-card" style="{{ auth()->user() && auth()->user()->dark_mode ? 'background-color: rgb(127, 29, 29) !important; border: 1px solid rgb(225, 29, 72) !important;' : 'background-color: rgb(255, 241, 242); border: 1px solid rgb(254, 205, 211);' }}">
                                            <div class="w-5 h-5 rounded-full bg-rose-500 shadow-sm"></div>
                                            <div class="flex-1">
                                                <p class="text-[11px] font-medium mb-0.5" style="{{ auth()->user() && auth()->user()->dark_mode ? 'color: rgb(209, 213, 219) !important;' : 'color: rgb(107, 114, 128);' }}">Pengeluaran</p>
                                                <p class="font-bold text-sm sensitive-data cash-flow-expense-amount" style="{{ auth()->user() && auth()->user()->dark_mode ? 'color: rgb(225, 29, 72) !important;' : 'color: rgb(17, 24, 39);' }}">{{ $currencySymbol }} {{ $userCurrency === 'USD' ? number_format($totalExpense, 2, '.', ',') : number_format($totalExpense, 0, ',', '.') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <!-- Empty state -->
                                <div class="flex flex-col items-center gap-4">
                                    <div class="relative" style="width: 280px; height: 280px;">
                                        <div class="relative w-full h-full bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center shadow-inner">
                                            <div class="absolute inset-0 m-auto bg-white dark:bg-gray-800 rounded-full flex flex-col items-center justify-center" style="width: 160px; height: 160px;">
                                                <i class="fa-solid fa-chart-pie text-gray-300 dark:text-gray-600 text-2xl mb-2"></i>
                                                <span class="text-xs text-gray-400 dark:text-gray-500 font-medium">Tidak ada data</span>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-400 dark:text-gray-500 font-medium">Belum ada transaksi untuk periode ini</p>
                                </div>
                            @endif
                        </div>
                    </div>
                    <!-- Quick Budget -->
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                        <h3 class="font-bold text-dark dark:text-white mb-4">Pantauan Budget</h3>
                        <div class="space-y-6">
                            @forelse($budgets->take(2) as $budget)
                                @php
                                    $available = $budget->amount + ($budget->rollover_enabled ? $budget->rollover_amount : 0);
                                    $spent = $budget->spent;
                                    $remaining = $budget->remaining;
                                    $percentage = $available > 0 ? min(100, ($spent / $available) * 100) : 0;
                                    $colorClass = $percentage >= 90 ? 'bg-orange-500' : ($percentage >= 70 ? 'bg-yellow-500' : 'bg-blue-500');
                                @endphp
                                <div>
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="font-medium text-gray-600 dark:text-gray-400 text-sm">{{ $budget->category->name ?? 'N/A' }}</span>
                                        <span class="text-xs {{ $percentage >= 90 ? 'text-orange-500' : ($percentage >= 70 ? 'text-yellow-500' : 'text-emerald-500') }} font-bold">{{ number_format($percentage, 0) }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2 mb-2">
                                        <div class="{{ $colorClass }} h-2 rounded-full transition-all" style="width: {{ $percentage }}%"></div>
                                    </div>
                                    <div class="flex justify-between items-center text-xs">
                                        <div class="flex flex-col">
                                            <span class="text-gray-400 dark:text-gray-500 mb-0.5">Digunakan</span>
                                            <span class="font-semibold text-gray-700 dark:text-gray-300 sensitive-data">{{ $currencySymbol }} {{ $userCurrency === 'USD' ? number_format($spent, 2, '.', ',') : number_format($spent, 0, ',', '.') }}</span>
                                        </div>
                                        <div class="flex flex-col text-right">
                                            <span class="text-gray-400 dark:text-gray-500 mb-0.5">Sisa</span>
                                            <span class="font-semibold {{ $remaining >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }} sensitive-data">
                                                {{ $remaining >= 0 ? '' : '-' }}{{ $currencySymbol }} {{ $userCurrency === 'USD' ? number_format(abs($remaining), 2, '.', ',') : number_format(abs($remaining), 0, ',', '.') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-gray-400 dark:text-gray-500 text-center">Belum ada budget</p>
                            @endforelse
                        </div>
                    </div>
                </div>
                
                <!-- Transaksi Harian -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-dark dark:text-white">Transaksi Hari Ini</h3>
                        <span class="text-xs text-gray-400 dark:text-gray-500">{{ \Carbon\Carbon::now()->format('d M Y') }}</span>
                    </div>
                    @if($todayTransactions->count() > 0)
                        <div class="space-y-3">
                            @foreach($todayTransactions as $transaction)
                                <div class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition cursor-pointer" onclick="openTransactionDetailModal({{ $transaction->id }})" data-transaction-id="{{ $transaction->id }}">
                                    <div class="flex items-center gap-3 flex-1">
                                        <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0
                                            @if($transaction->type === 'income') bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400
                                            @elseif($transaction->type === 'expense') bg-rose-100 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400
                                            @else bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400
                                            @endif">
                                            <i class="fa-solid 
                                                @if($transaction->type === 'income') fa-arrow-down
                                                @elseif($transaction->type === 'expense') fa-arrow-up
                                                @else fa-exchange-alt
                                                @endif text-xs"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-medium text-sm text-dark dark:text-white truncate">{{ $transaction->description }}</p>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                <span class="text-xs text-gray-400 dark:text-gray-500">
                                                    @if($transaction->type === 'transfer')
                                                        {{ $transaction->fromAccount->name ?? 'N/A' }} → {{ $transaction->toAccount->name ?? 'N/A' }}
                                                    @else
                                                        {{ $transaction->account->name ?? 'N/A' }}
                                                    @endif
                                                </span>
                                                @if($transaction->category)
                                                    <span class="text-xs text-gray-300 dark:text-gray-600">•</span>
                                                    <span class="text-xs text-gray-400 dark:text-gray-500">{{ $transaction->category->name }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex flex-col items-end ml-3">
                                        <span class="font-bold text-sm
                                            @if($transaction->type === 'income') text-emerald-600 dark:text-emerald-400
                                            @elseif($transaction->type === 'expense') text-rose-600 dark:text-rose-400
                                            @else text-blue-600 dark:text-blue-400
                                            @endif sensitive-data">
                                            @if($transaction->type === 'income')+
                                            @elseif($transaction->type === 'expense')-
                                            @endif
                                            {{ $currencySymbol }} {{ $userCurrency === 'USD' ? number_format($transaction->amount, 2, '.', ',') : number_format($transaction->amount, 0, ',', '.') }}
                                        </span>
                                        <span class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                                            {{ $transaction->created_at->format('H:i') }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @php
                            $todayTotal = $todayTransactions->sum(function($t) {
                                if ($t->type === 'income') return $t->amount;
                                if ($t->type === 'expense') return -$t->amount;
                                return 0;
                            });
                        @endphp
                        <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Hari Ini</span>
                            <span class="font-bold text-base {{ $todayTotal >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }} sensitive-data">
                                {{ $todayTotal >= 0 ? '+' : '' }}{{ $currencySymbol }} {{ $userCurrency === 'USD' ? number_format(abs($todayTotal), 2, '.', ',') : number_format(abs($todayTotal), 0, ',', '.') }}
                            </span>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="fa-solid fa-receipt text-gray-300 text-3xl mb-3"></i>
                            <p class="text-sm text-gray-400 dark:text-gray-500 font-medium">Belum ada transaksi hari ini</p>
                            <button type="button" onclick="openTransactionModal()" class="text-primary hover:underline text-xs mt-2 inline-block">
                                Tambah transaksi
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <!-- VIEW 2: TRANSAKSI -->
            <div id="view-transactions" class="content-section hidden">
                <form method="GET" action="{{ route('dashboard') }}" class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 mb-6 flex flex-col sm:flex-row gap-4 justify-between items-center">
                    <div class="relative w-full sm:w-64">
                        <input
                            type="text"
                            name="transaction_search"
                            value="{{ request('transaction_search') }}"
                            placeholder="Cari transaksi..."
                            class="w-full pl-10 pr-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-dark dark:text-white rounded-lg text-sm focus:outline-none focus:border-primary"
                        >
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-gray-400 text-xs"></i>
                    </div>
                    <div class="flex gap-2 w-full sm:w-auto">
                        <select
                            name="transaction_category_id"
                            class="px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-dark dark:text-white rounded-lg text-sm"
                        >
                            <option value="">Semua Kategori</option>
                            @foreach($filterCategories as $category)
                                <option value="{{ $category->id }}" @selected(request('transaction_category_id') == $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="bg-primary text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-emerald-600 transition flex items-center gap-2">
                            <i class="fa-solid fa-search"></i>
                            <span>Filter</span>
                        </button>
                    </div>
                </form>

                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                    @php
                        // Gunakan $allTransactions untuk tampilan daftar transaksi (agar filter bekerja)
                        $groupedTransactions = $allTransactions->groupBy(function($transaction) {
                            return $transaction->date->format('Y-m-d');
                        });
                    @endphp
                    @forelse($groupedTransactions as $date => $transactions)
                        <div class="bg-gray-50 dark:bg-gray-700 px-6 py-3 border-b border-gray-100 dark:border-gray-600 flex justify-between items-center">
                            <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase">{{ $transactions->first()->date->format('d M') }}</span>
                            <span class="text-xs font-bold {{ $transactions->sum(function($t) { return $t->type === 'income' ? $t->amount : -$t->amount; }) >= 0 ? 'text-emerald-500' : 'text-rose-500' }} sensitive-data">
                                {{ $transactions->sum(function($t) { return $t->type === 'income' ? $t->amount : -$t->amount; }) >= 0 ? '+' : '' }}{{ $currencySymbol }} {{ $userCurrency === 'USD' ? number_format(abs($transactions->sum(function($t) { return $t->type === 'income' ? $t->amount : -$t->amount; })), 2, '.', ',') : number_format(abs($transactions->sum(function($t) { return $t->type === 'income' ? $t->amount : -$t->amount; })), 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($transactions as $transaction)
                                <a href="{{ route('transactions.index') }}" class="p-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700 transition cursor-pointer block">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-full {{ $transaction->type === 'income' ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400' : 'bg-rose-100 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400' }} flex items-center justify-center">
                                            <i class="fa-solid {{ $transaction->type === 'income' ? 'fa-arrow-down' : 'fa-arrow-up' }}"></i>
                                        </div>
                                        <div>
                                            <p class="font-medium text-dark dark:text-white text-sm">{{ $transaction->description }}</p>
                                            <p class="text-xs text-gray-400 dark:text-gray-500">Dompet: {{ $transaction->account->name ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                    <span class="font-bold {{ $transaction->type === 'income' ? 'text-emerald-500 dark:text-emerald-400' : 'text-rose-500 dark:text-rose-400' }} text-sm sensitive-data">
                                        {{ $transaction->type === 'income' ? '+' : '-' }}{{ $currencySymbol }} {{ $userCurrency === 'USD' ? number_format($transaction->amount, 2, '.', ',') : number_format($transaction->amount, 0, ',', '.') }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    @empty
                        <div class="p-8 text-center text-gray-400">
                            <p>Belum ada transaksi</p>
                            <button type="button" onclick="openTransactionModal()" class="text-primary hover:underline mt-2 inline-block">
                                Tambah transaksi pertama
                            </button>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- VIEW 3: DOMPET (Wallets) -->
            <div id="view-wallets" class="content-section hidden">
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Add Wallet Button -->
                    <button type="button" onclick="openAccountModal()" class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-2xl p-6 flex flex-col items-center justify-center text-gray-400 dark:text-gray-500 hover:border-primary hover:text-primary transition h-48 bg-gray-50 dark:bg-gray-700 hover:bg-white dark:hover:bg-gray-600">
                        <i class="fa-solid fa-plus text-3xl mb-2"></i>
                        <span class="font-medium">Tambah Dompet Baru</span>
                    </button>

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
                        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm h-48 flex flex-col justify-between relative">
                            <div class="w-1.5 h-full absolute left-0 top-0 rounded-l-2xl" style="background-color: {{ $colors['bg'] }};"></div>
                            <div class="flex justify-between items-start pl-2">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center text-xl" style="background-color: {{ $colors['light'] }}; color: {{ $colors['text'] }};">
                                        <i class="fa-solid fa-{{ $icon }}"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-dark dark:text-white">{{ $account->name }}</h3>
                                        <p class="text-xs text-gray-400 dark:text-gray-500">{{ ucfirst($account->type) }}</p>
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    class="text-gray-300 dark:text-gray-500 hover:text-dark dark:hover:text-white"
                                    data-id="{{ $account->id }}"
                                    data-name="{{ e($account->name) }}"
                                    data-type="{{ $account->type }}"
                                    data-currency="{{ $account->currency }}"
                                    data-notes="{{ e($account->notes) }}"
                                    data-is-hidden="{{ $account->is_hidden ? 1 : 0 }}"
                                    data-is-active="{{ $account->is_active ? 1 : 0 }}"
                                    onclick="openAccountEditFromButton(this)"
                                >
                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                </button>
                            </div>
                            <div class="pl-2">
                                <p class="text-2xl font-bold text-dark dark:text-white sensitive-data">{{ $currencySymbol }} {{ $userCurrency === 'USD' ? number_format($account->balance, 2, '.', ',') : number_format($account->balance, 0, ',', '.') }}</p>
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
                        <h3 class="text-xl font-bold text-dark dark:text-white">Atur Kategori</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Sesuaikan label pengeluaran dan pemasukan Anda.</p>
                    </div>
                    <button type="button" onclick="openCategoryModal()" class="bg-primary hover:bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center gap-2">
                        <i class="fa-solid fa-plus"></i> Kategori Baru
                    </button>
                </div>

                <div class="grid lg:grid-cols-2 gap-8">
                    @php
                        $expenseCategories = \App\Models\Category::where('user_id', auth()->id())->where('type', 'expense')->get();
                        $incomeCategories = \App\Models\Category::where('user_id', auth()->id())->where('type', 'income')->get();
                    @endphp
                    <!-- Expense Categories -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
                        <div class="bg-rose-50 p-4 border-b border-rose-100 flex items-center justify-between">
                            <h4 class="font-bold text-rose-700 flex items-center gap-2"><i class="fa-solid fa-arrow-up"></i> Pengeluaran</h4>
                            <span class="text-xs bg-white dark:bg-gray-800 text-rose-500 dark:text-rose-400 px-2 py-1 rounded font-bold">{{ $expenseCategories->count() }} Kategori</span>
                        </div>
                        <div class="p-4 grid grid-cols-2 sm:grid-cols-3 gap-3">
                            @forelse($expenseCategories->take(6) as $category)
                                @php
                                    $colorMap = ['orange', 'blue', 'purple', 'teal', 'yellow', 'gray'];
                                    $color = $category->color ?? $colorMap[($loop->index % count($colorMap))];
                                @endphp
                                <div class="flex flex-col items-center justify-center p-3 rounded-xl border border-gray-100 dark:border-gray-700 hover:border-rose-200 dark:hover:border-rose-700 hover:bg-rose-50 dark:hover:bg-rose-900/20 cursor-pointer transition group">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center mb-2 group-hover:scale-110 transition" style="background-color: {{ $category->color ?? '#F97316' }}20; color: {{ $category->color ?? '#F97316' }};">
                                        <i class="fa-solid fa-{{ $category->icon ?? 'tag' }}"></i>
                                    </div>
                                    <span class="text-xs font-medium text-gray-600 dark:text-gray-400 group-hover:text-rose-700 dark:group-hover:text-rose-400">{{ $category->name }}</span>
                                </div>
                            @empty
                                <p class="col-span-3 text-center text-gray-400 text-sm py-4">Belum ada kategori</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Income Categories -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
                        <div class="bg-emerald-50 p-4 border-b border-emerald-100 flex items-center justify-between">
                            <h4 class="font-bold text-emerald-700 flex items-center gap-2"><i class="fa-solid fa-arrow-down"></i> Pemasukan</h4>
                            <span class="text-xs bg-white dark:bg-gray-800 text-emerald-500 dark:text-emerald-400 px-2 py-1 rounded font-bold">{{ $incomeCategories->count() }} Kategori</span>
                        </div>
                        <div class="p-4 grid grid-cols-2 sm:grid-cols-3 gap-3">
                            @forelse($incomeCategories->take(6) as $category)
                                <div class="flex flex-col items-center justify-center p-3 rounded-xl border border-gray-100 dark:border-gray-700 hover:border-emerald-200 dark:hover:border-emerald-700 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 cursor-pointer transition group">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center mb-2 group-hover:scale-110 transition" style="background-color: {{ $category->color ?? '#10B981' }}20; color: {{ $category->color ?? '#10B981' }};">
                                        <i class="fa-solid fa-{{ $category->icon ?? 'tag' }}"></i>
                                    </div>
                                    <span class="text-xs font-medium text-gray-600 dark:text-gray-400 group-hover:text-emerald-700 dark:group-hover:text-emerald-400">{{ $category->name }}</span>
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
                    <div class="bg-white dark:bg-gray-800 p-5 rounded-xl border border-gray-200 dark:border-gray-700 flex flex-col lg:flex-row justify-between items-start lg:items-end gap-4 shadow-sm">
                        <form method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap gap-4 w-full lg:w-auto items-end">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Dari Tanggal</label>
                                <input
                                    type="date"
                                    name="report_from"
                                    value="{{ $reportFrom ?? request('report_from', \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d')) }}"
                                    class="bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5"
                                >
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Sampai Tanggal</label>
                                <input
                                    type="date"
                                    name="report_to"
                                    value="{{ $reportTo ?? request('report_to', \Carbon\Carbon::now()->format('Y-m-d')) }}"
                                    class="bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5"
                                >
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Tipe Transaksi</label>
                                <select
                                    name="report_type"
                                    class="bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5"
                                >
                                    @php $rt = $reportType ?? request('report_type', 'all'); @endphp
                                    <option value="all" {{ $rt === 'all' ? 'selected' : '' }}>Semua Tipe</option>
                                    <option value="income" {{ $rt === 'income' ? 'selected' : '' }}>Pemasukan</option>
                                    <option value="expense" {{ $rt === 'expense' ? 'selected' : '' }}>Pengeluaran</option>
                                    <option value="transfer" {{ $rt === 'transfer' ? 'selected' : '' }}>Transfer</option>
                                </select>
                            </div>
                            <div class="flex items-end gap-2">
                                <button type="submit" class="bg-primary text-white px-4 py-2.5 rounded-lg text-sm font-medium hover:bg-emerald-600 transition flex items-center gap-2">
                                    <i class="fa-solid fa-filter"></i>
                                    <span>Filter</span>
                                </button>
                                <a href="{{ route('dashboard', ['view' => 'reports']) }}" class="px-4 py-2.5 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                    Clear
                                </a>
                            </div>
                        </form>
                        <div class="flex gap-2 w-full lg:w-auto">
                            @php
                                $exportDateFrom = $reportFrom ?? request('report_from', \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d'));
                                $exportDateTo = $reportTo ?? request('report_to', \Carbon\Carbon::now()->format('Y-m-d'));
                                $exportType = $reportType ?? request('report_type', 'all');
                            @endphp
                            <a href="{{ route('reports.export', ['date_from' => $exportDateFrom, 'date_to' => $exportDateTo, 'type' => $exportType]) }}" class="flex-1 lg:flex-none flex items-center justify-center gap-2 bg-emerald-50 border border-emerald-100 hover:bg-emerald-100 text-emerald-700 px-4 py-2.5 rounded-lg text-sm font-medium transition">
                                <i class="fa-solid fa-file-csv"></i> CSV
                            </a>
                            <a href="{{ route('reports.exportPdf', ['date_from' => $exportDateFrom, 'date_to' => $exportDateTo, 'type' => $exportType]) }}" class="flex-1 lg:flex-none flex items-center justify-center gap-2 bg-rose-50 border border-rose-100 hover:bg-rose-100 text-rose-700 px-4 py-2.5 rounded-lg text-sm font-medium transition">
                                <i class="fa-solid fa-file-pdf"></i> PDF
                            </a>
                        </div>
                    </div>

                    <!-- Summary Cards Row -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Total Income -->
                        <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                            <p class="text-xs text-gray-500 font-medium uppercase mb-1">Total Income</p>
                            <h3 class="text-xl font-bold text-emerald-600 dark:text-emerald-400 sensitive-data">{{ $currencySymbol }} {{ $userCurrency === 'USD' ? number_format($monthlyIncome, 2, '.', ',') : number_format($monthlyIncome, 0, ',', '.') }}</h3>
                            <span class="text-[10px] text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30 px-1.5 py-0.5 rounded mt-1 inline-block"><i class="fa-solid fa-arrow-trend-up"></i> Current Month</span>
                        </div>
                        <!-- Total Expense -->
                        <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                            <p class="text-xs text-gray-500 font-medium uppercase mb-1">Total Expense</p>
                            <h3 class="text-xl font-bold text-rose-600 dark:text-rose-400 sensitive-data">{{ $currencySymbol }} {{ $userCurrency === 'USD' ? number_format($monthlyExpense, 2, '.', ',') : number_format($monthlyExpense, 0, ',', '.') }}</h3>
                            <span class="text-[10px] text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-900/30 px-1.5 py-0.5 rounded mt-1 inline-block"><i class="fa-solid fa-arrow-trend-down"></i> Current Month</span>
                        </div>
                        <!-- Total Transfer -->
                        <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                            <p class="text-xs text-gray-500 font-medium uppercase mb-1">Total Transfer</p>
                            <h3 class="text-xl font-bold text-blue-600 sensitive-data">{{ $currencySymbol }} {{ $userCurrency === 'USD' ? number_format($totalTransfer ?? 0, 2, '.', ',') : number_format($totalTransfer ?? 0, 0, ',', '.') }}</h3>
                            <span class="text-[10px] text-gray-400 mt-1 inline-block">Internal mutations</span>
                        </div>
                        <!-- Net Amount -->
                        <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm bg-gradient-to-br from-emerald-50 dark:from-emerald-900/20 to-white dark:to-gray-800">
                            <p class="text-xs text-gray-500 font-medium uppercase mb-1">Net Amount</p>
                            <h3 class="text-xl font-bold text-dark dark:text-white sensitive-data">{{ $currencySymbol }} {{ $userCurrency === 'USD' ? number_format($monthlyIncome - $monthlyExpense, 2, '.', ',') : number_format($monthlyIncome - $monthlyExpense, 0, ',', '.') }}</h3>
                            <span class="text-[10px] {{ ($monthlyIncome - $monthlyExpense) >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }} font-bold mt-1 inline-block">{{ ($monthlyIncome - $monthlyExpense) >= 0 ? 'Healthy Cashflow' : 'Deficit' }}</span>
                        </div>
                    </div>

                    <!-- Category Charts Row -->
                    <div class="grid lg:grid-cols-2 gap-6">
                        <!-- Income by Category -->
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col items-center">
                            <h3 class="font-bold text-dark dark:text-white self-start mb-6 border-l-4 border-emerald-500 dark:border-emerald-400 pl-3">Income by Category</h3>
                            @if($incomeByCategory->count() > 0)
                                @php
                                    $totalIncomeCat = $incomeByCategory->sum('total');
                                @endphp
                                <div class="flex flex-col sm:flex-row items-center gap-8 w-full justify-center">
                                    <div class="relative w-56 h-56 conic-income shadow-lg flex-shrink-0">
                                        <div class="absolute inset-0 m-auto w-36 h-36 bg-white dark:bg-gray-800 rounded-full flex flex-col items-center justify-center px-4 py-3">
                                            <span class="text-xs text-gray-400 dark:text-gray-500 mb-1">Total</span>
                                            <span class="font-bold text-base text-dark dark:text-white sensitive-data text-center leading-tight">
                                                {{ $currencySymbol }} {{ $userCurrency === 'USD' ? number_format($totalIncomeCat, 2, '.', ',') : number_format($totalIncomeCat, 0, ',', '.') }}
                                            </span>
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
                                                    <span class="text-gray-600 dark:text-gray-400">{{ $item->category->name ?? 'N/A' }} ({{ number_format($percentage, 0) }}%)</span>
                                                </div>
                                                <span class="font-bold text-dark dark:text-white sensitive-data">
                                                    {{ $currencySymbol }} {{ $userCurrency === 'USD' ? number_format($item->total, 2, '.', ',') : number_format($item->total, 0, ',', '.') }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <p class="text-gray-400 dark:text-gray-500 text-sm py-8">Belum ada data pemasukan</p>
                            @endif
                        </div>

                        <!-- Expense by Category -->
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col items-center">
                            <h3 class="font-bold text-dark dark:text-white self-start mb-6 border-l-4 border-rose-500 dark:border-rose-400 pl-3">Expense by Category</h3>
                            @if($expenseByCategory->count() > 0)
                                @php
                                    $totalExpenseCat = $expenseByCategory->sum('total');
                                @endphp
                                <div class="flex flex-col sm:flex-row items-center gap-8 w-full justify-center">
                                    <div class="relative w-56 h-56 conic-expense shadow-lg flex-shrink-0">
                                        <div class="absolute inset-0 m-auto w-36 h-36 bg-white dark:bg-gray-800 rounded-full flex flex-col items-center justify-center px-4 py-3">
                                            <span class="text-xs text-gray-400 dark:text-gray-500 mb-1">Total</span>
                                            <span class="font-bold text-base text-dark dark:text-white sensitive-data text-center leading-tight">
                                                {{ $currencySymbol }} {{ $userCurrency === 'USD' ? number_format($totalExpenseCat, 2, '.', ',') : number_format($totalExpenseCat, 0, ',', '.') }}
                                            </span>
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
                                                    <span class="text-gray-600 dark:text-gray-400">{{ $item->category->name ?? 'N/A' }} ({{ number_format($percentage, 0) }}%)</span>
                                                </div>
                                                <span class="font-bold text-dark dark:text-white sensitive-data">
                                                    {{ $currencySymbol }} {{ $userCurrency === 'USD' ? number_format($item->total, 2, '.', ',') : number_format($item->total, 0, ',', '.') }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <p class="text-gray-400 dark:text-gray-500 text-sm py-8">Belum ada data pengeluaran</p>
                            @endif
                        </div>
                    </div>

                    <!-- Detailed Transactions Table -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
                        <div class="p-4 border-b border-gray-100 dark:border-gray-700">
                            <h3 class="font-bold text-dark dark:text-white">Detailed Transactions</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-gray-50 dark:bg-gray-700 text-xs text-gray-500 dark:text-gray-400 uppercase">
                                        <th class="px-6 py-3 font-medium">Tanggal</th>
                                        <th class="px-6 py-3 font-medium">Deskripsi</th>
                                        <th class="px-6 py-3 font-medium">Kategori</th>
                                        <th class="px-6 py-3 font-medium">Tipe</th>
                                        <th class="px-6 py-3 font-medium text-right">Nominal</th>
                                    </tr>
                                </thead>
                                <tbody class="text-sm divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                    @forelse($allTransactions->take(10) as $transaction)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition cursor-pointer" onclick="openTransactionDetailModal({{ $transaction->id }})" data-transaction-id="{{ $transaction->id }}">
                                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $transaction->date->format('d M Y') }}</td>
                                            <td class="px-6 py-4 font-medium text-dark dark:text-white">{{ $transaction->description }}</td>
                                            <td class="px-6 py-4">
                                                <span class="px-2 py-1 rounded text-xs font-bold {{ $transaction->type === 'income' ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400' : 'bg-rose-100 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400' }}">
                                                    {{ $transaction->category->name ?? 'N/A' }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 {{ $transaction->type === 'income' ? 'text-emerald-500 dark:text-emerald-400' : ($transaction->type === 'expense' ? 'text-rose-500 dark:text-rose-400' : 'text-blue-500 dark:text-blue-400') }} text-xs font-bold uppercase">
                                                {{ ucfirst($transaction->type) }}
                                            </td>
                                            <td class="px-6 py-4 text-right font-bold {{ $transaction->type === 'income' ? 'text-emerald-600 dark:text-emerald-400' : ($transaction->type === 'expense' ? 'text-rose-600 dark:text-rose-400' : 'text-gray-600 dark:text-gray-400') }} sensitive-data">
                                                {{ $transaction->type === 'income' ? '+' : ($transaction->type === 'expense' ? '-' : '') }}{{ $currencySymbol }} {{ $userCurrency === 'USD' ? number_format($transaction->amount, 2, '.', ',') : number_format($transaction->amount, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-8 text-center text-gray-400 dark:text-gray-500">Belum ada transaksi</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="p-4 border-t border-gray-100 dark:border-gray-700 space-y-3 bg-white dark:bg-gray-800">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Total Transaksi: <span class="font-bold text-dark dark:text-white">{{ number_format($totalTransactionsCount ?? $allTransactions->count(), 0, ',', '.') }}</span></span>
                                <span class="text-xs text-gray-400 dark:text-gray-500">Menampilkan {{ min(10, $allTransactions->count()) }} dari {{ number_format($totalTransactionsCount ?? $allTransactions->count(), 0, ',', '.') }} transaksi</span>
                            </div>
                            <div class="grid grid-cols-2 gap-4 pt-2 border-t border-gray-100 dark:border-gray-700">
                                <div>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 uppercase block mb-1">Total Pemasukan</span>
                                    <span class="text-lg font-bold text-emerald-600 dark:text-emerald-400 sensitive-data">
                                        +{{ $currencySymbol }} {{ $userCurrency === 'USD' ? number_format($totalDetailedIncome ?? 0, 2, '.', ',') : number_format($totalDetailedIncome ?? 0, 0, ',', '.') }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 uppercase block mb-1">Total Pengeluaran</span>
                                    <span class="text-lg font-bold text-rose-600 dark:text-rose-400 sensitive-data">
                                        -{{ $currencySymbol }} {{ $userCurrency === 'USD' ? number_format($totalDetailedExpense ?? 0, 2, '.', ',') : number_format($totalDetailedExpense ?? 0, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- VIEW 5: BUDGET -->
            <div id="view-budget" class="content-section hidden">
                <div class="flex justify-between items-end mb-6">
                    <div>
                        <h3 class="text-xl font-bold text-dark dark:text-white">Budget Planner</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Sisa budget total: <span class="text-emerald-600 dark:text-emerald-400 font-bold sensitive-data">{{ $currencySymbol }} {{ $userCurrency === 'USD' ? number_format($totalRemainingBudget ?? 0, 2, '.', ',') : number_format($totalRemainingBudget ?? 0, 0, ',', '.') }}</span></p>
                    </div>
                    <button type="button" onclick="openBudgetModal()" class="bg-dark text-white px-4 py-2 rounded-lg text-sm hover:bg-gray-800 transition">+ Buat Baru</button>
                </div>

                <div class="space-y-4">
                    @forelse($budgets as $budget)
                        @php
                            $percentage = min(100, ($budget->spent / max($budget->amount, 1)) * 100);
                            $remaining = max(0, $budget->amount - $budget->spent);
                            $isCritical = $percentage >= 90;
                            $isWarning = $percentage >= 70 && $percentage < 90;
                        @endphp
                        <div class="bg-white dark:bg-gray-800 p-5 rounded-xl border {{ $isCritical ? 'border-rose-200 dark:border-rose-800' : 'border-gray-200 dark:border-gray-700' }} shadow-sm relative overflow-hidden">
                            <div class="flex justify-between items-start mb-2 relative z-10">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background-color: {{ $budget->category->color ?? '#F97316' }}20; color: {{ $budget->category->color ?? '#F97316' }};">
                                        <i class="fa-solid fa-{{ $budget->category->icon ?? 'tag' }}"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-dark dark:text-white">{{ $budget->category->name ?? 'N/A' }}</h4>
                                        <p class="text-xs {{ $isCritical ? 'text-rose-500' : ($isWarning ? 'text-yellow-500' : 'text-emerald-500') }} font-bold">
                                            {{ $isCritical ? 'Overspending risk!' : ($isWarning ? 'Warning' : 'Aman terkendali') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="text-right">
                                        <p class="text-xs text-gray-400 dark:text-gray-500">Sisa</p>
                                        <p class="font-bold text-dark dark:text-white sensitive-data">{{ $currencySymbol }} {{ $userCurrency === 'USD' ? number_format($remaining, 2, '.', ',') : number_format($remaining, 0, ',', '.') }}</p>
                                    </div>
                                    @php
                                        $budgetData = [
                                            'category_id' => $budget->category_id,
                                            'amount' => $budget->amount,
                                            'month' => $budget->month,
                                            'year' => $budget->year,
                                            'rollover_enabled' => (bool)$budget->rollover_enabled
                                        ];
                                    @endphp
                                    <button 
                                        type="button" 
                                        onclick="openBudgetEditModal({{ $budget->id }}, this)"
                                        data-budget="{{ htmlspecialchars(json_encode($budgetData), ENT_QUOTES, 'UTF-8') }}"
                                        class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 transition p-2"
                                        title="Edit Budget">
                                        <i class="fa-solid fa-edit"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-3 mt-2 relative z-10">
                                @php
                                    $progressColor = $isCritical ? '#EF4444' : ($isWarning ? '#EAB308' : '#3B82F6');
                                @endphp
                                <div class="h-3 rounded-full flex items-center justify-end pr-2 text-[8px] text-white font-bold" style="width: {{ $percentage }}%; background-color: {{ $progressColor }};">{{ number_format($percentage, 0) }}%</div>
                            </div>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-2 relative z-10">Terpakai <span class="sensitive-data">{{ $currencySymbol }} {{ $userCurrency === 'USD' ? number_format($budget->spent, 2, '.', ',') : number_format($budget->spent, 0, ',', '.') }}</span> dari {{ $currencySymbol }} {{ $userCurrency === 'USD' ? number_format($budget->amount, 2, '.', ',') : number_format($budget->amount, 0, ',', '.') }}</p>
                        </div>
                    @empty
                        <div class="bg-white dark:bg-gray-800 p-8 rounded-xl border border-gray-200 dark:border-gray-700 text-center">
                            <p class="text-gray-400 dark:text-gray-500 mb-4">Belum ada budget yang dibuat</p>
                            <button type="button" onclick="openBudgetModal()" class="text-primary dark:text-emerald-400 hover:underline">Buat budget pertama</button>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- VIEW 6: UTANG (Debts) -->
            <div id="view-debts" class="content-section hidden">
                <div class="grid md:grid-cols-2 gap-6">
                    <!-- Column: Piutang (Orang berutang ke saya) -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                        <div class="bg-emerald-50 dark:bg-emerald-900/20 p-4 border-b border-emerald-100 dark:border-emerald-800 flex justify-between items-center">
                            <h3 class="font-bold text-emerald-800 dark:text-emerald-400">Piutang (Uang Saya)</h3>
                            <span class="bg-white dark:bg-gray-800 text-emerald-600 dark:text-emerald-400 text-xs px-2 py-1 rounded font-bold sensitive-data">
                                Total: {{ $currencySymbol }} {{ $userCurrency === 'USD' ? number_format($receivables->sum('current_amount'), 2, '.', ',') : number_format($receivables->sum('current_amount'), 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($receivables as $debt)
                                <div class="p-4 flex justify-between items-center hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <div>
                                        <p class="font-bold text-dark dark:text-white">{{ $debt->contact_name }}</p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500">
                                            Jatuh tempo: {{ $debt->due_date ? $debt->due_date->format('d M Y') : 'Tidak ditentukan' }}
                                            @if($debt->due_date && $debt->due_date->isPast())
                                                <span class="text-red-500 font-bold"> (Lewat {{ $debt->due_date->diffForHumans() }})</span>
                                            @endif
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-emerald-600 dark:text-emerald-400 sensitive-data">{{ $currencySymbol }} {{ $userCurrency === 'USD' ? number_format($debt->current_amount, 2, '.', ',') : number_format($debt->current_amount, 0, ',', '.') }}</p>
                                        <div class="flex gap-2 items-center justify-end">
                                            @if(!$debt->is_paid)
                                                <button 
                                                    type="button"
                                                    onclick="openMarkPaidModal({{ $debt->id }}, this)"
                                                    @php
                                                        $receivablePaidData = [
                                                            'contact_name' => $debt->contact_name,
                                                            'initial_amount' => $debt->initial_amount,
                                                            'account_id' => $debt->account_id,
                                                            'description' => $debt->description
                                                        ];
                                                    @endphp
                                                    data-receivable="{{ htmlspecialchars(json_encode($receivablePaidData), ENT_QUOTES, 'UTF-8') }}"
                                                    class="text-[10px] text-emerald-600 hover:underline font-bold"
                                                    title="Tandai sebagai Lunas">
                                                    <i class="fa-solid fa-check-circle"></i> Telah Lunas
                                                </button>
                                            @else
                                                <span class="text-[10px] text-emerald-600 font-bold">
                                                    <i class="fa-solid fa-check-circle"></i> Lunas
                                                </span>
                                            @endif
                                            <button 
                                                type="button"
                                                onclick="openReminderModal({{ $debt->id }}, this)"
                                                @php
                                                    $debtReminderData = [
                                                        'contact_name' => $debt->contact_name,
                                                        'current_amount' => $debt->current_amount,
                                                        'due_date' => $debt->due_date ? $debt->due_date->format('Y-m-d') : null,
                                                        'description' => $debt->description
                                                    ];
                                                @endphp
                                                data-debt="{{ htmlspecialchars(json_encode($debtReminderData), ENT_QUOTES, 'UTF-8') }}"
                                                class="text-[10px] {{ $debt->due_date && $debt->due_date->isPast() ? 'text-red-500' : 'text-blue-500' }} hover:underline font-bold">
                                                {{ $debt->due_date && $debt->due_date->isPast() ? 'Tagih!' : 'Ingatkan' }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="p-8 text-center text-gray-400 dark:text-gray-500">
                                    <p>Belum ada piutang</p>
                                </div>
                            @endforelse
                        </div>
                        <button type="button" onclick="openDebtModal('receivable')" class="block w-full py-3 text-sm text-gray-500 dark:text-gray-400 hover:text-emerald-600 dark:hover:text-emerald-400 border-t border-gray-100 dark:border-gray-700 transition text-center">+ Tambah Piutang</button>
                    </div>

                    <!-- Column: Utang (Saya berutang ke orang) -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                        <div class="bg-rose-50 dark:bg-rose-900/20 p-4 border-b border-rose-100 dark:border-rose-800 flex justify-between items-center">
                            <h3 class="font-bold text-rose-800 dark:text-rose-400">Utang Saya</h3>
                            <span class="bg-white dark:bg-gray-800 text-rose-600 dark:text-rose-400 text-xs px-2 py-1 rounded font-bold sensitive-data">
                                Total: {{ $currencySymbol }} {{ $userCurrency === 'USD' ? number_format($payables->sum('current_amount'), 2, '.', ',') : number_format($payables->sum('current_amount'), 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($payables as $debt)
                                <div class="p-4 flex justify-between items-center hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <div>
                                        <p class="font-bold text-dark dark:text-white">{{ $debt->contact_name }}</p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500">
                                            {{ $debt->description ?? 'Utang' }}
                                            @if($debt->payments->count() > 0)
                                                ({{ $debt->payments->count() }} pembayaran)
                                            @endif
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-rose-600 dark:text-rose-400 sensitive-data">{{ $currencySymbol }} {{ $userCurrency === 'USD' ? number_format($debt->current_amount, 2, '.', ',') : number_format($debt->current_amount, 0, ',', '.') }}</p>
                                        <button 
                                            type="button" 
                                            onclick="openPaymentModal({{ $debt->id }}, this)"
                                            @php
                                                $debtData = [
                                                    'contact_name' => $debt->contact_name,
                                                    'current_amount' => $debt->current_amount,
                                                    'type' => $debt->type
                                                ];
                                            @endphp
                                            data-debt="{{ htmlspecialchars(json_encode($debtData), ENT_QUOTES, 'UTF-8') }}"
                                            class="text-[10px] text-gray-500 dark:text-gray-400 hover:underline">Bayar Cicilan</button>
                                    </div>
                                </div>
                            @empty
                                <div class="p-8 text-center text-gray-400 dark:text-gray-500">
                                    <p>Belum ada utang</p>
                                </div>
                            @endforelse
                        </div>
                        <button type="button" onclick="openDebtModal('payable')" class="block w-full py-3 text-sm text-gray-500 dark:text-gray-400 hover:text-rose-600 dark:hover:text-rose-400 border-t border-gray-100 dark:border-gray-700 transition text-center">+ Catat Utang Baru</button>
                    </div>
                </div>

                <!-- History Section -->
                <div class="mt-6 grid md:grid-cols-2 gap-6">
                    <!-- History: Piutang yang sudah dibayar -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 border-b border-gray-100 dark:border-gray-600 flex justify-between items-center">
                            <h3 class="font-bold text-gray-700 dark:text-white">History Piutang (Sudah Lunas)</h3>
                            <span class="bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 text-xs px-2 py-1 rounded font-bold">
                                {{ $paidReceivables->count() }} item
                            </span>
                        </div>
                        <div class="divide-y divide-gray-100 dark:divide-gray-700 max-h-96 overflow-y-auto">
                            @forelse($paidReceivables as $debt)
                                <div class="p-4 flex justify-between items-center hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer" onclick="openDebtDetailModal({{ $debt->id }})">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <p class="font-bold text-dark dark:text-white">{{ $debt->contact_name }}</p>
                                            <span class="bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 text-[10px] px-2 py-0.5 rounded font-bold">
                                                <i class="fa-solid fa-check-circle"></i> Lunas
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                            Jumlah: <span class="font-semibold text-emerald-600 dark:text-emerald-400 sensitive-data">{{ $currencySymbol }} {{ $userCurrency === 'USD' ? number_format($debt->initial_amount, 2, '.', ',') : number_format($debt->initial_amount, 0, ',', '.') }}</span>
                                        </p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500">
                                            Dibayar: {{ $debt->paid_at ? $debt->paid_at->format('d M Y') : '-' }}
                                        </p>
                                        @if($debt->description)
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 italic">{{ \Illuminate\Support\Str::limit($debt->description, 50) }}</p>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="p-8 text-center text-gray-400 dark:text-gray-500">
                                    <i class="fa-solid fa-history text-3xl mb-2"></i>
                                    <p>Belum ada history piutang yang sudah lunas</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- History: Utang yang sudah dibayar -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 border-b border-gray-100 dark:border-gray-600 flex justify-between items-center">
                            <h3 class="font-bold text-gray-700 dark:text-white">History Utang (Sudah Lunas)</h3>
                            <span class="bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 text-xs px-2 py-1 rounded font-bold">
                                {{ $paidPayables->count() }} item
                            </span>
                        </div>
                        <div class="divide-y divide-gray-100 dark:divide-gray-700 max-h-96 overflow-y-auto">
                            @forelse($paidPayables as $debt)
                                <div class="p-4 flex justify-between items-center hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer" onclick="openDebtDetailModal({{ $debt->id }})">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <p class="font-bold text-dark dark:text-white">{{ $debt->contact_name }}</p>
                                            <span class="bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 text-[10px] px-2 py-0.5 rounded font-bold">
                                                <i class="fa-solid fa-check-circle"></i> Lunas
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                            Jumlah: <span class="font-semibold text-rose-600 dark:text-rose-400 sensitive-data">{{ $currencySymbol }} {{ $userCurrency === 'USD' ? number_format($debt->initial_amount, 2, '.', ',') : number_format($debt->initial_amount, 0, ',', '.') }}</span>
                                        </p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500">
                                            Dibayar: {{ $debt->paid_at ? $debt->paid_at->format('d M Y') : '-' }}
                                        </p>
                                        @if($debt->description)
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 italic">{{ \Illuminate\Support\Str::limit($debt->description, 50) }}</p>
                                        @endif
                                        @if($debt->payments->count() > 0)
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                <i class="fa-solid fa-receipt"></i> {{ $debt->payments->count() }} pembayaran
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="p-8 text-center text-gray-400 dark:text-gray-500">
                                    <i class="fa-solid fa-history text-3xl mb-2"></i>
                                    <p>Belum ada history utang yang sudah lunas</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <!-- Floating Action Button (Global - hanya untuk view selain Dompet) -->
    <button
        id="add-transaction-fab"
        type="button"
        onclick="openTransactionModal()"
        class="fixed bottom-8 right-8 bg-primary hover:bg-emerald-600 text-white w-14 h-14 rounded-full shadow-lg shadow-emerald-300 flex items-center justify-center text-2xl transition transform hover:scale-110 z-50">
        <i class="fa-solid fa-plus"></i>
    </button>

    <!-- JavaScript Interactions -->
    <script>
        // Function to change month and year
        function changeMonthYear(monthYear) {
            if (!monthYear) return;
            
            // Get current URL and parameters
            const url = new URL(window.location.href);
            url.searchParams.set('month_year', monthYear);
            
            // Remove report filters if they exist (month_year takes precedence)
            url.searchParams.delete('report_from');
            url.searchParams.delete('report_to');
            
            // Reload page with new month_year parameter
            window.location.href = url.toString();
        }

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
                el.className = 'nav-item w-full flex items-center gap-3 px-3 py-2.5 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-dark dark:hover:text-white rounded-lg font-medium transition group text-left';
                const icon = el.querySelector('i');
                if(icon) icon.className = icon.className.replace('text-center', 'text-center group-hover:text-primary');
            });

            // Set Active Style
            if(btnElement) {
                btnElement.className = 'nav-item w-full flex items-center gap-3 px-3 py-2.5 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 rounded-lg font-medium transition text-left';
                // Update Title
                const text = btnElement.innerText.trim();
                document.getElementById('page-title').innerText = text;
            }

            // Tampilkan / sembunyikan tombol FAB tambah transaksi
            const fab = document.getElementById('add-transaction-fab');
            if (fab) {
                // Sembunyikan pada view Dompet, Kategori, Budget, dan Debts
                if (viewId === 'wallets' || viewId === 'categories' || viewId === 'budget' || viewId === 'debts') {
                    fab.classList.add('hidden');
                } else {
                    fab.classList.remove('hidden');
                }
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
            // Hapus parameter yang khusus untuk filter transaksi agar tidak memaksa view Transaksi
            url.searchParams.delete('transaction_search');
            url.searchParams.delete('transaction_category_id');
            window.location.href = url.toString();
        }

        // Currency settings from server
        const userCurrency = '{{ $userCurrency ?? "IDR" }}';
        const currencySymbol = '{{ $currencySymbol ?? "Rp" }}';
        
        // Dark mode settings
        const isDarkMode = {{ auth()->user() && auth()->user()->dark_mode ? 'true' : 'false' }};
        
        // Apply dark mode on page load (ensure it's applied even if HTML class is set)
        if (isDarkMode) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
        
        // Apply dark mode colors to nominal amounts
        function applyDarkModeColors() {
            const isDark = document.documentElement.classList.contains('dark');
            const incomeEl = document.getElementById('income-amount-card');
            const expenseEl = document.getElementById('expense-amount-card');
            
            if (isDark) {
                // Income amount - emerald-500 (lebih gelap dan kontras)
                if (incomeEl) {
                    incomeEl.style.cssText = 'color: rgb(16, 185, 129) !important;';
                }
                // Expense amount - rose-500 (lebih gelap dan kontras)
                if (expenseEl) {
                    expenseEl.style.cssText = 'color: rgb(225, 29, 72) !important;';
                }
                // Also apply to all elements with these classes
                document.querySelectorAll('h3.income-amount.sensitive-data').forEach(el => {
                    el.style.cssText = 'color: rgb(16, 185, 129) !important;';
                });
                document.querySelectorAll('h3.expense-amount.sensitive-data').forEach(el => {
                    el.style.cssText = 'color: rgb(225, 29, 72) !important;';
                });
                
                // Force darker background for cards
                document.querySelectorAll('.bg-white.dark\\:bg-gray-800').forEach(card => {
                    if (card.closest('#view-dashboard')) {
                        const summaryCards = card.closest('.grid.grid-cols-1');
                        if (summaryCards) {
                            card.style.cssText += 'background-color: rgb(55, 65, 81) !important; border-color: rgb(75, 85, 99) !important;';
                        }
                    }
                });
                
                // Apply dark mode styles to cash flow chart cards
                document.querySelectorAll('.cash-flow-income-card').forEach(card => {
                    card.style.cssText = 'background-color: rgb(6, 78, 59) !important; border: 1px solid rgb(5, 150, 105) !important;';
                });
                document.querySelectorAll('.cash-flow-expense-card').forEach(card => {
                    card.style.cssText = 'background-color: rgb(127, 29, 29) !important; border: 1px solid rgb(225, 29, 72) !important;';
                });
                document.querySelectorAll('.cash-flow-income-amount').forEach(el => {
                    el.style.cssText = 'color: rgb(16, 185, 129) !important;';
                });
                document.querySelectorAll('.cash-flow-expense-amount').forEach(el => {
                    el.style.cssText = 'color: rgb(225, 29, 72) !important;';
                });
                document.querySelectorAll('.cash-flow-income-card p.text-\\[11px\\]').forEach(el => {
                    el.style.cssText = 'color: rgb(209, 213, 219) !important;';
                });
                document.querySelectorAll('.cash-flow-expense-card p.text-\\[11px\\]').forEach(el => {
                    el.style.cssText = 'color: rgb(209, 213, 219) !important;';
                });
            } else {
                // Reset to default colors
                if (incomeEl) {
                    incomeEl.style.cssText = '';
                }
                if (expenseEl) {
                    expenseEl.style.cssText = '';
                }
                document.querySelectorAll('h3.income-amount.sensitive-data').forEach(el => {
                    el.style.cssText = '';
                });
                document.querySelectorAll('h3.expense-amount.sensitive-data').forEach(el => {
                    el.style.cssText = '';
                });
                
                // Reset card backgrounds
                document.querySelectorAll('.bg-white.dark\\:bg-gray-800').forEach(card => {
                    card.style.cssText = card.style.cssText.replace(/background-color[^;]+!important;?/g, '');
                    card.style.cssText = card.style.cssText.replace(/border-color[^;]+!important;?/g, '');
                });
            }
        }
        
        // Apply immediately and multiple times to ensure it works
        function forceApplyDarkMode() {
            applyDarkModeColors();
            setTimeout(applyDarkModeColors, 50);
            setTimeout(applyDarkModeColors, 200);
            setTimeout(applyDarkModeColors, 500);
        }
        
        // Apply on load
        forceApplyDarkMode();
        
        // Also apply after DOM is fully loaded
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', forceApplyDarkMode);
        } else {
            forceApplyDarkMode();
        }
        
        // Watch for dark mode changes
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                    forceApplyDarkMode();
                }
            });
        });
        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class']
        });
        
        // Also watch for when view switches to dashboard
        const originalSwitchView = window.switchView;
        if (originalSwitchView) {
            window.switchView = function(...args) {
                const result = originalSwitchView.apply(this, args);
                setTimeout(forceApplyDarkMode, 100);
                return result;
            };
        }
        
        // Notification Functions
        function toggleNotificationDropdown() {
            const dropdown = document.getElementById('notificationDropdown');
            if (dropdown) {
                dropdown.classList.toggle('hidden');
                
                // Close dropdown when clicking outside
                if (!dropdown.classList.contains('hidden')) {
                    setTimeout(() => {
                        document.addEventListener('click', function closeDropdown(e) {
                            if (!dropdown.contains(e.target) && !e.target.closest('button[onclick="toggleNotificationDropdown()"]')) {
                                dropdown.classList.add('hidden');
                                document.removeEventListener('click', closeDropdown);
                            }
                        });
                    }, 100);
                }
            }
        }

        async function markNotificationAsRead(notificationId) {
            try {
                const response = await fetch(`/notifications/${notificationId}/read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    // Remove unread styling
                    const notificationItem = document.querySelector(`[data-notification-id="${notificationId}"]`);
                    if (notificationItem) {
                        notificationItem.classList.remove('bg-blue-50', 'dark:bg-blue-900/20');
                        const unreadDot = notificationItem.querySelector('.bg-blue-500');
                        if (unreadDot) {
                            unreadDot.remove();
                        }
                    }
                    
                    // Update unread count
                    loadNotifications();
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        async function markAllNotificationsAsRead() {
            try {
                const response = await fetch('/notifications/read-all', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    // Remove unread styling from all notifications
                    document.querySelectorAll('.notification-item').forEach(item => {
                        item.classList.remove('bg-blue-50', 'dark:bg-blue-900/20');
                        const unreadDot = item.querySelector('.bg-blue-500');
                        if (unreadDot) {
                            unreadDot.remove();
                        }
                    });
                    
                    // Hide "Tandai semua sudah dibaca" button
                    const markAllButton = document.querySelector('button[onclick="markAllNotificationsAsRead()"]');
                    if (markAllButton) {
                        markAllButton.style.display = 'none';
                    }
                    
                    // Remove badge from notification button
                    const notificationButton = document.querySelector('button[onclick="toggleNotificationDropdown()"]');
                    if (notificationButton) {
                        const badge = notificationButton.querySelector('.bg-red-500');
                        if (badge) {
                            badge.remove();
                        }
                    }
                    
                    // Reload notifications to get fresh data
                    loadNotifications();
                } else {
                    console.error('Error:', data.message || 'Gagal menandai semua notifikasi sebagai sudah dibaca');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menandai semua notifikasi sebagai sudah dibaca');
            }
        }

        async function loadNotifications() {
            try {
                const response = await fetch('/notifications', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    const notificationList = document.getElementById('notificationList');
                    const notificationButton = document.querySelector('button[onclick="toggleNotificationDropdown()"]');
                    
                    if (notificationList) {
                        if (data.notifications && data.notifications.length > 0) {
                            notificationList.innerHTML = data.notifications.map(notif => {
                                const createdDate = new Date(notif.created_at);
                                const timeAgo = getTimeAgo(createdDate);
                                
                                return `
                                <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer notification-item ${!notif.read_at ? 'bg-blue-50 dark:bg-blue-900/20' : ''}" data-notification-id="${notif.id}" onclick="markNotificationAsRead(${notif.id})">
                                    <div class="flex items-start gap-3">
                                        <div class="flex-shrink-0 mt-1">
                                            ${notif.type === 'budget' ? '<i class="fa-solid fa-exclamation-triangle text-orange-500"></i>' : 
                                              notif.type === 'security' ? '<i class="fa-solid fa-shield-halved text-blue-500"></i>' : 
                                              '<i class="fa-regular fa-bell text-gray-500"></i>'}
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-semibold text-dark dark:text-white">${notif.title}</p>
                                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">${notif.message}</p>
                                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">${timeAgo}</p>
                                        </div>
                                        ${!notif.read_at ? '<span class="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full mt-2"></span>' : ''}
                                    </div>
                                </div>
                            `;
                            }).join('');
                        } else {
                            notificationList.innerHTML = `
                                <div class="p-8 text-center text-gray-400 dark:text-gray-500">
                                    <i class="fa-regular fa-bell text-3xl mb-2"></i>
                                    <p class="text-sm">Tidak ada notifikasi</p>
                                </div>
                            `;
                        }
                    }
                    
                    // Update badge count
                    if (notificationButton) {
                        let badge = notificationButton.querySelector('.bg-red-500');
                        if (data.unread_count > 0) {
                            if (!badge) {
                                badge = document.createElement('span');
                                badge.className = 'absolute top-0 right-0 w-2.5 h-2.5 bg-red-500 border-2 border-white dark:border-gray-800 rounded-full';
                                notificationButton.appendChild(badge);
                            }
                        } else if (badge) {
                            badge.remove();
                        }
                    }
                }
            } catch (error) {
                console.error('Error loading notifications:', error);
            }
        }

        function getTimeAgo(date) {
            const now = new Date();
            const diff = Math.floor((now - date) / 1000);
            
            if (diff < 60) return 'Baru saja';
            if (diff < 3600) return `${Math.floor(diff / 60)} menit yang lalu`;
            if (diff < 86400) return `${Math.floor(diff / 3600)} jam yang lalu`;
            if (diff < 604800) return `${Math.floor(diff / 86400)} hari yang lalu`;
            return date.toLocaleDateString('id-ID');
        }

        // Load notifications on page load
        if (document.getElementById('notificationDropdown')) {
            loadNotifications();
            // Refresh notifications every 30 seconds
            setInterval(loadNotifications, 30000);
        }

        // Helper function to format currency in JavaScript
        function formatCurrencyJS(amount) {
            if (userCurrency === 'USD') {
                return currencySymbol + ' ' + parseFloat(amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            } else {
                return currencySymbol + ' ' + parseFloat(amount).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
            }
        }

        // 4. Transaction Modal Functions
        let accountsData = [];
        let categoriesData = [];

        async function openTransactionModal() {
            const modal = document.getElementById('transactionModal');
            modal.classList.remove('hidden');
            
            // Reset form
            document.getElementById('transactionForm').reset();
            document.getElementById('modal_date').value = new Date().toISOString().split('T')[0];
            clearErrors();
            
            // Load form data if not already loaded
            if (accountsData.length === 0) {
                try {
                    const response = await fetch('{{ route("transactions.formData") }}', {
                        headers: {
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin'
                    });

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        console.error('Failed loading form data:', data);
                        alert(data.message || 'Gagal memuat data form. Silakan refresh halaman.');
                        return;
                    }

                    accountsData = Array.isArray(data.accounts) ? data.accounts : [];
                    categoriesData = Array.isArray(data.categories) ? data.categories : [];
                    populateFormSelects();
                } catch (error) {
                    console.error('Error loading form data:', error);
                    alert('Gagal memuat data form. Silakan refresh halaman.');
                }
            } else {
                // Re-populate if already loaded
                populateFormSelects();
            }
        }

        function closeTransactionModal() {
            const modal = document.getElementById('transactionModal');
            modal.classList.add('hidden');
            document.getElementById('transactionForm').reset();
            document.getElementById('singleAccountField').style.display = 'block';
            document.getElementById('transferAccountsField').style.display = 'none';
            clearErrors();
        }

        // 12. Mark Paid Modal Functions (Tandai Piutang sebagai Lunas)
        function openMarkPaidModal(receivableId, buttonElement) {
            const modal = document.getElementById('markPaidModal');
            const form = document.getElementById('markPaidForm');
            
            if (!modal || !form) {
                console.error('Mark paid modal elements not found');
                return;
            }
            
            // Reset form first
            form.reset();
            clearErrors();
            
            // Parse receivable data from data attribute
            let receivableData;
            try {
                const receivableDataStr = buttonElement.dataset.receivable || buttonElement.getAttribute('data-receivable');
                if (!receivableDataStr) {
                    console.error('Receivable data not found in button element');
                    alert('Terjadi kesalahan saat memuat data piutang');
                    return;
                }
                
                // Decode HTML entities if present
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = receivableDataStr;
                const decodedStr = tempDiv.textContent || tempDiv.innerText || receivableDataStr;
                receivableData = JSON.parse(decodedStr.trim().replace(/\s+/g, ' '));
            } catch (e) {
                console.error('Error parsing receivable data:', e);
                alert('Terjadi kesalahan saat memuat data piutang');
                return;
            }
            
            // Use setTimeout to ensure form.reset() has completed
            setTimeout(() => {
                // Populate form with receivable data
                document.getElementById('mark_paid_debt_id').value = receivableId;
                document.getElementById('mark_paid_contact_name').textContent = receivableData.contact_name || 'N/A';
                document.getElementById('mark_paid_amount').textContent = formatCurrencyJS(receivableData.initial_amount || 0);
                
                // Set account if available
                if (receivableData.account_id) {
                    document.getElementById('mark_paid_account_id').value = receivableData.account_id;
                }
            }, 0);
            
            // Show modal
            modal.classList.remove('hidden');
        }

        function closeMarkPaidModal() {
            const modal = document.getElementById('markPaidModal');
            modal.classList.add('hidden');
            clearErrors();
        }

        async function submitMarkPaidForm(e) {
            e.preventDefault();
            clearErrors();

            const form = e.target;
            const formData = new FormData(form);
            const debtId = document.getElementById('mark_paid_debt_id').value;
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...';

            try {
                const response = await fetch(`/debts/${debtId}/mark-paid`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData,
                });

                let data;
                try {
                    data = await response.json();
                } catch (e) {
                    console.error('Error parsing response:', e);
                    alert('Terjadi kesalahan saat memproses respons dari server.');
                    return;
                }

                if (response.ok && data.success) {
                    const notification = document.createElement('div');
                    notification.className = 'fixed top-4 right-4 bg-emerald-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center gap-2';
                    notification.innerHTML = `<i class="fa-solid fa-check-circle"></i> ${data.message}`;
                    document.body.appendChild(notification);

                    setTimeout(() => {
                        notification.remove();
                        closeMarkPaidModal();
                        // Reload dengan parameter view=debts agar tetap di halaman utang/piutang
                        const currentUrl = new URL(window.location.href);
                        currentUrl.searchParams.set('view', 'debts');
                        window.location.href = currentUrl.toString();
                    }, 1500);
                } else {
                    // Handle validation errors
                    if (data.errors) {
                        Object.keys(data.errors).forEach(field => {
                            const errorMessage = Array.isArray(data.errors[field]) ? data.errors[field][0] : data.errors[field];
                            showError(field, errorMessage);
                        });
                    } else {
                        alert(data.message || 'Gagal menandai piutang sebagai lunas');
                    }
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan. Silakan coba lagi.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        }

        function populateFormSelects() {
            // Populate accounts
            const accountSelect = document.getElementById('modal_account_id');
            const fromAccountSelect = document.getElementById('modal_from_account_id');
            const toAccountSelect = document.getElementById('modal_to_account_id');
            
            [accountSelect, fromAccountSelect, toAccountSelect].forEach(select => {
                if (select) {
                    select.innerHTML = '<option value="">Pilih akun</option>';
                    accountsData.forEach(account => {
                        const option = document.createElement('option');
                        option.value = account.id;
                        option.textContent = account.name;
                        select.appendChild(option);
                    });
                }
            });

            // Populate categories
            const categorySelect = document.getElementById('modal_category_id');
            if (categorySelect) {
                categorySelect.innerHTML = '<option value="">Pilih kategori</option>';
                categoriesData.forEach(category => {
                    const option = document.createElement('option');
                    option.value = category.id;
                    option.textContent = category.name;
                    categorySelect.appendChild(option);
                });
            }
        }

        function handleTransactionTypeChange() {
            const type = document.getElementById('modal_transactionType').value;
            const singleAccountField = document.getElementById('singleAccountField');
            const transferAccountsField = document.getElementById('transferAccountsField');
            
            if (type === 'transfer') {
                singleAccountField.style.display = 'none';
                transferAccountsField.style.display = 'block';
                document.getElementById('modal_account_id').removeAttribute('required');
                document.getElementById('modal_from_account_id').setAttribute('required', 'required');
                document.getElementById('modal_to_account_id').setAttribute('required', 'required');
            } else {
                singleAccountField.style.display = 'block';
                transferAccountsField.style.display = 'none';
                document.getElementById('modal_account_id').setAttribute('required', 'required');
                document.getElementById('modal_from_account_id').removeAttribute('required');
                document.getElementById('modal_to_account_id').removeAttribute('required');
            }
        }

        function clearErrors() {
            document.querySelectorAll('.error-message').forEach(el => el.remove());
            document.querySelectorAll('.border-red-500').forEach(el => {
                el.classList.remove('border-red-500');
                el.classList.add('border-gray-200');
            });
        }

        function showError(field, message) {
            // Map field names to actual input IDs
            const fieldMap = {
                // Transaction modal
                'modal_account_id': 'modal_account_id',
                'modal_from_account_id': 'modal_from_account_id',
                'modal_to_account_id': 'modal_to_account_id',
                'modal_category_id': 'modal_category_id',
                'modal_amount': 'modal_amount',
                'modal_description': 'modal_description',
                'modal_date': 'modal_date',
                // Budget modal
                'category_id': 'budget_category_id',
                'amount': 'budget_amount',
                'month': 'budget_month',
                'year': 'budget_year',
                'modal_notes': 'modal_notes',
                'modal_receipt': 'modal_receipt',
                'modal_type': 'modal_transactionType',
                // Account modal
                'name': 'account_name',
                'type': 'account_type',
                'initial_balance': 'account_initial_balance',
                'currency': 'account_currency',
                'notes': 'account_notes',
                'is_hidden': 'account_is_hidden',
                'is_active': 'account_is_active',
                // Category modal
                'name': 'category_name',
                'type': 'category_type',
                'icon': 'category_icon',
                'color': 'category_color',
                'parent_id': 'category_parent_id',
                // Debt modal
                'contact_name': 'debt_contact_name',
                'contact_phone': 'debt_contact_phone',
                'contact_email': 'debt_contact_email',
                'initial_amount': 'debt_initial_amount',
                'due_date': 'debt_due_date',
                'account_id': 'debt_account_id',
                'description': 'debt_description',
                // Payment modal
                'amount': 'payment_amount',
                'payment_date': 'payment_payment_date',
                'notes': 'payment_notes',
                'account_id': 'payment_account_id',
                // Reminder modal
                'reminder_date': 'reminder_reminder_date',
                'reminder_time': 'reminder_reminder_time',
                'notes': 'reminder_notes',
                // Mark Paid modal
                'account_id': 'mark_paid_account_id',
            };
            
            const actualFieldId = fieldMap[field] || field;
            const input = document.getElementById(actualFieldId);
            if (input) {
                input.classList.remove('border-gray-200');
                input.classList.add('border-red-500');
                // Remove existing error message
                const existingError = input.parentElement.querySelector('.error-message');
                if (existingError) {
                    existingError.remove();
                }
                const errorDiv = document.createElement('div');
                errorDiv.className = 'error-message text-red-500 text-xs mt-1';
                errorDiv.textContent = message;
                input.parentElement.appendChild(errorDiv);
            }
        }

        async function submitTransactionForm(e) {
            e.preventDefault();
            clearErrors();

            const form = e.target;
            const formData = new FormData(form);
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';

            try {
                const response = await fetch('{{ route("transactions.store") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    // Success - show notification
                    const notification = document.createElement('div');
                    notification.className = 'fixed top-4 right-4 bg-emerald-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center gap-2';
                    notification.innerHTML = '<i class="fa-solid fa-check-circle"></i> Transaksi berhasil ditambahkan!';
                    document.body.appendChild(notification);
                    
                    setTimeout(() => {
                        notification.remove();
                        closeTransactionModal();
                        // Reload page to show new transaction
                        window.location.reload();
                    }, 1500);
                } else {
                    // Show validation errors
                    if (data.errors) {
                        Object.keys(data.errors).forEach(field => {
                            // Map field names to modal field IDs
                            let fieldName = 'modal_' + field;
                            // Handle nested fields (e.g., split_transactions.0.category_id)
                            if (field.includes('.')) {
                                const parts = field.split('.');
                                fieldName = 'modal_' + parts[parts.length - 1];
                            }
                            const errorMessage = Array.isArray(data.errors[field]) ? data.errors[field][0] : data.errors[field];
                            showError(fieldName, errorMessage);
                        });
                    } else {
                        alert(data.message || 'Gagal menyimpan transaksi');
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan. Silakan coba lagi.');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        }

        // 5. Account (Wallet) Modal Functions
        let editingAccountId = null;

        function openAccountEditFromButton(btn) {
            const account = {
                id: btn.dataset.id,
                name: btn.dataset.name || '',
                type: btn.dataset.type || 'cash',
                currency: btn.dataset.currency || 'IDR',
                notes: btn.dataset.notes || '',
                is_hidden: btn.dataset.isHidden === '1',
                is_active: btn.dataset.isActive === '1',
            };
            openAccountModal(account);
        }

        function openAccountModal(account = null) {
            const modal = document.getElementById('accountModal');
            const form = document.getElementById('accountForm');
            const title = document.getElementById('accountModalTitle');

            modal.classList.remove('hidden');
            form.reset();
            clearErrors();

            if (account && account.id) {
                // Edit mode
                editingAccountId = account.id;
                title.innerText = 'Edit Dompet';
                document.getElementById('account_name').value = account.name || '';
                document.getElementById('account_type').value = account.type || 'cash';
                document.getElementById('account_currency').value = account.currency || 'IDR';
                document.getElementById('account_initial_balance').value = '';
                document.getElementById('account_notes').value = account.notes || '';
                const hiddenCheckbox = document.getElementById('account_is_hidden');
                const activeCheckbox = document.getElementById('account_is_active');
                if (hiddenCheckbox) hiddenCheckbox.checked = !!account.is_hidden;
                if (activeCheckbox) activeCheckbox.checked = !!account.is_active;
            } else {
                // Create mode
                editingAccountId = null;
                title.innerText = 'Tambah Dompet Baru';
                document.getElementById('account_currency').value = 'IDR';
                document.getElementById('account_initial_balance').value = 0;
                const hiddenCheckbox = document.getElementById('account_is_hidden');
                const activeCheckbox = document.getElementById('account_is_active');
                if (hiddenCheckbox) hiddenCheckbox.checked = false;
                if (activeCheckbox) activeCheckbox.checked = true;
            }
        }

        function closeAccountModal() {
            const modal = document.getElementById('accountModal');
            modal.classList.add('hidden');
            clearErrors();
            editingAccountId = null;
        }

        async function submitAccountForm(e) {
            e.preventDefault();
            clearErrors();

            const form = e.target;
            const formData = new FormData(form);
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';

            try {
                let url = '{{ route("accounts.store") }}';
                if (editingAccountId) {
                    url = '{{ url("accounts") }}/' + editingAccountId;
                    formData.append('_method', 'PUT');
                }

                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData,
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    const notification = document.createElement('div');
                    notification.className = 'fixed top-4 right-4 bg-emerald-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center gap-2';
                    notification.innerHTML = '<i class="fa-solid fa-check-circle"></i> ' + (editingAccountId ? 'Dompet berhasil diperbarui!' : 'Dompet berhasil ditambahkan!');
                    document.body.appendChild(notification);

                    setTimeout(() => {
                        notification.remove();
                        closeAccountModal();
                        window.location.reload();
                    }, 1500);
                } else if (data.errors) {
                    Object.keys(data.errors).forEach(field => {
                        const errorMessage = Array.isArray(data.errors[field]) ? data.errors[field][0] : data.errors[field];
                        showError(field, errorMessage);
                    });
                } else {
                    alert(data.message || 'Gagal menyimpan dompet');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan. Silakan coba lagi.');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        }

        // 6. Category Modal Functions (Add Category via AJAX)
        function openCategoryModal() {
            const modal = document.getElementById('categoryModal');
            const form = document.getElementById('categoryForm');
            modal.classList.remove('hidden');
            form.reset();
            clearErrors();
            document.getElementById('category_type').value = 'expense';
        }

        function closeCategoryModal() {
            const modal = document.getElementById('categoryModal');
            modal.classList.add('hidden');
            clearErrors();
        }

        async function submitCategoryForm(e) {
            e.preventDefault();
            clearErrors();

            const form = e.target;
            const formData = new FormData(form);
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';

            try {
                const response = await fetch('{{ route("categories.store") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData,
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    const notification = document.createElement('div');
                    notification.className = 'fixed top-4 right-4 bg-emerald-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center gap-2';
                    notification.innerHTML = '<i class="fa-solid fa-check-circle"></i> Kategori berhasil ditambahkan!';
                    document.body.appendChild(notification);

                    setTimeout(() => {
                        notification.remove();
                        closeCategoryModal();
                        window.location.reload();
                    }, 1500);
                } else if (data.errors) {
                    Object.keys(data.errors).forEach(field => {
                        const errorMessage = Array.isArray(data.errors[field]) ? data.errors[field][0] : data.errors[field];
                        showError(field, errorMessage);
                    });
                } else {
                    alert(data.message || 'Gagal menyimpan kategori');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan. Silakan coba lagi.');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        }

        // 7. Transaction Detail Modal Functions
        async function openTransactionDetailModal(transactionId) {
            const modal = document.getElementById('transactionDetailModal');
            const content = document.getElementById('transactionDetailContent');
            
            modal.classList.remove('hidden');
            content.innerHTML = '<div class="flex items-center justify-center py-8"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div></div>';

            try {
                const response = await fetch(`/transactions/${transactionId}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const data = await response.json();

                if (response.ok && data.success && data.transaction) {
                    const t = data.transaction;
                    const typeLabels = {
                        'income': 'Pemasukan',
                        'expense': 'Pengeluaran',
                        'transfer': 'Transfer'
                    };
                    const typeLabel = typeLabels[t.type] || t.type;
                    
                    // Get color classes based on type
                    let badgeClass = 'px-3 py-1 rounded-full text-xs font-bold uppercase ';
                    let amountClass = 'text-xl font-bold sensitive-data mt-1 ';
                    let splitAmountClass = 'text-sm font-bold sensitive-data ';
                    
                    if (t.type === 'income') {
                        badgeClass += 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400';
                        amountClass += 'text-emerald-600 dark:text-emerald-400';
                        splitAmountClass += 'text-emerald-600 dark:text-emerald-400';
                    } else if (t.type === 'expense') {
                        badgeClass += 'bg-rose-100 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400';
                        amountClass += 'text-rose-600 dark:text-rose-400';
                        splitAmountClass += 'text-rose-600 dark:text-rose-400';
                    } else {
                        badgeClass += 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400';
                        amountClass += 'text-blue-600 dark:text-blue-400';
                        splitAmountClass += 'text-blue-600 dark:text-blue-400';
                    }

                    let html = `
                        <div class="space-y-6">
                            <div class="flex items-center justify-between pb-4 border-b border-gray-200 dark:border-gray-700">
                                <div>
                                    <h4 class="text-lg font-bold text-dark dark:text-white">${t.description || '-'}</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">${new Date(t.date).toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}</p>
                                </div>
                                <span class="${badgeClass}">${typeLabel}</span>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-xs text-gray-500 dark:text-gray-400 uppercase">Jumlah</label>
                                    <p class="${amountClass}">
                                        ${t.type === 'income' ? '+' : (t.type === 'expense' ? '-' : '')}${formatCurrencyJS(t.amount)}
                                    </p>
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500 dark:text-gray-400 uppercase">Kategori</label>
                                    <p class="text-sm font-medium text-dark dark:text-white mt-1">${t.category ? t.category.name : 'N/A'}</p>
                                </div>
                            </div>

                            ${t.type === 'transfer' ? `
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-xs text-gray-500 dark:text-gray-400 uppercase">Dari Akun</label>
                                        <p class="text-sm font-medium text-dark dark:text-white mt-1">${t.from_account ? t.from_account.name : 'N/A'}</p>
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-500 dark:text-gray-400 uppercase">Ke Akun</label>
                                        <p class="text-sm font-medium text-dark dark:text-white mt-1">${t.to_account ? t.to_account.name : 'N/A'}</p>
                                    </div>
                                </div>
                            ` : `
                                <div>
                                    <label class="text-xs text-gray-500 dark:text-gray-400 uppercase">Akun</label>
                                    <p class="text-sm font-medium text-dark dark:text-white mt-1">${t.account ? t.account.name : 'N/A'}</p>
                                </div>
                            `}

                            ${t.notes ? `
                                <div>
                                    <label class="text-xs text-gray-500 dark:text-gray-400 uppercase">Catatan</label>
                                    <p class="text-sm text-gray-700 dark:text-gray-300 mt-1 whitespace-pre-wrap">${t.notes}</p>
                                </div>
                            ` : ''}

                            ${t.receipt_path ? `
                                <div>
                                    <label class="text-xs text-gray-500 dark:text-gray-400 uppercase">Foto Struk</label>
                                    <div class="mt-2">
                                        <img src="/storage/${t.receipt_path}" alt="Receipt" class="max-w-full h-auto rounded-lg border border-gray-200 dark:border-gray-700">
                                    </div>
                                </div>
                            ` : ''}

                            ${t.is_split && t.split_transactions && t.split_transactions.length > 0 ? `
                                <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                                    <label class="text-xs text-gray-500 dark:text-gray-400 uppercase mb-3 block">Transaksi Terpisah</label>
                                    <div class="space-y-2">
                                        ${t.split_transactions.map(st => `
                                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                                <div>
                                                    <p class="text-sm font-medium text-dark dark:text-white">${st.description || '-'}</p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">${st.category ? st.category.name : 'N/A'}</p>
                                                </div>
                                                <p class="${splitAmountClass}">
                                                    ${formatCurrencyJS(st.amount)}
                                                </p>
                                            </div>
                                        `).join('')}
                                    </div>
                                </div>
                            ` : ''}
                        </div>
                    `;

                    content.innerHTML = html;
                } else {
                    content.innerHTML = '<div class="text-center py-8 text-red-500 dark:text-red-400">Gagal memuat detail transaksi</div>';
                }
            } catch (error) {
                console.error('Error:', error);
                content.innerHTML = '<div class="text-center py-8 text-red-500 dark:text-red-400">Terjadi kesalahan saat memuat detail transaksi</div>';
            }
        }

        function closeTransactionDetailModal() {
            const modal = document.getElementById('transactionDetailModal');
            modal.classList.add('hidden');
        }

        // 7.5. Debt Detail Modal Functions
        async function openDebtDetailModal(debtId) {
            const modal = document.getElementById('debtDetailModal');
            const content = document.getElementById('debtDetailContent');
            
            modal.classList.remove('hidden');
            content.innerHTML = '<div class="flex items-center justify-center py-8"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div></div>';

            try {
                const response = await fetch(`/debts/${debtId}/detail`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    const debt = data.debt;
                    const isReceivable = debt.type === 'receivable';
                    const typeLabel = isReceivable ? 'Piutang' : 'Utang';
                    const typeColor = isReceivable ? 'emerald' : 'rose';
                    
                    let html = `
                        <div class="space-y-6">
                            <!-- Header Info -->
                            <div class="bg-${typeColor}-50 dark:bg-${typeColor === 'emerald' ? 'emerald-900/30' : 'rose-900/30'} p-4 rounded-lg border border-${typeColor}-100 dark:border-${typeColor}-800">
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="font-bold text-${typeColor}-800 dark:text-${typeColor}-400 text-lg">${typeLabel}</h4>
                                    <span class="bg-${typeColor}-100 dark:bg-${typeColor}-900/30 text-${typeColor}-700 dark:text-${typeColor}-400 text-xs px-3 py-1 rounded font-bold">
                                        <i class="fa-solid fa-check-circle"></i> ${debt.is_paid ? 'Lunas' : 'Belum Lunas'}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-300">Kontak: <span class="font-bold text-dark dark:text-white">${debt.contact_name || 'N/A'}</span></p>
                            </div>

                            <!-- Amount Info -->
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Jumlah Awal</p>
                                    <p class="font-bold text-lg text-dark dark:text-white sensitive-data">${formatCurrencyJS(debt.initial_amount || 0)}</p>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Sisa ${typeLabel}</p>
                                    <p class="font-bold text-lg text-${typeColor}-600 dark:text-${typeColor}-400 sensitive-data">${formatCurrencyJS(debt.current_amount || 0)}</p>
                                </div>
                            </div>

                            <!-- Contact Info -->
                            <div class="bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                                <h5 class="font-bold text-dark dark:text-white mb-3 flex items-center gap-2">
                                    <i class="fa-solid fa-user text-gray-400 dark:text-gray-500"></i> Informasi Kontak
                                </h5>
                                <div class="space-y-2 text-sm">
                                    <p><span class="text-gray-500 dark:text-gray-400">Nama:</span> <span class="font-semibold text-dark dark:text-white">${debt.contact_name || 'N/A'}</span></p>
                                    ${debt.contact_phone ? `<p><span class="text-gray-500 dark:text-gray-400">Telepon:</span> <span class="font-semibold text-dark dark:text-white">${debt.contact_phone}</span></p>` : ''}
                                    ${debt.contact_email ? `<p><span class="text-gray-500 dark:text-gray-400">Email:</span> <span class="font-semibold text-dark dark:text-white">${debt.contact_email}</span></p>` : ''}
                                </div>
                            </div>

                            <!-- Dates Info -->
                            <div class="grid grid-cols-2 gap-4">
                                ${debt.due_date ? `
                                <div class="bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Jatuh Tempo</p>
                                    <p class="font-semibold text-dark dark:text-white">${new Date(debt.due_date).toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}</p>
                                </div>
                                ` : ''}
                                ${debt.paid_at ? `
                                <div class="bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Tanggal Lunas</p>
                                    <p class="font-semibold text-emerald-600 dark:text-emerald-400">${new Date(debt.paid_at).toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}</p>
                                </div>
                                ` : ''}
                            </div>

                            <!-- Account Info -->
                            ${debt.account ? `
                            <div class="bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                                <h5 class="font-bold text-dark dark:text-white mb-2 flex items-center gap-2">
                                    <i class="fa-solid fa-wallet text-gray-400 dark:text-gray-500"></i> Akun Terkait
                                </h5>
                                <p class="text-sm text-dark dark:text-white">${debt.account.name}</p>
                            </div>
                            ` : ''}

                            <!-- Description -->
                            ${debt.description ? `
                            <div class="bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                                <h5 class="font-bold text-dark dark:text-white mb-2 flex items-center gap-2">
                                    <i class="fa-solid fa-file-lines text-gray-400 dark:text-gray-500"></i> Deskripsi
                                </h5>
                                <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">${debt.description}</p>
                            </div>
                            ` : ''}

                            <!-- Payment History -->
                            ${debt.payments && debt.payments.length > 0 ? `
                            <div class="bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                                <h5 class="font-bold text-dark dark:text-white mb-3 flex items-center gap-2">
                                    <i class="fa-solid fa-receipt text-gray-400 dark:text-gray-500"></i> Riwayat Pembayaran (${debt.payments.length})
                                </h5>
                                <div class="space-y-3">
                                    ${debt.payments.map((payment, index) => `
                                        <div class="bg-gray-50 dark:bg-gray-600 p-3 rounded-lg border border-gray-100 dark:border-gray-500">
                                            <div class="flex justify-between items-start mb-2">
                                                <div>
                                                    <p class="font-semibold text-dark dark:text-white">Pembayaran #${index + 1}</p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">${new Date(payment.payment_date).toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}</p>
                                                </div>
                                                <p class="font-bold text-${typeColor}-600 dark:text-${typeColor}-400 sensitive-data">${formatCurrencyJS(payment.amount || 0)}</p>
                                            </div>
                                            ${payment.notes ? `<p class="text-xs text-gray-600 dark:text-gray-300 mt-2 italic">${payment.notes}</p>` : ''}
                                            ${payment.transaction ? `
                                                <p class="text-xs text-emerald-600 dark:text-emerald-400 mt-2">
                                                    <i class="fa-solid fa-check-circle"></i> Transaksi terkait: ${payment.transaction.description || 'N/A'}
                                                </p>
                                            ` : ''}
                                        </div>
                                    `).join('')}
                                </div>
                            </div>
                            ` : ''}
                        </div>
                    `;
                    
                    content.innerHTML = html;
                } else {
                    content.innerHTML = `
                        <div class="text-center py-8">
                            <i class="fa-solid fa-exclamation-circle text-red-500 dark:text-red-400 text-3xl mb-3"></i>
                            <p class="text-gray-600 dark:text-gray-300">Gagal memuat detail utang/piutang</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">${data.message || 'Terjadi kesalahan'}</p>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Error:', error);
                content.innerHTML = `
                    <div class="text-center py-8">
                        <i class="fa-solid fa-exclamation-circle text-red-500 dark:text-red-400 text-3xl mb-3"></i>
                        <p class="text-gray-600 dark:text-gray-300">Terjadi kesalahan saat memuat data</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Silakan coba lagi</p>
                    </div>
                `;
            }
        }

        function closeDebtDetailModal() {
            const modal = document.getElementById('debtDetailModal');
            modal.classList.add('hidden');
        }

        // 8. Budget Modal Functions
        function openBudgetModal() {
            const modal = document.getElementById('budgetModal');
            const form = document.getElementById('budgetForm');
            const modalTitle = document.getElementById('budgetModalTitle');
            
            // Reset form first
            form.reset();
            clearErrors();
            
            // Reset budget_id for new budget
            setTimeout(() => {
                document.getElementById('budget_id').value = '';
                modalTitle.textContent = 'Tambah Budget Baru';
                // Set default month and year
                document.getElementById('budget_month').value = new Date().getMonth() + 1;
                document.getElementById('budget_year').value = new Date().getFullYear();
            }, 0);
            
            modal.classList.remove('hidden');
        }

        function openBudgetEditModal(budgetId, buttonElement) {
            const modal = document.getElementById('budgetModal');
            const form = document.getElementById('budgetForm');
            const modalTitle = document.getElementById('budgetModalTitle');
            
            if (!modal || !form || !modalTitle) {
                console.error('Modal elements not found');
                return;
            }
            
            // Reset form first
            form.reset();
            clearErrors();
            
            // Parse budget data from data attribute
            let budgetData;
            try {
                // Try dataset first, then getAttribute as fallback
                let budgetDataStr = buttonElement.dataset.budget || buttonElement.getAttribute('data-budget');
                if (!budgetDataStr) {
                    console.error('Budget data not found in button element');
                    alert('Terjadi kesalahan saat memuat data budget');
                    return;
                }
                
                // Decode HTML entities if present
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = budgetDataStr;
                budgetDataStr = tempDiv.textContent || tempDiv.innerText || budgetDataStr;
                
                // Remove any whitespace and line breaks
                budgetDataStr = budgetDataStr.trim().replace(/\s+/g, ' ');
                
                budgetData = JSON.parse(budgetDataStr);
            } catch (e) {
                console.error('Error parsing budget data:', e);
                console.error('Data string:', buttonElement.getAttribute('data-budget'));
                alert('Terjadi kesalahan saat memuat data budget');
                return;
            }
            
            // Set modal title
            modalTitle.textContent = 'Edit Budget';
            
            // Use setTimeout to ensure form.reset() has completed
            setTimeout(() => {
                // Populate form with budget data AFTER reset
                const budgetIdInput = document.getElementById('budget_id');
                const categorySelect = document.getElementById('budget_category_id');
                const amountInput = document.getElementById('budget_amount');
                const monthSelect = document.getElementById('budget_month');
                const yearInput = document.getElementById('budget_year');
                const rolloverCheckbox = document.getElementById('budget_rollover_enabled');
                
                if (budgetIdInput) budgetIdInput.value = budgetId || '';
                if (categorySelect) categorySelect.value = budgetData.category_id || '';
                if (amountInput) amountInput.value = budgetData.amount || '';
                if (monthSelect) monthSelect.value = budgetData.month || new Date().getMonth() + 1;
                if (yearInput) yearInput.value = budgetData.year || new Date().getFullYear();
                if (rolloverCheckbox) rolloverCheckbox.checked = budgetData.rollover_enabled || false;
            }, 0);
            
            // Show modal
            modal.classList.remove('hidden');
        }

        function closeBudgetModal() {
            const modal = document.getElementById('budgetModal');
            modal.classList.add('hidden');
            clearErrors();
        }

        // 9. Debt Modal Functions (Piutang/Utang)
        function openDebtModal(type) {
            const modal = document.getElementById('debtModal');
            const form = document.getElementById('debtForm');
            const modalTitle = document.getElementById('debtModalTitle');
            const typeInput = document.getElementById('debt_type');
            
            modal.classList.remove('hidden');
            form.reset();
            clearErrors();
            
            // Set type and title based on type
            typeInput.value = type;
            if (type === 'receivable') {
                modalTitle.textContent = 'Tambah Piutang';
            } else {
                modalTitle.textContent = 'Catat Utang Baru';
            }
        }

        function closeDebtModal() {
            const modal = document.getElementById('debtModal');
            modal.classList.add('hidden');
            clearErrors();
        }

        // 10. Payment Modal Functions (Bayar Cicilan)
        function openPaymentModal(debtId, buttonElement) {
            const modal = document.getElementById('paymentModal');
            const form = document.getElementById('paymentForm');
            const modalTitle = document.getElementById('paymentModalTitle');
            
            if (!modal || !form || !modalTitle) {
                console.error('Payment modal elements not found');
                return;
            }
            
            // Reset form first
            form.reset();
            clearErrors();
            
            // Parse debt data from data attribute
            let debtData;
            try {
                const debtDataStr = buttonElement.dataset.debt || buttonElement.getAttribute('data-debt');
                if (!debtDataStr) {
                    console.error('Debt data not found in button element');
                    alert('Terjadi kesalahan saat memuat data utang');
                    return;
                }
                
                // Decode HTML entities if present
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = debtDataStr;
                const decodedStr = tempDiv.textContent || tempDiv.innerText || debtDataStr;
                debtData = JSON.parse(decodedStr.trim().replace(/\s+/g, ' '));
            } catch (e) {
                console.error('Error parsing debt data:', e);
                console.error('Data string:', buttonElement.getAttribute('data-debt'));
                alert('Terjadi kesalahan saat memuat data utang');
                return;
            }
            
            // Set modal title
            modalTitle.textContent = 'Bayar Cicilan';
            
            // Use setTimeout to ensure form.reset() has completed
            setTimeout(() => {
                // Populate form with debt data
                document.getElementById('payment_debt_id').value = debtId;
                document.getElementById('payment_contact_name').textContent = debtData.contact_name || 'N/A';
                document.getElementById('payment_remaining_amount').textContent = formatCurrencyJS(debtData.current_amount || 0);
                document.getElementById('payment_amount').max = debtData.current_amount || 0;
                document.getElementById('payment_payment_date').value = new Date().toISOString().split('T')[0];
                document.getElementById('payment_create_transaction').checked = false;
                document.getElementById('payment_account_field').style.display = 'none';
            }, 0);
            
            // Show modal
            modal.classList.remove('hidden');
        }

        function closePaymentModal() {
            const modal = document.getElementById('paymentModal');
            modal.classList.add('hidden');
            clearErrors();
        }

        function togglePaymentAccountField() {
            const checkbox = document.getElementById('payment_create_transaction');
            const accountField = document.getElementById('payment_account_field');
            const accountSelect = document.getElementById('payment_account_id');
            
            if (checkbox.checked) {
                accountField.style.display = 'block';
                accountSelect.required = true;
            } else {
                accountField.style.display = 'none';
                accountSelect.required = false;
                accountSelect.value = '';
            }
        }

        async function submitPaymentForm(e) {
            e.preventDefault();
            clearErrors();

            const form = e.target;
            const formData = new FormData(form);
            const debtId = document.getElementById('payment_debt_id').value;
            
            // Remove debt_id from formData as it's not needed (already in URL)
            formData.delete('debt_id');
            
            // Handle create_transaction checkbox - if not checked, don't send it
            const createTransaction = document.getElementById('payment_create_transaction').checked;
            if (!createTransaction) {
                formData.delete('create_transaction');
                formData.delete('account_id'); // Remove account_id if checkbox not checked
            } else {
                formData.append('create_transaction', '1');
            }
            
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';

            try {
                const response = await fetch(`/debts/${debtId}/payment`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData,
                });

                let data;
                try {
                    data = await response.json();
                } catch (e) {
                    console.error('Error parsing response:', e);
                    alert('Terjadi kesalahan saat memproses respons dari server.');
                    return;
                }

                if (response.ok && data.success) {
                    const notification = document.createElement('div');
                    notification.className = 'fixed top-4 right-4 bg-emerald-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center gap-2';
                    notification.innerHTML = `<i class="fa-solid fa-check-circle"></i> ${data.message}`;
                    document.body.appendChild(notification);

                    setTimeout(() => {
                        notification.remove();
                        closePaymentModal();
                        // Reload dengan parameter view=debts agar tetap di halaman utang/piutang
                        const currentUrl = new URL(window.location.href);
                        currentUrl.searchParams.set('view', 'debts');
                        window.location.href = currentUrl.toString();
                    }, 1500);
                } else {
                    // Handle validation errors
                    if (data.errors) {
                        Object.keys(data.errors).forEach(field => {
                            const errorMessage = Array.isArray(data.errors[field]) ? data.errors[field][0] : data.errors[field];
                            showError(field, errorMessage);
                        });
                    } else {
                        alert(data.message || 'Gagal mencatat pembayaran');
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan. Silakan coba lagi.');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        }

        async function submitDebtForm(e) {
            e.preventDefault();
            clearErrors();

            const form = e.target;
            const formData = new FormData(form);
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';

            try {
                const response = await fetch('{{ route("debts.store") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData,
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    const notification = document.createElement('div');
                    notification.className = 'fixed top-4 right-4 bg-emerald-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center gap-2';
                    notification.innerHTML = `<i class="fa-solid fa-check-circle"></i> ${data.message}`;
                    document.body.appendChild(notification);

                    setTimeout(() => {
                        notification.remove();
                        closeDebtModal();
                        // Reload dengan parameter view=debts agar tetap di halaman utang/piutang
                        const currentUrl = new URL(window.location.href);
                        currentUrl.searchParams.set('view', 'debts');
                        window.location.href = currentUrl.toString();
                    }, 1500);
                } else if (data.errors) {
                    Object.keys(data.errors).forEach(field => {
                        const errorMessage = Array.isArray(data.errors[field]) ? data.errors[field][0] : data.errors[field];
                        showError(field, errorMessage);
                    });
                } else {
                    alert(data.message || 'Gagal menyimpan data');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan. Silakan coba lagi.');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        }

        async function submitBudgetForm(e) {
            e.preventDefault();
            clearErrors();

            const form = e.target;
            const formData = new FormData(form);
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';

            try {
                const budgetId = document.getElementById('budget_id').value;
                const isEdit = budgetId && budgetId !== '';
                const url = isEdit 
                    ? `/budgets/${budgetId}`
                    : '{{ route("budgets.store") }}';
                
                // For PUT method, Laravel expects _method in form data
                if (isEdit) {
                    formData.append('_method', 'PUT');
                }

                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData,
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    const notification = document.createElement('div');
                    notification.className = 'fixed top-4 right-4 bg-emerald-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center gap-2';
                    notification.innerHTML = `<i class="fa-solid fa-check-circle"></i> Budget berhasil ${isEdit ? 'diperbarui' : 'dibuat'}!`;
                    document.body.appendChild(notification);

                    setTimeout(() => {
                        notification.remove();
                        closeBudgetModal();
                        // Reload dengan parameter view=budget agar tetap di halaman budget
                        const currentUrl = new URL(window.location.href);
                        currentUrl.searchParams.set('view', 'budget');
                        window.location.href = currentUrl.toString();
                    }, 1500);
                } else if (data.errors) {
                    Object.keys(data.errors).forEach(field => {
                        const errorMessage = Array.isArray(data.errors[field]) ? data.errors[field][0] : data.errors[field];
                        showError(field, errorMessage);
                    });
                } else {
                    alert(data.message || 'Gagal menyimpan budget');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan. Silakan coba lagi.');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        }

        // 11. Reminder Modal Functions (Ingatkan Piutang)
        function openReminderModal(debtId, buttonElement) {
            const modal = document.getElementById('reminderModal');
            const form = document.getElementById('reminderForm');
            
            if (!modal || !form) {
                console.error('Reminder modal elements not found');
                return;
            }
            
            // Reset form first
            form.reset();
            clearErrors();
            
            // Parse debt data from data attribute
            let debtData;
            try {
                const debtDataStr = buttonElement.dataset.debt || buttonElement.getAttribute('data-debt');
                if (!debtDataStr) {
                    console.error('Debt data not found in button element');
                    alert('Terjadi kesalahan saat memuat data piutang');
                    return;
                }
                
                // Decode HTML entities if present
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = debtDataStr;
                const decodedStr = tempDiv.textContent || tempDiv.innerText || debtDataStr;
                debtData = JSON.parse(decodedStr.trim().replace(/\s+/g, ' '));
            } catch (e) {
                console.error('Error parsing debt data:', e);
                console.error('Data string:', buttonElement.getAttribute('data-debt'));
                alert('Terjadi kesalahan saat memuat data piutang');
                return;
            }
            
            // Use setTimeout to ensure form.reset() has completed
            setTimeout(() => {
                // Populate form with debt data
                document.getElementById('reminder_debt_id').value = debtId;
                document.getElementById('reminder_contact_name').textContent = debtData.contact_name || 'N/A';
                document.getElementById('reminder_amount').textContent = formatCurrencyJS(debtData.current_amount || 0);
                
                // Set reminder date (default to due_date or tomorrow)
                let reminderDate = new Date();
                if (debtData.due_date) {
                    reminderDate = new Date(debtData.due_date);
                    // If due date is past, set to tomorrow
                    if (reminderDate < new Date()) {
                        reminderDate = new Date();
                        reminderDate.setDate(reminderDate.getDate() + 1);
                    }
                } else {
                    reminderDate.setDate(reminderDate.getDate() + 1);
                }
                document.getElementById('reminder_reminder_date').value = reminderDate.toISOString().split('T')[0];
                
                // Show due date info if available
                const dueDateInfo = document.getElementById('reminder_due_date_info');
                if (debtData.due_date) {
                    const dueDate = new Date(debtData.due_date);
                    const formattedDate = dueDate.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
                    dueDateInfo.textContent = `Jatuh tempo: ${formattedDate}`;
                } else {
                    dueDateInfo.textContent = 'Jatuh tempo: Tidak ditentukan';
                }
            }, 0);
            
            // Show modal
            modal.classList.remove('hidden');
        }

        function closeReminderModal() {
            const modal = document.getElementById('reminderModal');
            modal.classList.add('hidden');
            clearErrors();
        }

        async function submitReminderForm(e) {
            e.preventDefault();
            clearErrors();

            const form = e.target;
            const formData = new FormData(form);
            const debtId = document.getElementById('reminder_debt_id').value;
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Membuat...';

            try {
                const response = await fetch(`/debts/${debtId}/reminder`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData,
                });

                let data;
                try {
                    data = await response.json();
                } catch (e) {
                    console.error('Error parsing response:', e);
                    alert('Terjadi kesalahan saat memproses respons dari server.');
                    return;
                }

                if (response.ok && data.success) {
                    const notification = document.createElement('div');
                    notification.className = 'fixed top-4 right-4 bg-emerald-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center gap-2';
                    notification.innerHTML = `<i class="fa-solid fa-check-circle"></i> ${data.message}`;
                    document.body.appendChild(notification);

                    setTimeout(() => {
                        notification.remove();
                        closeReminderModal();
                        // Open Google Calendar in new tab
                        if (data.calendar_url) {
                            window.open(data.calendar_url, '_blank');
                        }
                    }, 1000);
                } else {
                    // Handle validation errors
                    if (data.errors) {
                        Object.keys(data.errors).forEach(field => {
                            const errorMessage = Array.isArray(data.errors[field]) ? data.errors[field][0] : data.errors[field];
                            showError(field, errorMessage);
                        });
                    } else {
                        alert(data.message || 'Gagal membuat reminder');
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan. Silakan coba lagi.');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        }

        // Initialize handlers
        document.addEventListener('DOMContentLoaded', function() {
            const typeSelect = document.getElementById('modal_transactionType');
            if (typeSelect) {
                typeSelect.addEventListener('change', handleTransactionTypeChange);
            }

            const form = document.getElementById('transactionForm');
            if (form) {
                form.addEventListener('submit', submitTransactionForm);
            }

            // Close modal on backdrop click
            const modal = document.getElementById('transactionModal');
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        closeTransactionModal();
                    }
                });
            }

            // Account modal events
            const accountForm = document.getElementById('accountForm');
            if (accountForm) {
                accountForm.addEventListener('submit', submitAccountForm);
            }

            const accountModal = document.getElementById('accountModal');
            if (accountModal) {
                accountModal.addEventListener('click', function(e) {
                    if (e.target === accountModal) {
                        closeAccountModal();
                    }
                });
            }

            // Category modal events
            const categoryForm = document.getElementById('categoryForm');
            if (categoryForm) {
                categoryForm.addEventListener('submit', submitCategoryForm);
            }

            const categoryModal = document.getElementById('categoryModal');
            if (categoryModal) {
                categoryModal.addEventListener('click', function(e) {
                    if (e.target === categoryModal) {
                        closeCategoryModal();
                    }
                });
            }

            // Budget modal events
            const budgetForm = document.getElementById('budgetForm');
            if (budgetForm) {
                budgetForm.addEventListener('submit', submitBudgetForm);
            }

            const budgetModal = document.getElementById('budgetModal');
            if (budgetModal) {
                budgetModal.addEventListener('click', function(e) {
                    if (e.target === budgetModal) {
                        closeBudgetModal();
                    }
                });
            }

            // Debt modal events
            const debtForm = document.getElementById('debtForm');
            if (debtForm) {
                debtForm.addEventListener('submit', submitDebtForm);
            }

            const debtModal = document.getElementById('debtModal');
            if (debtModal) {
                debtModal.addEventListener('click', function(e) {
                    if (e.target === debtModal) {
                        closeDebtModal();
                    }
                });
            }

            // Payment modal events
            const paymentForm = document.getElementById('paymentForm');
            if (paymentForm) {
                paymentForm.addEventListener('submit', submitPaymentForm);
            }

            const paymentModal = document.getElementById('paymentModal');
            if (paymentModal) {
                paymentModal.addEventListener('click', function(e) {
                    if (e.target === paymentModal) {
                        closePaymentModal();
                    }
                });
            }

            // Reminder modal events
            const reminderForm = document.getElementById('reminderForm');
            if (reminderForm) {
                reminderForm.addEventListener('submit', submitReminderForm);
            }

            const reminderModal = document.getElementById('reminderModal');
            if (reminderModal) {
                reminderModal.addEventListener('click', function(e) {
                    if (e.target === reminderModal) {
                        closeReminderModal();
                    }
                });
            }

            // Mark Paid modal events
            const markPaidForm = document.getElementById('markPaidForm');
            if (markPaidForm) {
                markPaidForm.addEventListener('submit', submitMarkPaidForm);
            }

            const markPaidModal = document.getElementById('markPaidModal');
            if (markPaidModal) {
                markPaidModal.addEventListener('click', function(e) {
                    if (e.target === markPaidModal) {
                        closeMarkPaidModal();
                    }
                });
            }

            // Debt Detail Modal events
            const debtDetailModal = document.getElementById('debtDetailModal');
            if (debtDetailModal) {
                debtDetailModal.addEventListener('click', function(e) {
                    if (e.target === debtDetailModal) {
                        closeDebtDetailModal();
                    }
                });
            }

            // Transaction Detail Modal events
            const transactionDetailModal = document.getElementById('transactionDetailModal');
            if (transactionDetailModal) {
                transactionDetailModal.addEventListener('click', function(e) {
                    if (e.target === transactionDetailModal) {
                        closeTransactionDetailModal();
                    }
                });
            }

            // Set initial view based on URL params
            const params = new URLSearchParams(window.location.search);
            const hasTransactionFilter = params.has('transaction_search') || params.has('transaction_category_id');
            const hasReportFilter = params.has('report_from') || params.has('report_to') || params.has('report_type');
            const viewParam = params.get('view');

            if (hasTransactionFilter) {
                const transactionsBtn = document.getElementById('nav-transactions');
                if (transactionsBtn) {
                    switchView('transactions', transactionsBtn);
                }
            } else if (hasReportFilter || viewParam === 'reports') {
                const reportsBtn = document.getElementById('nav-reports');
                if (reportsBtn) {
                    switchView('reports', reportsBtn);
                }
            } else if (viewParam === 'budget') {
                // Find budget button by looking for the one that contains "Budget" text
                const navItems = document.querySelectorAll('.nav-item');
                let budgetBtn = null;
                navItems.forEach(btn => {
                    if (btn.textContent.trim().includes('Budget')) {
                        budgetBtn = btn;
                    }
                });
                if (budgetBtn) {
                    switchView('budget', budgetBtn);
                }
            } else if (viewParam === 'debts') {
                // Find debts button by looking for the one that contains "Utang" text
                const navItems = document.querySelectorAll('.nav-item');
                let debtsBtn = null;
                navItems.forEach(btn => {
                    if (btn.textContent.trim().includes('Utang')) {
                        debtsBtn = btn;
                    }
                });
                if (debtsBtn) {
                    switchView('debts', debtsBtn);
                }
            } else {
                // Default view is Dashboard
                const dashboardBtn = document.getElementById('nav-dashboard');
                if (dashboardBtn) {
                    switchView('dashboard', dashboardBtn);
                }
            }
        });
    </script>

    <!-- Transaction Modal -->
    <div id="transactionModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4 flex justify-between items-center">
                <h3 class="text-xl font-bold text-dark dark:text-white">Tambah Transaksi</h3>
                    <button onclick="closeTransactionModal()" class="text-gray-400 dark:text-gray-300 hover:text-gray-600 dark:hover:text-gray-100 transition">
                    <i class="fa-solid fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="transactionForm" class="p-6 space-y-4">
                @csrf
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipe Transaksi</label>
                    <select name="type" id="modal_transactionType" required class="w-full px-4 py-2 bg-white dark:bg-gray-700 text-dark dark:text-white border border-gray-200 dark:border-gray-600 rounded-lg focus:outline-none focus:border-primary">
                        <option value="income">Pemasukan</option>
                        <option value="expense">Pengeluaran</option>
                        <option value="transfer">Transfer</option>
                    </select>
                </div>

                <div id="singleAccountField">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Akun</label>
                    <select name="account_id" id="modal_account_id" required class="w-full px-4 py-2 bg-white dark:bg-gray-700 text-dark dark:text-white border border-gray-200 dark:border-gray-600 rounded-lg focus:outline-none focus:border-primary">
                        <option value="">Pilih akun</option>
                    </select>
                </div>

                <div id="transferAccountsField" style="display: none;">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Dari Akun</label>
                            <select name="from_account_id" id="modal_from_account_id" class="w-full px-4 py-2 bg-white dark:bg-gray-700 text-dark dark:text-white border border-gray-200 dark:border-gray-600 rounded-lg focus:outline-none focus:border-primary">
                                <option value="">Pilih akun</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ke Akun</label>
                            <select name="to_account_id" id="modal_to_account_id" class="w-full px-4 py-2 bg-white dark:bg-gray-700 text-dark dark:text-white border border-gray-200 dark:border-gray-600 rounded-lg focus:outline-none focus:border-primary">
                                <option value="">Pilih akun</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kategori</label>
                    <select name="category_id" id="modal_category_id" class="w-full px-4 py-2 bg-white dark:bg-gray-700 text-dark dark:text-white border border-gray-200 dark:border-gray-600 rounded-lg focus:outline-none focus:border-primary">
                        <option value="">Pilih kategori</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jumlah</label>
                    <input type="number" name="amount" id="modal_amount" step="0.01" min="0.01" required class="w-full px-4 py-2 bg-white dark:bg-gray-700 text-dark dark:text-white border border-gray-200 dark:border-gray-600 rounded-lg focus:outline-none focus:border-primary" placeholder="0.00">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Deskripsi</label>
                    <input type="text" name="description" id="modal_description" required class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-dark dark:text-white rounded-lg focus:outline-none focus:border-primary" placeholder="Masukkan deskripsi">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal</label>
                    <input type="date" name="date" id="modal_date" value="{{ date('Y-m-d') }}" required class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-dark dark:text-white rounded-lg focus:outline-none focus:border-primary">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Catatan</label>
                    <textarea name="notes" id="modal_notes" rows="3" class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-dark dark:text-white rounded-lg focus:outline-none focus:border-primary" placeholder="Catatan tambahan (opsional)"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Foto Struk (Opsional)</label>
                    <input type="file" name="receipt" id="modal_receipt" accept="image/*" class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-dark dark:text-white rounded-lg focus:outline-none focus:border-primary">
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit" class="flex-1 bg-primary text-white px-4 py-2.5 rounded-lg font-medium hover:bg-emerald-600 transition">
                        <i class="fa-solid fa-save mr-2"></i> Simpan Transaksi
                    </button>
                    <button type="button" onclick="closeTransactionModal()" class="px-4 py-2.5 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Account (Wallet) Modal -->
    <div id="accountModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4 flex justify-between items-center">
                <h3 id="accountModalTitle" class="text-xl font-bold text-dark dark:text-white">Tambah Dompet Baru</h3>
                <button onclick="closeAccountModal()" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fa-solid fa-times text-xl"></i>
                </button>
            </div>

            <form id="accountForm" class="p-6 space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Dompet</label>
                    <input
                        type="text"
                        name="name"
                        id="account_name"
                        required
                            class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-dark dark:text-white rounded-lg focus:outline-none focus:border-primary"
                        placeholder="Contoh: BCA, Dompet Cash"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipe Dompet</label>
                    <select
                        name="type"
                        id="account_type"
                        required
                            class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-dark dark:text-white rounded-lg focus:outline-none focus:border-primary"
                    >
                        <option value="cash">Cash</option>
                        <option value="bank">Rekening Bank</option>
                        <option value="ewallet">E-Wallet</option>
                        <option value="liability">Kewajiban (Kartu Kredit, Paylater)</option>
                        <option value="investment">Investasi</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Saldo Awal</label>
                    <input
                        type="number"
                        step="0.01"
                        name="initial_balance"
                        id="account_initial_balance"
                        value="0"
                            class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-dark dark:text-white rounded-lg focus:outline-none focus:border-primary"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mata Uang</label>
                    <input
                        type="text"
                        name="currency"
                        id="account_currency"
                        value="IDR"
                        maxlength="3"
                            class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-dark dark:text-white rounded-lg focus:outline-none focus:border-primary"
                    >
                </div>

                <div>
                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 mb-1">
                        <input
                            type="checkbox"
                            name="is_hidden"
                            id="account_is_hidden"
                            class="rounded border-gray-300 dark:border-gray-600"
                        >
                        <span>Sembunyikan dari ringkasan saldo</span>
                    </label>
                </div>

                <div>
                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 mb-1">
                        <input
                            type="checkbox"
                            name="is_active"
                            id="account_is_active"
                            class="rounded border-gray-300 dark:border-gray-600"
                            checked
                        >
                        <span>Dompet aktif</span>
                    </label>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Catatan</label>
                    <textarea
                        name="notes"
                        id="account_notes"
                        rows="3"
                            class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-dark dark:text-white rounded-lg focus:outline-none focus:border-primary"
                        placeholder="Catatan tambahan (opsional)"
                    ></textarea>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit" class="flex-1 bg-primary text-white px-4 py-2.5 rounded-lg font-medium hover:bg-emerald-600 transition">
                        <i class="fa-solid fa-save mr-2"></i> Simpan Dompet
                    </button>
                    <button type="button" onclick="closeAccountModal()" class="px-4 py-2.5 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Category Modal -->
    <div id="categoryModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4 flex justify-between items-center">
                <h3 class="text-xl font-bold text-dark dark:text-white">Tambah Kategori Baru</h3>
                <button onclick="closeCategoryModal()" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fa-solid fa-times text-xl"></i>
                </button>
            </div>

            <form id="categoryForm" class="p-6 space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Kategori</label>
                    <input
                        type="text"
                        name="name"
                        id="category_name"
                        required
                            class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-dark dark:text-white rounded-lg focus:outline-none focus:border-primary"
                        placeholder="Contoh: Makan, Transport"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipe</label>
                    <select
                        name="type"
                        id="category_type"
                        required
                            class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-dark dark:text-white rounded-lg focus:outline-none focus:border-primary"
                    >
                        <option value="expense">Pengeluaran</option>
                        <option value="income">Pemasukan</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Icon (opsional)</label>
                    <input
                        type="text"
                        name="icon"
                        id="category_icon"
                            class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-dark dark:text-white rounded-lg focus:outline-none focus:border-primary"
                        placeholder="Contoh: utensils, car, shopping-bag"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Warna (opsional)</label>
                    <input
                        type="text"
                        name="color"
                        id="category_color"
                            class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-dark dark:text-white rounded-lg focus:outline-none focus:border-primary"
                        placeholder="#F97316"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Parent (opsional)</label>
                    <select
                        name="parent_id"
                        id="category_parent_id"
                            class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-dark dark:text-white rounded-lg focus:outline-none focus:border-primary"
                    >
                        <option value="">Tanpa parent</option>
                        @php
                            $allCategoriesForParent = \App\Models\Category::where('user_id', auth()->id())
                                ->whereNull('parent_id')
                                ->orderBy('name')
                                ->get();
                        @endphp
                        @foreach($allCategoriesForParent as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }} ({{ $cat->type }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit" class="flex-1 bg-primary text-white px-4 py-2.5 rounded-lg font-medium hover:bg-emerald-600 transition">
                        <i class="fa-solid fa-save mr-2"></i> Simpan Kategori
                    </button>
                    <button type="button" onclick="closeCategoryModal()" class="px-4 py-2.5 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Transaction Detail Modal -->
    <div id="transactionDetailModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4 flex justify-between items-center">
                <h3 class="text-xl font-bold text-dark dark:text-white">Detail Transaksi</h3>
                <button onclick="closeTransactionDetailModal()" class="text-gray-400 dark:text-gray-300 hover:text-gray-600 dark:hover:text-gray-100 transition">
                    <i class="fa-solid fa-times text-xl"></i>
                </button>
            </div>
            
            <div id="transactionDetailContent" class="p-6 space-y-4">
                <div class="flex items-center justify-center py-8">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Budget Modal -->
    <div id="budgetModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4 flex justify-between items-center">
                <h3 id="budgetModalTitle" class="text-xl font-bold text-dark dark:text-white">Tambah Budget Baru</h3>
                <button onclick="closeBudgetModal()" class="text-gray-400 dark:text-gray-300 hover:text-gray-600 dark:hover:text-gray-100 transition">
                    <i class="fa-solid fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="budgetForm" class="p-6 space-y-4">
                @csrf
                <input type="hidden" id="budget_id" name="budget_id" value="">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kategori</label>
                    <select name="category_id" id="budget_category_id" required class="w-full px-4 py-2 bg-white dark:bg-gray-700 text-dark dark:text-white border border-gray-200 dark:border-gray-600 rounded-lg focus:outline-none focus:border-primary">
                        <option value="">Pilih kategori</option>
                        @foreach($expenseCategories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <span class="error-message text-red-500 dark:text-red-400 text-xs mt-1 hidden" id="error_category_id"></span>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jumlah Budget</label>
                    <input type="number" name="amount" id="budget_amount" step="0.01" min="0.01" required class="w-full px-4 py-2 bg-white dark:bg-gray-700 text-dark dark:text-white border border-gray-200 dark:border-gray-600 rounded-lg focus:outline-none focus:border-primary" placeholder="0.00">
                    <span class="error-message text-red-500 dark:text-red-400 text-xs mt-1 hidden" id="error_amount"></span>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Bulan</label>
                        <select name="month" id="budget_month" required class="w-full px-4 py-2 bg-white dark:bg-gray-700 text-dark dark:text-white border border-gray-200 dark:border-gray-600 rounded-lg focus:outline-none focus:border-primary">
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ $i == date('n') ? 'selected' : '' }}>{{ \Carbon\Carbon::create(null, $i, 1)->locale('id')->monthName }}</option>
                            @endfor
                        </select>
                        <span class="error-message text-red-500 dark:text-red-400 text-xs mt-1 hidden" id="error_month"></span>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tahun</label>
                        <input type="number" name="year" id="budget_year" value="{{ date('Y') }}" min="2020" required class="w-full px-4 py-2 bg-white dark:bg-gray-700 text-dark dark:text-white border border-gray-200 dark:border-gray-600 rounded-lg focus:outline-none focus:border-primary">
                        <span class="error-message text-red-500 dark:text-red-400 text-xs mt-1 hidden" id="error_year"></span>
                    </div>
                </div>

                <div>
                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 mb-1">
                        <input
                            type="checkbox"
                            name="rollover_enabled"
                            id="budget_rollover_enabled"
                            class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                        >
                        <span>Aktifkan rollover (sisa budget bulan ini akan ditambahkan ke bulan berikutnya)</span>
                    </label>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit" class="flex-1 bg-primary text-white px-4 py-2.5 rounded-lg font-medium hover:bg-emerald-600 transition">
                        <i class="fa-solid fa-save mr-2"></i> Simpan Budget
                    </button>
                    <button type="button" onclick="closeBudgetModal()" class="px-4 py-2.5 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Debt Modal (Piutang/Utang) -->
    <div id="debtModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4 flex justify-between items-center">
                <h3 id="debtModalTitle" class="text-xl font-bold text-dark dark:text-white">Tambah Piutang</h3>
                <button onclick="closeDebtModal()" class="text-gray-400 dark:text-gray-300 hover:text-gray-600 dark:hover:text-gray-100 transition">
                    <i class="fa-solid fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="debtForm" class="p-6 space-y-4">
                @csrf
                <input type="hidden" id="debt_type" name="type" value="">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Kontak</label>
                    <input type="text" name="contact_name" id="debt_contact_name" required class="w-full px-4 py-2 bg-white dark:bg-gray-700 text-dark dark:text-white border border-gray-200 dark:border-gray-600 rounded-lg focus:outline-none focus:border-primary" placeholder="Nama orang/kontak">
                    <span class="error-message text-red-500 dark:text-red-400 text-xs mt-1 hidden" id="error_contact_name"></span>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nomor Telepon (Opsional)</label>
                    <input type="text" name="contact_phone" id="debt_contact_phone" class="w-full px-4 py-2 bg-white dark:bg-gray-700 text-dark dark:text-white border border-gray-200 dark:border-gray-600 rounded-lg focus:outline-none focus:border-primary" placeholder="08xxxxxxxxxx">
                    <span class="error-message text-red-500 dark:text-red-400 text-xs mt-1 hidden" id="error_contact_phone"></span>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email (Opsional)</label>
                    <input type="email" name="contact_email" id="debt_contact_email" class="w-full px-4 py-2 bg-white dark:bg-gray-700 text-dark dark:text-white border border-gray-200 dark:border-gray-600 rounded-lg focus:outline-none focus:border-primary" placeholder="email@example.com">
                    <span class="error-message text-red-500 dark:text-red-400 text-xs mt-1 hidden" id="error_contact_email"></span>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jumlah</label>
                    <input type="number" name="initial_amount" id="debt_initial_amount" step="0.01" min="0.01" required class="w-full px-4 py-2 bg-white dark:bg-gray-700 text-dark dark:text-white border border-gray-200 dark:border-gray-600 rounded-lg focus:outline-none focus:border-primary" placeholder="0.00">
                    <span class="error-message text-red-500 dark:text-red-400 text-xs mt-1 hidden" id="error_initial_amount"></span>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal Jatuh Tempo (Opsional)</label>
                    <input type="date" name="due_date" id="debt_due_date" class="w-full px-4 py-2 bg-white dark:bg-gray-700 text-dark dark:text-white border border-gray-200 dark:border-gray-600 rounded-lg focus:outline-none focus:border-primary">
                    <span class="error-message text-red-500 dark:text-red-400 text-xs mt-1 hidden" id="error_due_date"></span>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Akun Terkait (Opsional)</label>
                    <select name="account_id" id="debt_account_id" class="w-full px-4 py-2 bg-white dark:bg-gray-700 text-dark dark:text-white border border-gray-200 dark:border-gray-600 rounded-lg focus:outline-none focus:border-primary">
                        <option value="">Pilih akun (opsional)</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                        @endforeach
                    </select>
                    <span class="error-message text-red-500 dark:text-red-400 text-xs mt-1 hidden" id="error_account_id"></span>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Deskripsi (Opsional)</label>
                    <textarea name="description" id="debt_description" rows="3" class="w-full px-4 py-2 bg-white dark:bg-gray-700 text-dark dark:text-white border border-gray-200 dark:border-gray-600 rounded-lg focus:outline-none focus:border-primary" placeholder="Catatan tambahan"></textarea>
                    <span class="error-message text-red-500 dark:text-red-400 text-xs mt-1 hidden" id="error_description"></span>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit" class="flex-1 bg-primary text-white px-4 py-2.5 rounded-lg font-medium hover:bg-emerald-600 transition">
                        <i class="fa-solid fa-save mr-2"></i> Simpan
                    </button>
                    <button type="button" onclick="closeDebtModal()" class="px-4 py-2.5 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Payment Modal (Bayar Cicilan) -->
    <div id="paymentModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4 flex justify-between items-center">
                <h3 id="paymentModalTitle" class="text-xl font-bold text-dark dark:text-white">Bayar Cicilan</h3>
                <button onclick="closePaymentModal()" class="text-gray-400 dark:text-gray-300 hover:text-gray-600 dark:hover:text-gray-100 transition">
                    <i class="fa-solid fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="paymentForm" class="p-6 space-y-4">
                @csrf
                <input type="hidden" id="payment_debt_id" value="">
                
                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg mb-4">
                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-1">Kontak</p>
                    <p id="payment_contact_name" class="font-bold text-dark dark:text-white"></p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Sisa Utang: <span id="payment_remaining_amount" class="font-bold text-rose-600 dark:text-rose-400 sensitive-data"></span></p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jumlah Pembayaran</label>
                    <input type="number" name="amount" id="payment_amount" step="0.01" min="0.01" required class="w-full px-4 py-2 border border-gray-200 dark:border-gray-600 rounded-lg focus:outline-none focus:border-primary dark:bg-gray-700 dark:text-white" placeholder="0.00">
                    <span class="error-message text-red-500 dark:text-red-400 text-xs mt-1 hidden" id="error_amount"></span>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal Pembayaran</label>
                    <input type="date" name="payment_date" id="payment_payment_date" value="{{ date('Y-m-d') }}" required class="w-full px-4 py-2 border border-gray-200 dark:border-gray-600 rounded-lg focus:outline-none focus:border-primary dark:bg-gray-700 dark:text-white">
                    <span class="error-message text-red-500 dark:text-red-400 text-xs mt-1 hidden" id="error_payment_date"></span>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Catatan (Opsional)</label>
                    <textarea name="notes" id="payment_notes" rows="3" class="w-full px-4 py-2 border border-gray-200 dark:border-gray-600 rounded-lg focus:outline-none focus:border-primary dark:bg-gray-700 dark:text-white" placeholder="Catatan pembayaran"></textarea>
                    <span class="error-message text-red-500 dark:text-red-400 text-xs mt-1 hidden" id="error_notes"></span>
                </div>

                <div>
                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 mb-1">
                        <input
                            type="checkbox"
                            name="create_transaction"
                            id="payment_create_transaction"
                            class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                            onchange="togglePaymentAccountField()"
                        >
                        <span>Buat transaksi untuk pembayaran ini</span>
                    </label>
                </div>

                <div id="payment_account_field" style="display: none;">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Akun</label>
                    <select name="account_id" id="payment_account_id" class="w-full px-4 py-2 bg-white dark:bg-gray-700 text-dark dark:text-white border border-gray-200 dark:border-gray-600 rounded-lg focus:outline-none focus:border-primary">
                        <option value="">Pilih akun</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                        @endforeach
                    </select>
                    <span class="error-message text-red-500 dark:text-red-400 text-xs mt-1 hidden" id="error_account_id"></span>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit" class="flex-1 bg-primary text-white px-4 py-2.5 rounded-lg font-medium hover:bg-emerald-600 transition">
                        <i class="fa-solid fa-save mr-2"></i> Catat Pembayaran
                    </button>
                    <button type="button" onclick="closePaymentModal()" class="px-4 py-2.5 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reminder Modal (Ingatkan Piutang) -->
    <div id="reminderModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center">
                <h3 class="text-xl font-bold text-dark">Buat Reminder</h3>
                <button onclick="closeReminderModal()" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fa-solid fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="reminderForm" class="p-6 space-y-4">
                @csrf
                <input type="hidden" id="reminder_debt_id" value="">
                
                <div class="bg-gray-50 p-4 rounded-lg mb-4">
                    <p class="text-sm text-gray-600 mb-1">Kontak</p>
                    <p id="reminder_contact_name" class="font-bold text-dark"></p>
                    <p class="text-xs text-gray-500 mt-2">Jumlah Piutang: <span id="reminder_amount" class="font-bold text-emerald-600 sensitive-data"></span></p>
                    <p id="reminder_due_date_info" class="text-xs text-gray-500 mt-1"></p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Reminder</label>
                    <input type="date" name="reminder_date" id="reminder_reminder_date" required class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-primary">
                    <span class="error-message text-red-500 text-xs mt-1 hidden" id="error_reminder_date"></span>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Waktu Reminder (Opsional)</label>
                    <input type="time" name="reminder_time" id="reminder_reminder_time" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-primary">
                    <span class="text-xs text-gray-500 mt-1">Kosongkan untuk menggunakan waktu default (09:00)</span>
                    <span class="error-message text-red-500 text-xs mt-1 hidden" id="error_reminder_time"></span>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Reminder (Opsional)</label>
                    <textarea name="notes" id="reminder_notes" rows="3" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-primary" placeholder="Catatan tambahan untuk reminder"></textarea>
                    <span class="error-message text-red-500 text-xs mt-1 hidden" id="error_notes"></span>
                </div>

                <div class="bg-blue-50 p-3 rounded-lg">
                    <p class="text-xs text-blue-700">
                        <i class="fa-solid fa-info-circle mr-1"></i>
                        Reminder akan dibuat di Google Calendar Anda. Setelah berhasil, halaman Google Calendar akan terbuka untuk konfirmasi.
                    </p>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit" class="flex-1 bg-primary text-white px-4 py-2.5 rounded-lg font-medium hover:bg-emerald-600 transition">
                        <i class="fa-solid fa-calendar-plus mr-2"></i> Buat Reminder
                    </button>
                    <button type="button" onclick="closeReminderModal()" class="px-4 py-2.5 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Mark Paid Modal (Tandai Piutang sebagai Lunas) -->
    <div id="markPaidModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4 flex justify-between items-center">
                <h3 class="text-xl font-bold text-dark dark:text-white">Tandai sebagai Lunas</h3>
                <button onclick="closeMarkPaidModal()" class="text-gray-400 dark:text-gray-300 hover:text-gray-600 dark:hover:text-gray-100 transition">
                    <i class="fa-solid fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="markPaidForm" class="p-6 space-y-4">
                @csrf
                <input type="hidden" id="mark_paid_debt_id" value="">
                <input type="hidden" name="create_transaction" value="1">
                
                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg mb-4">
                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-1">Kontak</p>
                    <p id="mark_paid_contact_name" class="font-bold text-dark dark:text-white"></p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Jumlah Piutang: <span id="mark_paid_amount" class="font-bold text-emerald-600 dark:text-emerald-400 sensitive-data"></span></p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pilih Akun untuk Transaksi</label>
                    <select name="account_id" id="mark_paid_account_id" required class="w-full px-4 py-2 bg-white dark:bg-gray-700 text-dark dark:text-white border border-gray-200 dark:border-gray-600 rounded-lg focus:outline-none focus:border-primary">
                        <option value="">Pilih akun</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                        @endforeach
                    </select>
                    <span class="error-message text-red-500 dark:text-red-400 text-xs mt-1 hidden" id="error_account_id"></span>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Transaksi income akan dibuat otomatis saat piutang ditandai sebagai lunas.</p>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit" class="flex-1 bg-primary text-white px-4 py-2.5 rounded-lg font-medium hover:bg-emerald-600 transition">
                        <i class="fa-solid fa-check-circle mr-2"></i> Tandai sebagai Lunas
                    </button>
                    <button type="button" onclick="closeMarkPaidModal()" class="px-4 py-2.5 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Debt Detail Modal -->
    <div id="debtDetailModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4 flex justify-between items-center">
                <h3 class="text-xl font-bold text-dark dark:text-white">Detail Utang/Piutang</h3>
                <button onclick="closeDebtDetailModal()" class="text-gray-400 dark:text-gray-300 hover:text-gray-600 dark:hover:text-gray-100 transition">
                    <i class="fa-solid fa-times text-xl"></i>
                </button>
            </div>
            <div id="debtDetailContent" class="p-6 space-y-4">
                <div class="flex items-center justify-center py-8">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
