<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <title>Motrac - {{ __('Manage Your Finances') }} {{ __('Without Hassle') }}</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Vite CSS & JS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-slate-600 bg-slate-50 overflow-x-hidden">

    <!-- Navbar -->
    <nav class="fixed w-full z-50 transition-all duration-300 glass-nav" id="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <a href="{{ route('landing') }}" class="flex items-center gap-2 group">
                    <img src="{{ asset('logo.png') }}" alt="Motrac" class="h-10 w-auto group-hover:scale-105 transition">
                    <span class="text-2xl font-bold text-dark tracking-tight">Motrac</span>
                </a>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#features" class="text-sm font-semibold text-slate-500 hover:text-primary-600 transition">{{ __('Features') }}</a>
                    <a href="#how-it-works" class="text-sm font-semibold text-slate-500 hover:text-primary-600 transition">{{ __('How It Works') }}</a>
                    <a href="#pricing" class="text-sm font-semibold text-slate-500 hover:text-primary-600 transition">{{ __('Pricing') }}</a>
                    <a href="#faq" class="text-sm font-semibold text-slate-500 hover:text-primary-600 transition">{{ __('FAQ') }}</a>
                </div>

                <!-- CTA Button -->
                <div class="hidden md:flex items-center gap-4">
                    <!-- Language Toggle -->
                    <div class="flex items-center bg-gray-100 rounded-full p-1">
                        <a href="{{ route('language.switch', ['locale' => 'id', 'redirect' => url()->current()]) }}" class="px-3 py-1.5 rounded-full text-sm font-semibold transition-all {{ app()->getLocale() === 'id' ? 'bg-primary-600 text-white' : 'text-gray-600' }}">
                            ID
                        </a>
                        <a href="{{ route('language.switch', ['locale' => 'en', 'redirect' => url()->current()]) }}" class="px-3 py-1.5 rounded-full text-sm font-semibold transition-all {{ app()->getLocale() === 'en' ? 'bg-primary-600 text-white' : 'text-gray-600' }}">
                            EN
                        </a>
                    </div>
                    <a href="{{ route('login') }}" class="bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 text-sm font-bold px-6 py-3 rounded-full transition shadow-sm hover:shadow-md">{{ __('Login') }}</a>
                    <a href="{{ route('register') }}" class="bg-dark hover:bg-slate-800 text-white text-sm font-bold px-6 py-3 rounded-full transition shadow-xl shadow-slate-900/20 hover:-translate-y-1">
                        {{ __('Register Free') }}
                    </a>
                </div>

                <!-- Mobile Menu Button -->
                <button class="md:hidden bg-white hover:bg-slate-50 text-slate-600 hover:text-primary-600 border border-slate-200 rounded-lg p-2 focus:outline-none transition" onclick="toggleMobileMenu()">
                    <i class="fa-solid fa-bars text-2xl"></i>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden bg-white border-t border-gray-100 absolute w-full left-0 top-20 shadow-lg">
            <div class="px-4 py-6 space-y-4">
                <a href="#features" class="block text-base font-semibold text-slate-600">{{ __('Features') }}</a>
                <a href="#how-it-works" class="block text-base font-semibold text-slate-600">{{ __('How It Works') }}</a>
                <a href="#pricing" class="block text-base font-semibold text-slate-600">{{ __('Pricing') }}</a>
                <a href="#faq" class="block text-base font-semibold text-slate-600">{{ __('FAQ') }}</a>
                <hr class="border-gray-100">
                <div class="flex items-center gap-2">
                    <a href="{{ route('language.switch', ['locale' => 'id', 'redirect' => url()->current()]) }}" class="px-3 py-1.5 rounded-full text-sm font-semibold transition-all {{ app()->getLocale() === 'id' ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-600' }}">
                        ID
                    </a>
                    <a href="{{ route('language.switch', ['locale' => 'en', 'redirect' => url()->current()]) }}" class="px-3 py-1.5 rounded-full text-sm font-semibold transition-all {{ app()->getLocale() === 'en' ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-600' }}">
                        EN
                    </a>
                </div>
                <a href="{{ route('login') }}" class="block w-full text-center bg-white border border-slate-300 text-slate-700 font-bold py-3 rounded-xl hover:bg-slate-50 transition">{{ __('Login') }}</a>
                <a href="{{ route('register') }}" class="block w-full text-center bg-primary-600 text-white font-bold py-3 rounded-xl hover:bg-primary-700 transition">{{ __('Register Free') }}</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="pt-32 pb-20 lg:pt-48 lg:pb-32 hero-pattern relative overflow-hidden">
        <!-- Background Blobs -->
        <div class="absolute top-20 right-0 w-[500px] h-[500px] bg-primary-200/40 rounded-full blur-3xl -z-10 animate-float"></div>
        <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-blue-200/40 rounded-full blur-3xl -z-10 animate-float-delayed"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-2 hero-section-grid gap-12 items-center">
            <!-- Text Content -->
            <div class="max-w-2xl text-center lg:text-left animate-fade-up">
                
                <h1 class="text-5xl sm:text-6xl lg:text-7xl font-extrabold text-dark tracking-tight leading-[1.1] mb-6">
                    @if(app()->getLocale() === 'id')
                        Uang Anda, <br>
                        <span class="text-gradient">Kendali Anda.</span>
                    @else
                        {{ __('Manage Your Finances') }}, <br>
                        <span class="text-gradient">{{ __('Without Hassle') }}</span>
                    @endif
                </h1>
                <p class="text-lg sm:text-xl text-slate-500 mb-8 leading-relaxed">
                    @if(app()->getLocale() === 'id')
                        Hentikan kebiasaan mencatat manual. Motrac membantu Anda melacak, mengatur budget, dan menganalisa kekayaan bersih dalam satu aplikasi cerdas.
                    @else
                        {{ __('Forget boring manual records. Monitor your cash flow, digital wallets, and investments in one smart dashboard.') }}
                    @endif
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                    <a href="{{ route('register') }}" class="bg-primary-600 hover:bg-primary-700 text-white text-lg font-bold px-8 py-4 rounded-full transition shadow-xl shadow-primary-600/30 hover:-translate-y-1 flex items-center justify-center gap-2">
                        @if(app()->getLocale() === 'id')
                            Mulai Sekarang <i class="fa-solid fa-arrow-right"></i>
                        @else
                            {{ __('Start Tracking Now') }} <i class="fa-solid fa-arrow-right"></i>
                        @endif
                    </a>
                </div>
                
                <div class="mt-10 flex items-center justify-center lg:justify-start gap-6 text-sm font-semibold text-slate-400">
                    <div class="flex items-center gap-2"><i class="fa-solid fa-check text-primary-500"></i> @if(app()->getLocale() === 'id') Gratis Selamanya @else {{ __('Free forever for basic features. No credit card required.') }} @endif</div>
                    <div class="flex items-center gap-2"><i class="fa-solid fa-check text-primary-500"></i> @if(app()->getLocale() === 'id') Aman & Terenkripsi @else {{ __('Encrypted data security') }} @endif</div>
                </div>
            </div>

            <!-- Visual Mockup (CSS Only) -->
            <div class="relative lg:h-[600px] flex items-center justify-center perspective-1000">
                <!-- Main Card (Dashboard) -->
                <div class="relative w-full max-w-md bg-white rounded-3xl shadow-2xl border border-slate-100 p-6 z-20 animate-float transform rotate-y-12 rotate-x-6 hover:rotate-0 transition duration-500">
                    <!-- Fake Header -->
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <div class="w-20 h-4 bg-slate-200 rounded mb-2"></div>
                            <div class="w-32 h-2 bg-slate-100 rounded"></div>
                        </div>
                        <div class="w-10 h-10 bg-slate-100 rounded-full"></div>
                    </div>
                    <!-- Fake Total Balance -->
                    <div class="bg-dark rounded-2xl p-6 mb-6 text-white relative overflow-hidden">
                        <div class="absolute right-0 top-0 w-32 h-32 bg-white/10 rounded-full blur-2xl -mr-10 -mt-10"></div>
                        <p class="text-xs text-slate-400 mb-1">{{ __('Total Balance') }}</p>
                        <h3 class="text-3xl font-bold">Rp 15.450.000</h3>
                        <div class="flex gap-2 mt-4">
                            <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center"><i class="fa-solid fa-plus text-xs"></i></div>
                            <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center"><i class="fa-solid fa-minus text-xs"></i></div>
                        </div>
                    </div>
                    <!-- Fake Chart -->
                    <div class="flex items-end justify-between h-32 gap-2 mb-6">
                        <div class="w-full bg-slate-100 rounded-t-lg h-[40%]"></div>
                        <div class="w-full bg-slate-100 rounded-t-lg h-[70%]"></div>
                        <div class="w-full bg-primary-500 rounded-t-lg h-[50%] shadow-lg shadow-primary-500/40"></div>
                        <div class="w-full bg-slate-100 rounded-t-lg h-[80%]"></div>
                        <div class="w-full bg-slate-100 rounded-t-lg h-[60%]"></div>
                    </div>
                    <!-- Fake List -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-orange-100 rounded-xl"></div>
                                <div>
                                    <div class="w-24 h-3 bg-slate-200 rounded mb-1"></div>
                                    <div class="w-16 h-2 bg-slate-100 rounded"></div>
                                </div>
                            </div>
                            <div class="w-16 h-3 bg-red-100 rounded"></div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-blue-100 rounded-xl"></div>
                                <div>
                                    <div class="w-24 h-3 bg-slate-200 rounded mb-1"></div>
                                    <div class="w-16 h-2 bg-slate-100 rounded"></div>
                                </div>
                            </div>
                            <div class="w-16 h-3 bg-red-100 rounded"></div>
                        </div>
                    </div>
                </div>

                <!-- Floating Elements (Budget Card) -->
                <div class="absolute -left-10 bottom-20 bg-white p-4 rounded-2xl shadow-xl border border-slate-100 z-30 animate-float-delayed w-48 hidden sm:block">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center text-red-500"><i class="fa-solid fa-burger"></i></div>
                        <div>
                            <p class="text-xs font-bold text-dark">@if(app()->getLocale() === 'id') Makanan @else {{ __('Category') }} @endif</p>
                            <p class="text-[10px] text-slate-400">Limit Rp 1.5jt</p>
                        </div>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-red-500 h-full w-[85%]"></div>
                    </div>
                    <p class="text-[10px] text-right text-red-500 font-bold mt-1">@if(app()->getLocale() === 'id') Bahaya! @else ! @endif</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-10 border-y border-slate-200 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center reveal">
                <div>
                    <h3 class="text-3xl font-extrabold text-dark mb-1">100%</h3>
                    <p class="text-sm font-semibold text-slate-400 uppercase tracking-wider">@if(app()->getLocale() === 'id') Gratis Selamanya @else {{ __('Free forever for basic features. No credit card required.') }} @endif</p>
                </div>
                <div>
                    <h3 class="text-3xl font-extrabold text-dark mb-1">AES-256</h3>
                    <p class="text-sm font-semibold text-slate-400 uppercase tracking-wider">@if(app()->getLocale() === 'id') Enkripsi Data @else {{ __('Encrypted data security') }} @endif</p>
                </div>
                <div>
                    <h3 class="text-3xl font-extrabold text-dark mb-1">Unlimited</h3>
                    <p class="text-sm font-semibold text-slate-400 uppercase tracking-wider">@if(app()->getLocale() === 'id') Dompet & Transaksi @else {{ __('Unlimited transactions') }} @endif</p>
                </div>
                <div>
                    <h3 class="text-3xl font-extrabold text-dark mb-1">24/7</h3>
                    <p class="text-sm font-semibold text-slate-400 uppercase tracking-wider">@if(app()->getLocale() === 'id') Akses Real-time @else Real-time Access @endif</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Grid -->
    <section id="features" class="py-24 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16 reveal">
                <h2 class="text-primary-600 font-bold tracking-wide uppercase text-sm mb-3">{{ __('Features') }}</h2>
                <h3 class="text-3xl md:text-4xl font-extrabold text-dark mb-4">@if(app()->getLocale() === 'id') Satu Aplikasi, Solusi Total. @else {{ __('Everything You Need') }} @endif</h3>
                <p class="text-lg text-slate-500">@if(app()->getLocale() === 'id') Kami menyederhanakan pengelolaan keuangan yang rumit menjadi pengalaman yang menyenangkan. @else {{ __('We make advanced features simple, so you can focus on your financial goals.') }} @endif</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8 features-grid">
                <!-- Feature 1 -->
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 hover:shadow-xl hover:-translate-y-2 transition duration-300 reveal group">
                    <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 text-2xl mb-6 group-hover:bg-blue-600 group-hover:text-white transition">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                    <h4 class="text-xl font-bold text-dark mb-3">{{ __('Multi-Wallet Sync') }}</h4>
                    <p class="text-slate-500 leading-relaxed">{{ __('Manage Cash, BCA, Gopay, and OVO in one screen. No need to open multiple apps.') }}</p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 hover:shadow-xl hover:-translate-y-2 transition duration-300 reveal group" style="transition-delay: 100ms;">
                    <div class="w-14 h-14 bg-primary-50 rounded-2xl flex items-center justify-center text-primary-600 text-2xl mb-6 group-hover:bg-primary-600 group-hover:text-white transition">
                        <i class="fa-solid fa-chart-pie"></i>
                    </div>
                    <h4 class="text-xl font-bold text-dark mb-3">{{ __('Smart Budgeting') }}</h4>
                    <p class="text-slate-500 leading-relaxed">@if(app()->getLocale() === 'id') Pasang alarm batas belanja per kategori. Dapatkan notifikasi 'lampu merah' sebelum dompet jebol. @else {{ __('Set spending limit alerts per category. We\'ll remind you before your wallet breaks.') }} @endif</p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 hover:shadow-xl hover:-translate-y-2 transition duration-300 reveal group" style="transition-delay: 200ms;">
                    <div class="w-14 h-14 bg-purple-50 rounded-2xl flex items-center justify-center text-purple-600 text-2xl mb-6 group-hover:bg-purple-600 group-hover:text-white transition">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                    </div>
                    <h4 class="text-xl font-bold text-dark mb-3">@if(app()->getLocale() === 'id') Laporan Visual @else {{ __('Visual Reports') }} @endif</h4>
                    <p class="text-slate-500 leading-relaxed">@if(app()->getLocale() === 'id') Grafik intuitif yang menjawab pertanyaan "Uang saya habis kemana?" dalam hitungan detik. @else {{ __('Understand where your money goes through intuitive charts and export data to Excel/PDF.') }} @endif</p>
                </div>
                
                 <!-- Feature 4 -->
                 <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 hover:shadow-xl hover:-translate-y-2 transition duration-300 reveal group">
                    <div class="w-14 h-14 bg-orange-50 rounded-2xl flex items-center justify-center text-orange-600 text-2xl mb-6 group-hover:bg-orange-600 group-hover:text-white transition">
                        <i class="fa-solid fa-bolt"></i>
                    </div>
                    <h4 class="text-xl font-bold text-dark mb-3">@if(app()->getLocale() === 'id') Input Kilat @else Quick Input @endif</h4>
                    <p class="text-slate-500 leading-relaxed">@if(app()->getLocale() === 'id') Teknologi Quick-Add kami memungkinkan Anda mencatat transaksi kurang dari 3 detik. @else Record transactions in less than 3 seconds with our Quick-Add technology. @endif</p>
                </div>

                <!-- Feature 5 (Wide) -->
                <div class="md:col-span-2 bg-gradient-to-br from-dark to-slate-900 p-8 rounded-3xl shadow-lg hover:shadow-xl hover:-translate-y-2 transition duration-300 reveal relative overflow-hidden text-white group">
                    <div class="absolute right-0 bottom-0 opacity-10 transform translate-x-10 translate-y-10 group-hover:translate-x-0 group-hover:translate-y-0 transition duration-500">
                        <i class="fa-solid fa-shield-halved text-9xl"></i>
                    </div>
                    <div class="relative z-10">
                        <div class="w-14 h-14 bg-white/10 backdrop-blur rounded-2xl flex items-center justify-center text-white text-2xl mb-6">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <h4 class="text-xl font-bold mb-3">@if(app()->getLocale() === 'id') Keamanan Bank-Grade @else Bank-Grade Security @endif</h4>
                        <p class="text-slate-300 leading-relaxed max-w-lg">
                            @if(app()->getLocale() === 'id')
                                Data Anda dienkripsi dengan standar AES-256. Kami tidak pernah menjual data pribadi Anda ke pihak ketiga. Privasi Anda adalah prioritas utama kami.
                            @else
                                {{ __('Data security is our top priority. All your data is encrypted using SSL/TLS technology and stored securely. We never share your personal information with third parties without permission.') }}
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section id="how-it-works" class="py-24 bg-white overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center how-it-works-grid">
                <div class="order-2 lg:order-1 relative reveal">
                    <!-- Decorative Circle Background -->
                    <div class="absolute inset-0 bg-primary-100 rounded-full blur-3xl opacity-50"></div>
                    <!-- Updated Image: Shows data analytics/graphs on screen, fitting for a money tracker -->
                    <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Analisa Keuangan Digital" class="relative z-10 rounded-3xl shadow-2xl transform -rotate-3 hover:rotate-0 transition duration-500">
                </div>
                <div class="order-1 lg:order-2 reveal">
                    <h2 class="text-primary-600 font-bold tracking-wide uppercase text-sm mb-3">@if(app()->getLocale() === 'id') Langkah Mudah @else {{ __('How It Works') }} @endif</h2>
                    <h3 class="text-3xl md:text-4xl font-extrabold text-dark mb-8">@if(app()->getLocale() === 'id') Mulai dalam 3 Menit @else {{ __('Get started with Motrac in just a few simple steps') }} @endif</h3>
                    
                    <div class="space-y-8">
                        <div class="flex gap-4 group">
                            <div class="flex-shrink-0 w-12 h-12 bg-primary-100 text-primary-600 rounded-full flex items-center justify-center font-bold text-xl group-hover:bg-primary-600 group-hover:text-white transition">1</div>
                            <div>
                                <h4 class="text-xl font-bold text-dark mb-2">@if(app()->getLocale() === 'id') Buat Akun Gratis @else {{ __('Create Your Account') }} @endif</h4>
                                <p class="text-slate-500">@if(app()->getLocale() === 'id') Daftar menggunakan email. Tanpa perlu kartu kredit. @else {{ __('Sign up for free in less than a minute. No credit card required.') }} @endif</p>
                            </div>
                        </div>
                        <div class="flex gap-4 group">
                            <div class="flex-shrink-0 w-12 h-12 bg-primary-100 text-primary-600 rounded-full flex items-center justify-center font-bold text-xl group-hover:bg-primary-600 group-hover:text-white transition">2</div>
                            <div>
                                <h4 class="text-xl font-bold text-dark mb-2">@if(app()->getLocale() === 'id') Atur Dompet & Budget @else {{ __('Add Your Wallets') }} @endif</h4>
                                <p class="text-slate-500">@if(app()->getLocale() === 'id') Tambahkan akun bank/e-wallet dan tentukan batas pengeluaran bulananmu. @else {{ __('Connect your cash, bank accounts, and digital wallets to track everything in one place.') }} @endif</p>
                            </div>
                        </div>
                        <div class="flex gap-4 group">
                            <div class="flex-shrink-0 w-12 h-12 bg-primary-100 text-primary-600 rounded-full flex items-center justify-center font-bold text-xl group-hover:bg-primary-600 group-hover:text-white transition">3</div>
                            <div>
                                <h4 class="text-xl font-bold text-dark mb-2">@if(app()->getLocale() === 'id') Catat & Pantau @else {{ __('Track & Analyze') }} @endif</h4>
                                <p class="text-slate-500">@if(app()->getLocale() === 'id') Mulai catat transaksi. Grafik akan otomatis terbentuk secara real-time. @else {{ __('Start recording transactions and watch your financial insights grow with beautiful charts and reports.') }} @endif</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="py-24 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16 reveal">
                <h2 class="text-primary-600 font-bold tracking-wide uppercase text-sm mb-3">{{ __('Pricing') }}</h2>
                <h3 class="text-3xl md:text-4xl font-extrabold text-dark mb-4">@if(app()->getLocale() === 'id') Investasi Kecil, Dampak Besar. @else {{ __('Choose the plan that works best for you') }} @endif</h3>
                <p class="text-lg text-slate-500">@if(app()->getLocale() === 'id') Nikmati semua fitur canggih tanpa biaya langganan bulanan. @else {{ __('Simple, transparent pricing. Free forever.') }} @endif</p>
            </div>

            <div class="flex justify-center items-center">
                
                <!-- Free Plan (Now the only plan) -->
                <div class="bg-white p-8 rounded-3xl shadow-xl border-2 border-primary-500 hover:shadow-2xl transition duration-300 reveal relative w-full max-w-md">
                    <div class="absolute top-0 right-0 bg-primary-500 text-white text-xs font-bold px-3 py-1 rounded-bl-xl rounded-tr-2xl">BEST VALUE</div>
                    <h4 class="text-2xl font-bold text-dark mb-2">@if(app()->getLocale() === 'id') Motrac Gratis @else {{ __('Free') }} @endif</h4>
                    <p class="text-slate-500 text-sm mb-6">@if(app()->getLocale() === 'id') Akses penuh ke semua fitur tanpa batasan. @else {{ __('Perfect for getting started') }} @endif</p>
                    <div class="mb-6">
                        <span class="text-5xl font-extrabold text-dark">@if(app()->getLocale() === 'id') Rp 0 @else $0 @endif</span>
                        <span class="text-slate-400 font-medium">/ @if(app()->getLocale() === 'id') selamanya @else {{ __('month') }} @endif</span>
                    </div>
                    <a href="{{ route('register') }}" class="block w-full py-4 px-6 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl text-center transition mb-8 shadow-lg shadow-primary-500/30">
                        @if(app()->getLocale() === 'id') Daftar Sekarang @else {{ __('Get Started Free') }} @endif
                    </a>
                    <ul class="space-y-4 text-sm text-slate-600">
                        <li class="flex items-center gap-3"><i class="fa-solid fa-check text-primary-500"></i> <strong>@if(app()->getLocale() === 'id') Unlimited @else {{ __('Unlimited transactions') }} @endif</strong> @if(app()->getLocale() === 'id') Transaksi & Dompet @endif</li>
                        <li class="flex items-center gap-3"><i class="fa-solid fa-check text-primary-500"></i> @if(app()->getLocale() === 'id') Smart Budgeting Alerts @else {{ __('Multiple wallets & categories') }} @endif</li>
                        <li class="flex items-center gap-3"><i class="fa-solid fa-check text-primary-500"></i> @if(app()->getLocale() === 'id') Laporan Bulanan Lengkap @else {{ __('Basic reports & charts') }} @endif</li>
                        <li class="flex items-center gap-3"><i class="fa-solid fa-check text-primary-500"></i> @if(app()->getLocale() === 'id') Export Data (Excel/CSV) @else {{ __('Budget planning') }} @endif</li>
                        <li class="flex items-center gap-3"><i class="fa-solid fa-check text-primary-500"></i> @if(app()->getLocale() === 'id') Multi-Device Sync @else {{ __('Data export (CSV/PDF)') }} @endif</li>
                    </ul>
                </div>

            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="py-24 bg-white">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 reveal">
                <h2 class="text-primary-600 font-bold tracking-wide uppercase text-sm mb-3">{{ __('FAQ') }}</h2>
                <h3 class="text-3xl md:text-4xl font-extrabold text-dark">@if(app()->getLocale() === 'id') Pertanyaan Umum @else {{ __('Frequently Asked Questions') }} @endif</h3>
            </div>

            <div class="space-y-4 reveal">
                <!-- FAQ 1 -->
                <details class="group bg-slate-50 rounded-2xl p-6 [&_summary::-webkit-details-marker]:hidden border border-transparent hover:border-slate-200 transition cursor-pointer">
                    <summary class="flex items-center justify-between font-bold text-dark text-lg">
                        {{ __('Is Motrac really free?') }}
                        <span class="ml-4 transition-transform duration-300 group-open:rotate-180">
                            <i class="fa-solid fa-chevron-down text-slate-400"></i>
                        </span>
                    </summary>
                    <p class="text-slate-500 mt-4 leading-relaxed">
                        @if(app()->getLocale() === 'id')
                            Ya, Motrac sepenuhnya gratis untuk digunakan. Kami percaya bahwa manajemen keuangan yang baik harus dapat diakses oleh semua orang tanpa hambatan biaya.
                        @else
                            {{ __('Yes, Motrac offers basic features for free forever. You can record transactions, manage wallets, create categories, and view basic reports at no cost. No credit card required to get started.') }}
                        @endif
                    </p>
                </details>

                <!-- FAQ 2 -->
                <details class="group bg-slate-50 rounded-2xl p-6 [&_summary::-webkit-details-marker]:hidden border border-transparent hover:border-slate-200 transition cursor-pointer">
                    <summary class="flex items-center justify-between font-bold text-dark text-lg">
                        {{ __('Is my data safe and encrypted?') }}
                        <span class="ml-4 transition-transform duration-300 group-open:rotate-180">
                            <i class="fa-solid fa-chevron-down text-slate-400"></i>
                        </span>
                    </summary>
                    <p class="text-slate-500 mt-4 leading-relaxed">
                        @if(app()->getLocale() === 'id')
                            Keamanan adalah prioritas kami. Semua data dienkripsi dengan standar enkripsi AES-256 yang setara dengan keamanan bank. Kami tidak pernah menjual data Anda ke pihak ketiga.
                        @else
                            {{ __('Data security is our top priority. All your data is encrypted using SSL/TLS technology and stored securely. We never share your personal information with third parties without permission.') }}
                        @endif
                    </p>
                </details>

                <!-- FAQ 3 -->
                <details class="group bg-slate-50 rounded-2xl p-6 [&_summary::-webkit-details-marker]:hidden border border-transparent hover:border-slate-200 transition cursor-pointer">
                    <summary class="flex items-center justify-between font-bold text-dark text-lg">
                        {{ __('How do I import data from other applications?') }}
                        <span class="ml-4 transition-transform duration-300 group-open:rotate-180">
                            <i class="fa-solid fa-chevron-down text-slate-400"></i>
                        </span>
                    </summary>
                    <p class="text-slate-500 mt-4 leading-relaxed">
                        @if(app()->getLocale() === 'id')
                            Tentu saja! Fitur export ke format .CSV dan .XLSX (Excel) tersedia secara gratis. Ini memudahkan Anda untuk melakukan analisa lebih lanjut atau backup data mandiri.
                        @else
                            {{ __('Currently, you can import data manually through the Export/Import CSV feature. We are developing direct integration with popular financial applications. For further assistance, please contact our support team.') }}
                        @endif
                    </p>
                </details>

            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-primary-600 rounded-[3rem] p-12 md:p-20 text-center relative overflow-hidden shadow-2xl shadow-primary-500/30 reveal">
                <!-- Background pattern -->
                <div class="absolute top-0 left-0 w-full h-full opacity-10">
                    <svg width="100%" height="100%" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                                <path d="M 40 0 L 0 0 0 40" fill="none" stroke="white" stroke-width="1"/>
                            </pattern>
                        </defs>
                        <rect width="100%" height="100%" fill="url(#grid)" />
                    </svg>
                </div>
                
                <div class="relative z-10">
                    <h2 class="text-3xl md:text-5xl font-extrabold text-white mb-6">@if(app()->getLocale() === 'id') Siap Merapikan <br>Keuangan Anda? @else {{ __('Start Your Financial Journey.') }} @endif</h2>
                    <p class="text-primary-100 text-lg mb-10 max-w-xl mx-auto">@if(app()->getLocale() === 'id') Bergabunglah dengan ribuan orang yang telah berhasil mencapai kebebasan finansial dengan Motrac. @else {{ __('Free forever, no credit card required.') }} @endif</p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="{{ route('register') }}" class="bg-white text-primary-700 font-bold text-lg px-8 py-4 rounded-full hover:bg-slate-50 transition shadow-lg transform hover:-translate-y-1">
                            @if(app()->getLocale() === 'id') Buat Akun Sekarang @else {{ __('Create New Account') }} @endif
                        </a>
                        <a href="{{ route('login') }}" class="bg-white text-primary-700 border-2 border-primary-500 font-bold text-lg px-8 py-4 rounded-full hover:bg-primary-50 transition shadow-lg">
                            {{ __('Login') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 pt-16 pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8 mb-12">
                <div class="col-span-1 md:col-span-2">
                    <a href="{{ route('landing') }}" class="flex items-center gap-2 mb-4">
                        <div class="w-8 h-8 bg-primary-600 rounded-lg flex items-center justify-center text-white text-lg">
                            <i class="fa-solid fa-wallet"></i>
                        </div>
                        <span class="text-xl font-bold text-dark">Motrac</span>
                    </a>
                    <p class="text-slate-500 max-w-xs mb-6">
                        @if(app()->getLocale() === 'id')
                            Platform manajemen keuangan pribadi #1 di Indonesia yang membantu Anda mencapai tujuan finansial lebih cepat.
                        @else
                            {{ __('Free forever for basic features. No credit card required.') }}
                        @endif
                    </p>
                    <div class="flex gap-4">
                        <a href="#" class="w-10 h-10 rounded-full bg-slate-50 flex items-center justify-center text-slate-600 hover:bg-primary-500 hover:text-white transition"><i class="fa-brands fa-twitter"></i></a>
                        <a href="#" class="w-10 h-10 rounded-full bg-slate-50 flex items-center justify-center text-slate-600 hover:bg-primary-500 hover:text-white transition"><i class="fa-brands fa-instagram"></i></a>
                        <a href="#" class="w-10 h-10 rounded-full bg-slate-50 flex items-center justify-center text-slate-600 hover:bg-primary-500 hover:text-white transition"><i class="fa-brands fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div>
                    <h4 class="font-bold text-dark mb-4">@if(app()->getLocale() === 'id') Produk @else {{ __('Features') }} @endif</h4>
                    <ul class="space-y-2 text-sm text-slate-500">
                        <li><a href="#features" class="hover:text-primary-600 transition">{{ __('Features') }}</a></li>
                        <li><a href="#pricing" class="hover:text-primary-600 transition">{{ __('Pricing') }}</a></li>
                        <li><a href="#faq" class="hover:text-primary-600 transition">{{ __('FAQ') }}</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold text-dark mb-4">{{ __('Account') }}</h4>
                    <ul class="space-y-2 text-sm text-slate-500">
                        <li><a href="{{ route('login') }}" class="hover:text-primary-600 transition">{{ __('Login') }}</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-primary-600 transition">{{ __('Register Free') }}</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-slate-100 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-slate-400 text-sm">&copy; {{ date('Y') }} Motrac. {{ __('All rights reserved.') }}</p>
                <div class="flex gap-6 text-sm text-slate-400">
                    <a href="#" class="hover:text-dark">Privacy Policy</a>
                    <a href="#" class="hover:text-dark">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script>
        // Mobile Menu Toggle
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        }

        // Scroll Animation Logic
        function reveal() {
            var reveals = document.querySelectorAll(".reveal");
            for (var i = 0; i < reveals.length; i++) {
                var windowHeight = window.innerHeight;
                var elementTop = reveals[i].getBoundingClientRect().top;
                var elementVisible = 150;
                if (elementTop < windowHeight - elementVisible) {
                    reveals[i].classList.add("active");
                }
            }
        }
        window.addEventListener("scroll", reveal);
        // Trigger once on load
        reveal();

        // Navbar Blur Effect on Scroll
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 10) {
                navbar.classList.add('shadow-sm');
            } else {
                navbar.classList.remove('shadow-sm');
            }
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                    // Close mobile menu if open
                    document.getElementById('mobile-menu').classList.add('hidden');
                }
            });
        });

    </script>
</body>
</html>
