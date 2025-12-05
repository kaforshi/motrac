<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ auth()->user() && auth()->user()->dark_mode ? 'dark' : '' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <title>{{ __('Verify Your Email') }} - Motrac</title>
    
    <!-- Vite CSS & JS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-8 border border-gray-200 dark:border-gray-700">
            <!-- Logo and Language Switch -->
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('logo.png') }}" alt="Motrac" class="h-16 w-auto">
                    <span class="text-2xl font-extrabold tracking-tight text-dark dark:text-white" style="font-family: 'Inter', sans-serif; font-weight: 800;">Motrac</span>
                </div>
                <!-- Language Toggle Switch -->
                <div class="flex items-center bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-full p-1">
                    <button onclick="switchLanguage('id')" class="px-3 py-1.5 rounded-full text-sm font-semibold transition-all {{ app()->getLocale() === 'id' ? 'bg-dark dark:bg-gray-600 text-white' : 'text-gray-500 dark:text-gray-400' }}">
                        ID
                    </button>
                    <button onclick="switchLanguage('en')" class="px-3 py-1.5 rounded-full text-sm font-semibold transition-all {{ app()->getLocale() === 'en' ? 'bg-dark dark:bg-gray-600 text-white' : 'text-gray-500 dark:text-gray-400' }}">
                        EN
                    </button>
                </div>
            </div>

            <!-- Icon -->
            <div class="flex justify-center mb-6">
                <div class="w-20 h-20 bg-emerald-100 dark:bg-emerald-900/30 rounded-full flex items-center justify-center">
                    <i class="fa-solid fa-envelope-circle-check text-4xl text-emerald-600 dark:text-emerald-400"></i>
                </div>
            </div>

            <!-- Title -->
            <h1 class="text-2xl font-bold text-center text-dark dark:text-white mb-2">
                {{ __('Verify Your Email') }}
            </h1>

            <!-- Message -->
            <p class="text-center text-gray-600 dark:text-gray-400 mb-6">
                {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
            </p>

            <!-- Success Message -->
            @if (session('message'))
                <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 px-4 py-3 rounded-xl text-sm mb-4">
                    <i class="fa-solid fa-circle-check mr-2"></i>
                    {{ session('message') }}
                </div>
            @endif

            <!-- Error Message -->
            @if (session('error'))
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 px-4 py-3 rounded-xl text-sm mb-4">
                    <i class="fa-solid fa-circle-exclamation mr-2"></i>
                    {{ session('error') }}
                </div>
            @endif

            <!-- Resend Form -->
            <form method="POST" action="{{ route('verification.send') }}" class="mb-6">
                @csrf
                <button type="submit" class="w-full bg-primary hover:bg-emerald-600 text-white px-6 py-3 rounded-xl text-sm font-medium transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-paper-plane"></i>
                    {{ __('Resend Verification Email') }}
                </button>
            </form>

            <!-- Logout -->
            <form method="POST" action="{{ route('logout') }}" class="text-center">
                @csrf
                <button type="submit" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 transition">
                    {{ __('Logout') }}
                </button>
            </form>
        </div>
    </div>

    <script>
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

