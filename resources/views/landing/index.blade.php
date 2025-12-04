<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <title>Motrac - Smart Money Tracker</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

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
</head>
<body class="font-sans text-slate-800 bg-gray-50">

    <!-- Navbar -->
    <nav class="bg-white border-b border-gray-100 fixed w-full z-50">
        <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-8">
            <div class="flex justify-between h-14 sm:h-16 items-center">
                <!-- Logo -->
                <a href="{{ route('landing') }}" class="flex items-center gap-1.5 sm:gap-2 flex-shrink-0">
                    <img src="{{ asset('logo.png') }}" alt="Motrac" class="h-7 sm:h-8 w-auto">
                    <span class="text-lg sm:text-xl font-extrabold tracking-tight text-dark" style="font-family: 'Inter', sans-serif; font-weight: 800;">Motrac</span>
                </a>

                <!-- Desktop Menu -->
                <div class="hidden lg:flex space-x-6 xl:space-x-8 text-sm font-medium text-gray-500">
                    <a href="#features" class="hover:text-primary transition">{{ __('Features') }}</a>
                    <a href="#how-it-works" class="hover:text-primary transition">{{ __('How It Works') }}</a>
                    <a href="#pricing" class="hover:text-primary transition">{{ __('Pricing') }}</a>
                    <a href="#faq" class="hover:text-primary transition">{{ __('FAQ') }}</a>
                </div>

                <!-- Desktop Language Toggle & Auth Buttons -->
                <div class="hidden md:flex items-center gap-2 lg:gap-3">
                    <!-- Language Toggle Switch -->
                    <div class="flex items-center bg-white border border-gray-200 rounded-full p-0.5 sm:p-1">
                        <button onclick="switchLanguage('id')" class="px-2 lg:px-3 py-1 sm:py-1.5 rounded-full text-xs lg:text-sm font-semibold transition-all {{ app()->getLocale() === 'id' ? 'bg-dark text-white' : 'text-gray-500' }}">
                            ID
                        </button>
                        <button onclick="switchLanguage('en')" class="px-2 lg:px-3 py-1 sm:py-1.5 rounded-full text-xs lg:text-sm font-semibold transition-all {{ app()->getLocale() === 'en' ? 'bg-dark text-white' : 'text-gray-500' }}">
                            EN
                        </button>
                    </div>
                    <a href="{{ route('login') }}" class="text-xs lg:text-sm font-medium text-gray-600 hover:text-primary transition hidden lg:inline-block">{{ __('Login') }}</a>
                    <a href="{{ route('register') }}" class="bg-primary hover:bg-emerald-600 text-white text-xs lg:text-sm font-medium px-3 lg:px-5 py-1.5 lg:py-2.5 rounded-full transition shadow-lg shadow-emerald-200">
                        {{ __('Register Free') }}
                    </a>
                </div>

                <!-- Mobile Menu Button -->
                <button onclick="toggleMobileMenu()" class="md:hidden text-gray-600 hover:text-primary transition flex-shrink-0 p-2">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
            </div>
        </div>

        <!-- Mobile Menu Overlay -->
        <div id="mobileMenu" class="hidden fixed inset-0 bg-black bg-opacity-50 z-40 md:hidden" onclick="toggleMobileMenu()">
            <div class="absolute right-0 top-0 h-full w-72 sm:w-80 bg-white shadow-xl" onclick="event.stopPropagation()">
                <!-- Mobile Menu Header -->
                <div class="p-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                    <div class="flex items-center gap-2">
                        <img src="{{ asset('logo.png') }}" alt="Motrac" class="h-7 w-auto">
                        <span class="text-lg font-extrabold tracking-tight text-dark" style="font-family: 'Inter', sans-serif; font-weight: 800;">Motrac</span>
                    </div>
                    <button onclick="toggleMobileMenu()" class="text-gray-400 hover:text-gray-600 transition p-2">
                        <i class="fa-solid fa-times text-xl"></i>
                    </button>
                </div>
                
                <!-- Mobile Menu Content -->
                <div class="p-4 space-y-1 overflow-y-auto max-h-[calc(100vh-80px)]">
                    <!-- Navigation Links -->
                    <a href="#features" onclick="toggleMobileMenu()" class="block px-4 py-3 text-gray-700 hover:bg-gray-50 hover:text-primary rounded-lg transition font-medium">
                        <i class="fa-solid fa-star w-5 inline-block mr-3 text-gray-400"></i>
                        {{ __('Features') }}
                    </a>
                    <a href="#how-it-works" onclick="toggleMobileMenu()" class="block px-4 py-3 text-gray-700 hover:bg-gray-50 hover:text-primary rounded-lg transition font-medium">
                        <i class="fa-solid fa-gear w-5 inline-block mr-3 text-gray-400"></i>
                        {{ __('How It Works') }}
                    </a>
                    <a href="#pricing" onclick="toggleMobileMenu()" class="block px-4 py-3 text-gray-700 hover:bg-gray-50 hover:text-primary rounded-lg transition font-medium">
                        <i class="fa-solid fa-tag w-5 inline-block mr-3 text-gray-400"></i>
                        {{ __('Pricing') }}
                    </a>
                    <a href="#faq" onclick="toggleMobileMenu()" class="block px-4 py-3 text-gray-700 hover:bg-gray-50 hover:text-primary rounded-lg transition font-medium">
                        <i class="fa-solid fa-question-circle w-5 inline-block mr-3 text-gray-400"></i>
                        {{ __('FAQ') }}
                    </a>
                    
                    <!-- Divider -->
                    <div class="my-4 border-t border-gray-200"></div>
                    
                    <!-- Language Toggle -->
                    <div class="px-4 py-2">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">{{ __('Language') }}</p>
                        <div class="flex items-center bg-gray-50 border border-gray-200 rounded-full p-1">
                            <button onclick="switchLanguage('id'); toggleMobileMenu();" class="flex-1 px-3 py-2 rounded-full text-sm font-semibold transition-all {{ app()->getLocale() === 'id' ? 'bg-dark text-white' : 'text-gray-500' }}">
                                ID
                            </button>
                            <button onclick="switchLanguage('en'); toggleMobileMenu();" class="flex-1 px-3 py-2 rounded-full text-sm font-semibold transition-all {{ app()->getLocale() === 'en' ? 'bg-dark text-white' : 'text-gray-500' }}">
                                EN
                            </button>
                        </div>
                    </div>
                    
                    <!-- Divider -->
                    <div class="my-4 border-t border-gray-200"></div>
                    
                    <!-- Auth Buttons -->
                    <div class="px-4 space-y-2">
                        <a href="{{ route('login') }}" onclick="toggleMobileMenu()" class="block w-full text-center px-4 py-3 text-sm font-medium text-gray-700 border border-gray-200 hover:bg-gray-50 rounded-lg transition">
                            {{ __('Login') }}
                        </a>
                        <a href="{{ route('register') }}" onclick="toggleMobileMenu()" class="block w-full text-center bg-primary hover:bg-emerald-600 text-white text-sm font-medium px-4 py-3 rounded-lg transition shadow-lg shadow-emerald-200">
                            {{ __('Register Free') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="pt-32 pb-20 px-4 sm:px-6 lg:px-8 bg-white overflow-hidden">
        <div class="max-w-7xl mx-auto text-center lg:text-left grid lg:grid-cols-2 gap-12 items-center">
            <!-- Text Content -->
            <div class="space-y-6">
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-dark leading-tight">
                    {{ __('Manage Your Finances') }} <br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary to-blue-500">{{ __('Without Hassle') }}.</span>
                </h1>
                <p class="text-lg text-gray-500 max-w-lg mx-auto lg:mx-0">
                    {{ __('Forget boring manual records. Monitor your cash flow, digital wallets, and investments in one smart dashboard.') }}
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start pt-4">
                    <a href="{{ route('register') }}" class="inline-block bg-dark hover:bg-gray-800 text-white px-8 py-3.5 rounded-xl font-medium shadow-xl transition transform hover:-translate-y-1">
                        {{ __('Start Tracking Now') }}
                    </a>
                    <a href="{{ route('login') }}" class="flex items-center justify-center gap-2 px-8 py-3.5 rounded-xl font-medium text-gray-600 border border-gray-200 hover:bg-gray-50 transition">
                        {{ __('Login Now') }}
                    </a>
                </div>
                <p class="text-sm text-gray-400">{{ __('Free forever for basic features. No credit card required.') }}</p>
            </div>

            <!-- Visual Content (Mockup) -->
            <div class="relative group">
                <div class="absolute -inset-1 bg-gradient-to-r from-primary to-blue-600 rounded-2xl blur opacity-25 group-hover:opacity-40 transition duration-1000 group-hover:duration-200"></div>
                <div class="relative bg-white border border-gray-200 rounded-2xl shadow-2xl overflow-hidden">
                    <!-- Mockup Header -->
                    <div class="bg-gray-50 border-b border-gray-100 p-4 flex gap-2">
                        <div class="w-3 h-3 rounded-full bg-red-400"></div>
                        <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                        <div class="w-3 h-3 rounded-full bg-green-400"></div>
                    </div>
                    <!-- Mockup Body -->
                    <div class="p-6 grid gap-6 bg-white">
                        <div class="flex justify-between items-end">
                            <div>
                                <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider">Total Balance</p>
                                <h3 class="text-3xl font-bold text-dark mt-1">Rp 15.450.000</h3>
                            </div>
                            <span class="bg-emerald-100 text-emerald-700 text-xs px-2 py-1 rounded font-bold">+12%</span>
                        </div>
                        <!-- Mock Bars -->
                        <div class="flex items-end gap-2 h-32 mt-4">
                            <div class="w-full bg-gray-100 rounded-t-lg h-[40%] hover:bg-primary transition-colors"></div>
                            <div class="w-full bg-gray-100 rounded-t-lg h-[60%] hover:bg-primary transition-colors"></div>
                            <div class="w-full bg-gray-100 rounded-t-lg h-[30%] hover:bg-primary transition-colors"></div>
                            <div class="w-full bg-primary rounded-t-lg h-[80%] shadow-lg shadow-emerald-200"></div>
                            <div class="w-full bg-gray-100 rounded-t-lg h-[50%] hover:bg-primary transition-colors"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Grid -->
    <section id="features" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-3xl font-bold text-dark mb-4">{{ __('Everything You Need') }}</h2>
                <p class="text-gray-500">{{ __('We make advanced features simple, so you can focus on your financial goals.') }}</p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition">
                    <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center text-blue-500 mb-6 text-xl">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                    <h3 class="text-xl font-bold text-dark mb-3">{{ __('Multi-Wallet Sync') }}</h3>
                    <p class="text-gray-500 leading-relaxed">{{ __('Manage Cash, BCA, Gopay, and OVO in one screen. No need to open multiple apps.') }}</p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition">
                    <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center text-primary mb-6 text-xl">
                        <i class="fa-solid fa-chart-pie"></i>
                    </div>
                    <h3 class="text-xl font-bold text-dark mb-3">{{ __('Smart Budgeting') }}</h3>
                    <p class="text-gray-500 leading-relaxed">{{ __("Set spending limit alerts per category. We'll remind you before your wallet breaks.") }}</p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition">
                    <div class="w-12 h-12 bg-rose-50 rounded-xl flex items-center justify-center text-rose-500 mb-6 text-xl">
                        <i class="fa-solid fa-file-invoice"></i>
                    </div>
                    <h3 class="text-xl font-bold text-dark mb-3">{{ __('Visual Reports') }}</h3>
                    <p class="text-gray-500 leading-relaxed">{{ __('Understand where your money goes through intuitive charts and export data to Excel/PDF.') }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-3xl font-bold text-dark mb-4">{{ __('How It Works') }}</h2>
                <p class="text-gray-500">{{ __('Get started with Motrac in just a few simple steps') }}</p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <!-- Step 1 -->
                <div class="text-center">
                    <div class="w-20 h-20 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-6 relative">
                        <div class="absolute -top-2 -right-2 w-8 h-8 bg-primary rounded-full flex items-center justify-center text-white font-bold text-sm">1</div>
                        <i class="fa-solid fa-user-plus text-3xl text-primary"></i>
                    </div>
                    <h3 class="text-xl font-bold text-dark mb-3">{{ __('Create Your Account') }}</h3>
                    <p class="text-gray-500 leading-relaxed">{{ __('Sign up for free in less than a minute. No credit card required.') }}</p>
                </div>

                <!-- Step 2 -->
                <div class="text-center">
                    <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-6 relative">
                        <div class="absolute -top-2 -right-2 w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white font-bold text-sm">2</div>
                        <i class="fa-solid fa-wallet text-3xl text-blue-500"></i>
                    </div>
                    <h3 class="text-xl font-bold text-dark mb-3">{{ __('Add Your Wallets') }}</h3>
                    <p class="text-gray-500 leading-relaxed">{{ __('Connect your cash, bank accounts, and digital wallets to track everything in one place.') }}</p>
                </div>

                <!-- Step 3 -->
                <div class="text-center">
                    <div class="w-20 h-20 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-6 relative">
                        <div class="absolute -top-2 -right-2 w-8 h-8 bg-emerald-500 rounded-full flex items-center justify-center text-white font-bold text-sm">3</div>
                        <i class="fa-solid fa-chart-line text-3xl text-emerald-500"></i>
                    </div>
                    <h3 class="text-xl font-bold text-dark mb-3">{{ __('Track & Analyze') }}</h3>
                    <p class="text-gray-500 leading-relaxed">{{ __('Start recording transactions and watch your financial insights grow with beautiful charts and reports.') }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-3xl font-bold text-dark mb-4">{{ __('Pricing') }}</h2>
                <p class="text-gray-500">{{ __('Simple, transparent pricing. Free forever.') }}</p>
            </div>
            
            <div class="max-w-2xl mx-auto">
                <!-- Free Plan -->
                <div class="bg-white p-10 rounded-2xl shadow-lg border-2 border-primary hover:shadow-xl transition">
                    <div class="text-center mb-8">
                        <h3 class="text-3xl font-bold text-dark mb-2">{{ __('Free') }}</h3>
                        <div class="flex items-baseline justify-center gap-1 mb-4">
                            <span class="text-5xl font-bold text-primary">Rp 0</span>
                            <span class="text-gray-500 text-xl">/{{ __('month') }}</span>
                        </div>
                        <p class="text-gray-500 text-lg">{{ __('Perfect for getting started') }}</p>
                    </div>
                    <ul class="space-y-4 mb-8">
                        <li class="flex items-start gap-3">
                            <i class="fa-solid fa-check text-primary mt-1 text-lg"></i>
                            <span class="text-gray-600 text-lg">{{ __('Unlimited transactions') }}</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <i class="fa-solid fa-check text-primary mt-1 text-lg"></i>
                            <span class="text-gray-600 text-lg">{{ __('Multiple wallets & categories') }}</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <i class="fa-solid fa-check text-primary mt-1 text-lg"></i>
                            <span class="text-gray-600 text-lg">{{ __('Basic reports & charts') }}</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <i class="fa-solid fa-check text-primary mt-1 text-lg"></i>
                            <span class="text-gray-600 text-lg">{{ __('Budget planning') }}</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <i class="fa-solid fa-check text-primary mt-1 text-lg"></i>
                            <span class="text-gray-600 text-lg">{{ __('Debt & receivable tracking') }}</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <i class="fa-solid fa-check text-primary mt-1 text-lg"></i>
                            <span class="text-gray-600 text-lg">{{ __('Data export (CSV/PDF)') }}</span>
                        </li>
                    </ul>
                    <a href="{{ route('register') }}" class="block w-full bg-primary hover:bg-emerald-600 text-white text-center px-6 py-4 rounded-xl font-medium transition shadow-lg shadow-emerald-200">
                        {{ __('Get Started Free') }}
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="py-20 bg-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-dark mb-4">{{ __('Frequently Asked Questions') }}</h2>
                <p class="text-gray-500">{{ __('Find answers to common questions about Motrac') }}</p>
            </div>
            
            <div class="space-y-4">
                <!-- FAQ Item 1 -->
                <div class="bg-gray-50 rounded-xl border border-gray-100 overflow-hidden">
                    <button class="faq-toggle w-full px-6 py-5 flex justify-between items-center text-left hover:bg-gray-100 transition" onclick="toggleFaq(this)">
                        <span class="font-semibold text-dark">{{ __('Is Motrac really free?') }}</span>
                        <i class="fa-solid fa-chevron-down text-gray-400 transition-transform"></i>
                    </button>
                    <div class="faq-content hidden px-6 pb-5">
                        <p class="text-gray-600 leading-relaxed">{{ __('Yes, Motrac offers basic features for free forever. You can record transactions, manage wallets, create categories, and view basic reports at no cost. No credit card required to get started.') }}</p>
                    </div>
                </div>

                <!-- FAQ Item 2 -->
                <div class="bg-gray-50 rounded-xl border border-gray-100 overflow-hidden">
                    <button class="faq-toggle w-full px-6 py-5 flex justify-between items-center text-left hover:bg-gray-100 transition" onclick="toggleFaq(this)">
                        <span class="font-semibold text-dark">{{ __('How do I import data from other applications?') }}</span>
                        <i class="fa-solid fa-chevron-down text-gray-400 transition-transform"></i>
                    </button>
                    <div class="faq-content hidden px-6 pb-5">
                        <p class="text-gray-600 leading-relaxed">{{ __('Currently, you can import data manually through the Export/Import CSV feature. We are developing direct integration with popular financial applications. For further assistance, please contact our support team.') }}</p>
                    </div>
                </div>

                <!-- FAQ Item 3 -->
                <div class="bg-gray-50 rounded-xl border border-gray-100 overflow-hidden">
                    <button class="faq-toggle w-full px-6 py-5 flex justify-between items-center text-left hover:bg-gray-100 transition" onclick="toggleFaq(this)">
                        <span class="font-semibold text-dark">{{ __('Is my data safe and encrypted?') }}</span>
                        <i class="fa-solid fa-chevron-down text-gray-400 transition-transform"></i>
                    </button>
                    <div class="faq-content hidden px-6 pb-5">
                        <p class="text-gray-600 leading-relaxed">{{ __('Data security is our top priority. All your data is encrypted using SSL/TLS technology and stored securely. We never share your personal information with third parties without permission.') }}</p>
                    </div>
                </div>

                <!-- FAQ Item 4 -->
                <div class="bg-gray-50 rounded-xl border border-gray-100 overflow-hidden">
                    <button class="faq-toggle w-full px-6 py-5 flex justify-between items-center text-left hover:bg-gray-100 transition" onclick="toggleFaq(this)">
                        <span class="font-semibold text-dark">{{ __('Can I use Motrac on multiple devices?') }}</span>
                        <i class="fa-solid fa-chevron-down text-gray-400 transition-transform"></i>
                    </button>
                    <div class="faq-content hidden px-6 pb-5">
                        <p class="text-gray-600 leading-relaxed">{{ __('Of course! Motrac is a responsive web-based application, so it can be accessed from desktop, tablet, or smartphone. Your data will be synchronized in real-time across all devices you use to log in.') }}</p>
                    </div>
                </div>

                <!-- FAQ Item 5 -->
                <div class="bg-gray-50 rounded-xl border border-gray-100 overflow-hidden">
                    <button class="faq-toggle w-full px-6 py-5 flex justify-between items-center text-left hover:bg-gray-100 transition" onclick="toggleFaq(this)">
                        <span class="font-semibold text-dark">{{ __('Is there a limit on the number of transactions I can record?') }}</span>
                        <i class="fa-solid fa-chevron-down text-gray-400 transition-transform"></i>
                    </button>
                    <div class="faq-content hidden px-6 pb-5">
                        <p class="text-gray-600 leading-relaxed">{{ __('There is no limit on the number of transactions for free accounts. You can record as many transactions as you need. All recording, reporting, and analysis features are available without limits.') }}</p>
                    </div>
                </div>

                <!-- FAQ Item 6 -->
                <div class="bg-gray-50 rounded-xl border border-gray-100 overflow-hidden">
                    <button class="faq-toggle w-full px-6 py-5 flex justify-between items-center text-left hover:bg-gray-100 transition" onclick="toggleFaq(this)">
                        <span class="font-semibold text-dark">{{ __('How do I delete my account?') }}</span>
                        <i class="fa-solid fa-chevron-down text-gray-400 transition-transform"></i>
                    </button>
                    <div class="faq-content hidden px-6 pb-5">
                        <p class="text-gray-600 leading-relaxed">{{ __('You can delete your account at any time through the Settings page. After deleting your account, all your data will be permanently deleted and cannot be recovered. Make sure to export important data first if needed.') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-white border-t border-gray-200 py-8">
        <div class="max-w-7xl mx-auto px-4 text-center text-gray-400 text-sm">
            &copy; 2024 Motrac Money Tracker. All rights reserved.
        </div>
    </footer>

    <!-- FAQ JavaScript -->
    <script>
        function toggleFaq(button) {
            const content = button.nextElementSibling;
            const icon = button.querySelector('i');
            const isOpen = !content.classList.contains('hidden');
            
            // Close all other FAQs
            document.querySelectorAll('.faq-content').forEach(item => {
                if (item !== content) {
                    item.classList.add('hidden');
                }
            });
            document.querySelectorAll('.faq-toggle i').forEach(item => {
                if (item !== icon) {
                    item.classList.remove('rotate-180');
                }
            });
            
            // Toggle current FAQ
            if (isOpen) {
                content.classList.add('hidden');
                icon.classList.remove('rotate-180');
            } else {
                content.classList.remove('hidden');
                icon.classList.add('rotate-180');
            }
        }

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Function to toggle mobile menu
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            if (menu) {
                const isHidden = menu.classList.contains('hidden');
                if (isHidden) {
                    menu.classList.remove('hidden');
                    document.body.style.overflow = 'hidden'; // Prevent body scroll when menu is open
                } else {
                    menu.classList.add('hidden');
                    document.body.style.overflow = ''; // Restore body scroll
                }
            }
        }
        
        // Close mobile menu when window is resized to desktop
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 768) {
                const menu = document.getElementById('mobileMenu');
                if (menu && !menu.classList.contains('hidden')) {
                    menu.classList.add('hidden');
                    document.body.style.overflow = '';
                }
            }
        });

        // Function to switch language
        function switchLanguage(locale) {
            if (locale === 'id' || locale === 'en') {
                // Preserve current URL parameters
                const currentUrl = new URL(window.location.href);
                const newUrl = '/language/' + locale + '?redirect=' + encodeURIComponent(currentUrl.pathname + currentUrl.search);
                window.location.href = newUrl;
            }
        }
    </script>

</body>
</html>
