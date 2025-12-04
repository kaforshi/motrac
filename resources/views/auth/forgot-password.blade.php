<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ auth()->user() && auth()->user()->dark_mode ? 'dark' : '' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Forgot Password') }} - Motrac</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: { primary: '#10B981', dark: '#1E293B' }
                }
            }
        }
    </script>
</head>
<body class="h-screen bg-white dark:bg-gray-900 font-sans text-slate-800 dark:text-gray-100">

    <div class="flex h-full w-full">
        
        <!-- Left Side: Branding / Visual (Hidden on Mobile) -->
        <div class="hidden lg:flex w-1/2 bg-gradient-to-br from-emerald-600 to-teal-800 text-white flex-col justify-between p-12 relative overflow-hidden">
            <!-- Decorative Circle -->
            <div class="absolute top-0 right-0 -mr-20 -mt-20 w-80 h-80 rounded-full bg-white opacity-10 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-80 h-80 rounded-full bg-emerald-400 opacity-10 blur-3xl"></div>

            <!-- Logo and Language Switch -->
            <div class="flex items-center justify-between w-full z-10">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-white/20 backdrop-blur rounded-lg flex items-center justify-center font-bold">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                    <span class="text-xl font-bold tracking-tight">Motrac</span>
                </div>
                <!-- Language Toggle Switch -->
                <div class="flex items-center bg-white/20 backdrop-blur border border-white/30 rounded-full p-1">
                    <button onclick="switchLanguage('id')" class="px-3 py-1.5 rounded-full text-sm font-semibold transition-all {{ app()->getLocale() === 'id' ? 'bg-white/30 text-white' : 'text-white/70' }}">
                        ID
                    </button>
                    <button onclick="switchLanguage('en')" class="px-3 py-1.5 rounded-full text-sm font-semibold transition-all {{ app()->getLocale() === 'en' ? 'bg-white/30 text-white' : 'text-white/70' }}">
                        EN
                    </button>
                </div>
            </div>

            <!-- Quote/Text -->
            <div class="z-10 max-w-md">
                <h2 class="text-4xl font-bold mb-6 leading-tight">{{ __('Reset Your Password') }}</h2>
                <p class="text-emerald-100 text-lg leading-relaxed">
                    {{ __('Enter your email address and we will send you a link to reset your password.') }}
                </p>
            </div>

            <!-- Copyright -->
            <div class="text-xs text-emerald-200/60 z-10">
                &copy; 2024 Motrac Financial Technologies.
            </div>
        </div>

        <!-- Right Side: Form -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8 bg-white dark:bg-gray-800">
            <div class="w-full max-w-md space-y-8">
                
                <!-- Mobile Logo (Only visible on mobile) -->
                <div class="lg:hidden flex justify-center mb-6">
                    <div class="w-10 h-10 bg-primary rounded-lg flex items-center justify-center text-white font-bold text-xl">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                </div>

                <div class="text-center lg:text-left">
                    <h2 class="text-3xl font-bold text-dark dark:text-white">{{ __('Forgot Password') }}</h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-2">{{ __('Enter your email address and we will send you a link to reset your password.') }}</p>
                </div>

                @if (session('status'))
                    <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 px-4 py-3 rounded-xl text-sm">
                        <i class="fa-solid fa-circle-check mr-2"></i>
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 px-4 py-3 rounded-xl text-sm">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                    @csrf
                    
                    <!-- Email Input -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Email Address') }}</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <i class="fa-regular fa-envelope"></i>
                            </div>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" required placeholder="nama@email.com" class="pl-10 w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-dark dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition text-sm">
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="w-full bg-primary hover:bg-emerald-600 text-white font-bold py-3.5 rounded-xl transition shadow-lg shadow-emerald-200 transform active:scale-95">
                        <i class="fa-solid fa-paper-plane mr-2"></i>
                        {{ __('Send Password Reset Link') }}
                    </button>
                </form>

                <!-- Footer -->
                <div class="text-center space-y-2">
                    <a href="{{ route('login') }}" class="block text-sm text-gray-600 dark:text-gray-400 hover:text-primary dark:hover:text-emerald-400 transition">
                        <i class="fa-solid fa-arrow-left mr-2"></i>
                        {{ __('Back to Login') }}
                    </a>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Remember your password?') }} 
                        <a href="{{ route('login') }}" class="font-bold text-primary hover:text-emerald-700 dark:hover:text-emerald-400 transition">{{ __('Login') }}</a>
                    </p>
                </div>
            </div>
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
