<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <title>{{ __('Login') }} - Motrac</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: { primary: '#10B981', dark: '#1E293B' }
                }
            }
        }
    </script>
</head>
<body class="h-screen bg-white font-sans text-slate-800">

    <div class="flex h-full w-full">
        
        <!-- Left Side: Branding / Visual (Hidden on Mobile) -->
        <div class="hidden lg:flex w-1/2 bg-gradient-to-br from-emerald-600 to-teal-800 text-white flex-col justify-between p-12 relative overflow-hidden">
            <!-- Decorative Circle -->
            <div class="absolute top-0 right-0 -mr-20 -mt-20 w-80 h-80 rounded-full bg-white opacity-10 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-80 h-80 rounded-full bg-emerald-400 opacity-10 blur-3xl"></div>

            <!-- Logo and Language Switch -->
            <div class="flex items-center justify-between w-full z-10">
                <div class="flex items-center gap-2">
                    <img src="{{ asset('logo.png') }}" alt="Motrac" class="h-8 w-auto">
                    <span class="text-xl font-extrabold tracking-tight text-white" style="font-family: 'Inter', sans-serif; font-weight: 800;">Motrac</span>
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
                <h2 class="text-4xl font-bold mb-6 leading-tight">{{ __('Welcome Back!') }}</h2>
                <p class="text-emerald-100 text-lg leading-relaxed">
                    "{{ __('Don\'t save what is left after spending, but spend what is left after saving.') }}"
                </p>
            </div>

            <!-- Copyright -->
            <div class="text-xs text-emerald-200/60 z-10">
                &copy; 2024 Motrac Financial Technologies.
            </div>
        </div>

        <!-- Right Side: Form -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8 bg-white">
            <div class="w-full max-w-md space-y-8">
                
                <!-- Mobile Logo (Only visible on mobile) -->
                <div class="lg:hidden flex items-center justify-center gap-2 mb-6">
                    <img src="{{ asset('logo.png') }}" alt="Motrac" class="h-10 w-auto">
                    <span class="text-2xl font-extrabold tracking-tight text-dark" style="font-family: 'Inter', sans-serif; font-weight: 800;">Motrac</span>
                </div>

                <div class="text-center lg:text-left">
                    <h2 class="text-3xl font-bold text-dark">{{ __('Login to Account') }}</h2>
                    <p class="text-gray-500 mt-2">{{ __('Enter your account details to continue.') }}</p>
                </div>

                @if (session('success'))
                    <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm">
                        <i class="fa-solid fa-circle-check mr-2"></i>
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf
                    
                    <!-- Email Input -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Email Address') }}</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <i class="fa-regular fa-envelope"></i>
                            </div>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" required placeholder="nama@email.com" class="pl-10 w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition text-sm">
                        </div>
                    </div>

                    <!-- Password Input -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                            <a href="{{ route('password.request') }}" class="text-xs font-medium text-primary hover:text-emerald-700">{{ __('Forgot Password?') }}</a>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <i class="fa-solid fa-lock"></i>
                            </div>
                            <input type="password" id="password" name="password" required placeholder="••••••••" class="pl-10 pr-10 w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition text-sm">
                            <button type="button" onclick="togglePassword('password', this)" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center">
                        <input type="checkbox" id="remember" name="remember" class="w-4 h-4 text-primary bg-gray-50 border-gray-200 rounded focus:ring-primary focus:ring-2">
                        <label for="remember" class="ml-2 text-sm text-gray-600">{{ __('Remember me') }}</label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="w-full bg-primary hover:bg-emerald-600 text-white font-bold py-3.5 rounded-xl transition shadow-lg shadow-emerald-200 transform active:scale-95">
                        {{ __('Login Now') }}
                    </button>
                </form>

                <!-- Footer -->
                <p class="text-center text-sm text-gray-600">
                    {{ __('Don\'t have an account?') }} 
                    <a href="{{ route('register') }}" class="font-bold text-primary hover:text-emerald-700 transition">{{ __('Register Free') }}</a>
                </p>
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
