<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ auth()->user() && auth()->user()->dark_mode ? 'dark' : '' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Verify Your Email') }} - Motrac</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#10B981',
                        dark: '#1F2937'
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-8 border border-gray-200 dark:border-gray-700">
            <!-- Logo -->
            <div class="flex justify-center mb-6">
                <div class="w-16 h-16 bg-primary rounded-xl flex items-center justify-center text-white text-2xl">
                    <i class="fa-solid fa-wallet"></i>
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
</body>
</html>

