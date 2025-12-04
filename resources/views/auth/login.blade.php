<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk - Motrac Money Tracker</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

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

            <!-- Logo -->
            <div class="flex items-center gap-2 z-10">
                <div class="w-8 h-8 bg-white/20 backdrop-blur rounded-lg flex items-center justify-center font-bold">
                    <i class="fa-solid fa-wallet"></i>
                </div>
                <span class="text-xl font-bold tracking-tight">Motrac</span>
            </div>

            <!-- Quote/Text -->
            <div class="z-10 max-w-md">
                <h2 class="text-4xl font-bold mb-6 leading-tight">Selamat Datang Kembali!</h2>
                <p class="text-emerald-100 text-lg leading-relaxed">
                    "Jangan menabung apa yang tersisa setelah belanja, tapi belanjalah apa yang tersisa setelah menabung."
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
                <div class="lg:hidden flex justify-center mb-6">
                    <div class="w-10 h-10 bg-primary rounded-lg flex items-center justify-center text-white font-bold text-xl">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                </div>

                <div class="text-center lg:text-left">
                    <h2 class="text-3xl font-bold text-dark">Masuk ke Akun</h2>
                    <p class="text-gray-500 mt-2">Masukkan detail akun Anda untuk melanjutkan.</p>
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
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
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
                            <a href="#" class="text-xs font-medium text-primary hover:text-emerald-700">Lupa Password?</a>
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
                        <label for="remember" class="ml-2 text-sm text-gray-600">Ingat saya</label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="w-full bg-primary hover:bg-emerald-600 text-white font-bold py-3.5 rounded-xl transition shadow-lg shadow-emerald-200 transform active:scale-95">
                        Masuk Sekarang
                    </button>
                </form>

                <!-- Footer -->
                <p class="text-center text-sm text-gray-600">
                    Belum punya akun? 
                    <a href="{{ route('register') }}" class="font-bold text-primary hover:text-emerald-700 transition">Daftar Gratis</a>
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
    </script>
</body>
</html>
