<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ auth()->user() && auth()->user()->dark_mode ? 'dark' : '' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <title>{{ __('Reset Password') }} - Motrac</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

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
            <div class="flex items-center justify-between w-full z-20 relative">
                <div class="flex items-center gap-2">
                    <img src="{{ asset('logo.png') }}" alt="Motrac" class="h-8 w-auto">
                    <span class="text-xl font-extrabold tracking-tight text-white" style="font-family: 'Inter', sans-serif; font-weight: 800;">Motrac</span>
                </div>
                <!-- Language Toggle Switch -->
                <div class="flex items-center bg-white/20 backdrop-blur border border-white/30 rounded-full p-1 relative z-20">
                    <button onclick="switchLanguage('id')" class="px-3 py-1.5 rounded-full text-sm font-semibold transition-all cursor-pointer {{ app()->getLocale() === 'id' ? 'bg-white/30 text-white' : 'text-white/70' }}" type="button">
                        ID
                    </button>
                    <button onclick="switchLanguage('en')" class="px-3 py-1.5 rounded-full text-sm font-semibold transition-all cursor-pointer {{ app()->getLocale() === 'en' ? 'bg-white/30 text-white' : 'text-white/70' }}" type="button">
                        EN
                    </button>
                </div>
            </div>

            <!-- Quote/Text -->
            <div class="z-10 max-w-md">
                <h2 class="text-4xl font-bold mb-6 leading-tight">{{ __('Reset Your Password') }}</h2>
                <p class="text-emerald-100 text-lg leading-relaxed">
                    {{ __('Enter your new password below. Make sure it\'s strong and secure.') }}
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
                <div class="lg:hidden flex items-center justify-center gap-2 mb-6">
                    <img src="{{ asset('logo.png') }}" alt="Motrac" class="h-10 w-auto">
                    <span class="text-2xl font-extrabold tracking-tight text-dark dark:text-white" style="font-family: 'Inter', sans-serif; font-weight: 800;">Motrac</span>
                </div>

                <div class="text-center lg:text-left">
                    <h2 class="text-3xl font-bold text-dark dark:text-white">{{ __('Reset Password') }}</h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-2">{{ __('Enter your new password below.') }}</p>
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

                <form method="POST" action="{{ route('password.update') }}" class="space-y-6">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    
                    <!-- Email Input -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Email Address') }}</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <i class="fa-regular fa-envelope"></i>
                            </div>
                            <input type="email" id="email" name="email" value="{{ $email }}" required readonly class="pl-10 w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-dark dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition text-sm cursor-not-allowed">
                        </div>
                    </div>

                    <!-- Password Input -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('New Password') }}</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <i class="fa-solid fa-lock"></i>
                            </div>
                            <input type="password" id="password" name="password" required placeholder="••••••••" class="pl-10 pr-10 w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-dark dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition text-sm">
                            <button type="button" onclick="togglePassword('password', this)" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Confirm Password Input -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Confirm New Password') }}</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <i class="fa-solid fa-lock"></i>
                            </div>
                            <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="••••••••" class="pl-10 pr-10 w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-dark dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition text-sm">
                            <button type="button" onclick="togglePassword('password_confirmation', this)" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="w-full bg-primary hover:bg-emerald-600 text-white font-bold py-3.5 rounded-xl transition shadow-lg shadow-emerald-200 transform active:scale-95">
                        <i class="fa-solid fa-key mr-2"></i>
                        {{ __('Reset Password') }}
                    </button>
                </form>

                <!-- Footer -->
                <div class="text-center">
                    <a href="{{ route('login') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-primary dark:hover:text-emerald-400 transition">
                        <i class="fa-solid fa-arrow-left mr-2"></i>
                        {{ __('Back to Login') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(inputId, btn) {
            const input = document.getElementById(inputId);
            const icon = btn.querySelector('i');
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = "password";
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

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

