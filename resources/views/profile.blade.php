<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pengaturan Akun - Motrac</title>
    
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
        /* Hide scrollbar for clean look */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        
        /* Toggle Switch Checkbox Style */
        .toggle-checkbox:checked {
            right: 0;
            border-color: #10B981;
        }
        .toggle-checkbox:checked + .toggle-label {
            background-color: #10B981;
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
            <span class="text-lg font-bold text-dark">Motrac</span>
        </a>

        <!-- Menu -->
        <div class="flex-1 overflow-y-auto py-6 px-4 space-y-1">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-dark dark:hover:text-white rounded-lg font-medium transition group">
                <i class="fa-solid fa-arrow-left w-5 text-center group-hover:text-primary"></i> Kembali ke Dashboard
            </a>
            
            <div class="my-4 border-t border-gray-100 dark:border-gray-700"></div>

            <p class="px-2 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Pengaturan</p>
            
            <button onclick="switchSettings('profile', this)" class="nav-item w-full flex items-center gap-3 px-3 py-2.5 bg-emerald-50 text-emerald-700 rounded-lg font-medium transition text-left">
                <i class="fa-regular fa-user w-5 text-center"></i> Profil Saya
            </button>
            <button onclick="switchSettings('security', this)" class="nav-item w-full flex items-center gap-3 px-3 py-2.5 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-dark dark:hover:text-white rounded-lg font-medium transition group text-left">
                <i class="fa-solid fa-shield-halved w-5 text-center group-hover:text-primary"></i> Keamanan
            </button>
            <button onclick="switchSettings('notifications', this)" class="nav-item w-full flex items-center gap-3 px-3 py-2.5 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-dark dark:hover:text-white rounded-lg font-medium transition group text-left">
                <i class="fa-regular fa-bell w-5 text-center group-hover:text-primary"></i> Notifikasi
            </button>
        </div>
    </aside>

    <!-- ================= Main Content ================= -->
    <main class="flex-1 flex flex-col h-screen overflow-hidden relative">
        
        <!-- Header -->
        <header class="bg-white dark:bg-gray-800 h-16 border-b border-gray-200 dark:border-gray-700 flex-shrink-0 px-4 sm:px-8 flex items-center justify-between z-20">
            <div class="flex items-center gap-4">
                <button class="md:hidden text-gray-500 dark:text-gray-300 hover:text-dark dark:hover:text-white"><i class="fa-solid fa-bars text-xl"></i></button>
                <h2 class="text-lg font-bold text-dark dark:text-white" id="page-title">Profil Saya</h2>
            </div>
            
            <!-- User Menu -->
            <div class="flex items-center gap-3">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=10B981&color=fff" class="w-9 h-9 rounded-full border border-gray-200 dark:border-gray-600">
            </div>
        </header>

        <!-- Content Area -->
        <div class="flex-1 overflow-y-auto p-4 sm:p-8 bg-gray-50 dark:bg-gray-900">
            <div class="max-w-5xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-8">

                <!-- Left Column: Profile Card (Static) -->
                <div class="space-y-6">
                    <!-- Profile Summary -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 flex flex-col items-center text-center shadow-sm relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-full h-24 bg-gradient-to-r from-emerald-500 to-teal-600"></div>
                        
                        <div class="relative z-10 mt-8 mb-4">
                            <img id="profile-photo" src="{{ $user->photo ? \Illuminate\Support\Facades\Storage::url($user->photo) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=10B981&color=fff&size=128' }}" class="w-24 h-24 rounded-full border-4 border-white shadow-md object-cover">
                            <label for="photo-input" class="absolute bottom-0 right-0 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 p-1.5 rounded-full text-gray-500 dark:text-gray-400 hover:text-primary shadow-sm transition cursor-pointer">
                                <i class="fa-solid fa-camera text-xs"></i>
                            </label>
                            <input type="file" id="photo-input" accept="image/*" class="hidden" onchange="handlePhotoUpload(event)">
                        </div>
                        
                        <h3 class="text-xl font-bold text-dark dark:text-white">{{ $user->name }}</h3>
                        <p class="text-gray-500 dark:text-gray-400 text-sm mb-4">{{ $user->email }}</p>
                        
                        <!-- Badge Free Plan removed here -->

                        <div class="grid grid-cols-2 w-full gap-4 mt-8 pt-6 border-t border-gray-100 dark:border-gray-700">
                            <div>
                                <p class="text-2xl font-bold text-dark dark:text-white">{{ $transactionCount }}</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 uppercase font-medium">Transaksi</p>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-dark dark:text-white">{{ $accountCount }}</p>
                                <p class="text-xs text-gray-400 uppercase font-medium">Dompet</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Dynamic Content -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- VIEW 1: PROFILE (Default) -->
                    <div id="view-profile" class="content-section">
                        <!-- Personal Info -->
                        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm mb-6">
                            <div class="flex justify-between items-center mb-6">
                                <h3 class="font-bold text-lg text-dark dark:text-white">Informasi Pribadi</h3>
                            </div>
                            
                            <form id="profileForm" class="space-y-4">
                                @csrf
                                @php
                                    $nameParts = explode(' ', $user->name, 2);
                                    $firstName = $nameParts[0] ?? '';
                                    $lastName = $nameParts[1] ?? '';
                                @endphp
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Nama Depan</label>
                                        <input type="text" name="first_name" id="first_name" value="{{ $firstName }}" required class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-dark dark:text-white rounded-lg text-sm focus:outline-none focus:border-primary">
                                        <span class="error-message text-red-500 text-xs mt-1 hidden" id="error_first_name"></span>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Nama Belakang</label>
                                        <input type="text" name="last_name" id="last_name" value="{{ $lastName }}" class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-dark dark:text-white rounded-lg text-sm focus:outline-none focus:border-primary">
                                        <span class="error-message text-red-500 text-xs mt-1 hidden" id="error_last_name"></span>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Email</label>
                                    <div class="relative">
                                        <i class="fa-regular fa-envelope absolute left-3 top-2.5 text-gray-400 text-xs"></i>
                                        <input type="email" name="email" id="email" value="{{ $user->email }}" required class="pl-9 w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-dark dark:text-white rounded-lg text-sm focus:outline-none focus:border-primary">
                                    </div>
                                    <span class="error-message text-red-500 text-xs mt-1 hidden" id="error_email"></span>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Bio Singkat</label>
                                    <textarea name="bio" id="bio" rows="3" class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-dark dark:text-white rounded-lg text-sm focus:outline-none focus:border-primary" placeholder="Tulis bio singkat tentang Anda...">{{ $user->bio ?? '' }}</textarea>
                                    <span class="error-message text-red-500 text-xs mt-1 hidden" id="error_bio"></span>
                                </div>
                                <div class="flex justify-end pt-2">
                                    <button type="submit" class="bg-primary hover:bg-emerald-600 text-white px-6 py-2 rounded-lg text-sm font-medium transition">Simpan Perubahan</button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Preferences -->
                        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm mb-6">
                            <h3 class="font-bold text-lg text-dark dark:text-white mb-6">Preferensi Aplikasi</h3>
                            <form id="preferencesForm" class="space-y-5">
                                @csrf
                                <div class="flex items-center justify-between">
                                    <div><p class="font-medium text-sm text-dark dark:text-white">Mata Uang Utama</p><p class="text-xs text-gray-400 dark:text-gray-500">Mata uang default laporan.</p></div>
                                    <select name="currency" id="currency" class="bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-dark dark:text-white text-sm rounded-lg px-3 py-1.5 focus:outline-none">
                                        <option value="IDR" {{ ($user->currency ?? 'IDR') === 'IDR' ? 'selected' : '' }}>IDR (Rupiah)</option>
                                        <option value="USD" {{ ($user->currency ?? 'IDR') === 'USD' ? 'selected' : '' }}>USD (Dollar)</option>
                                    </select>
                                </div>
                                <hr class="border-gray-100 dark:border-gray-700">
                                <div class="flex items-center justify-between">
                                    <div><p class="font-medium text-sm text-dark dark:text-white">Mode Gelap</p><p class="text-xs text-gray-400 dark:text-gray-500">Ganti tampilan tema.</p></div>
                                    <div class="relative inline-block w-10 mr-2 align-middle select-none transition duration-200 ease-in">
                                        <input type="checkbox" name="dark_mode" id="toggle-dark" {{ ($user->dark_mode ?? false) ? 'checked' : '' }} class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer border-gray-300" onchange="updatePreferences()"/>
                                        <label for="toggle-dark" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Danger Zone -->
                        <div class="bg-red-50 dark:bg-red-900/20 rounded-2xl border border-red-100 dark:border-red-800 p-6">
                            <h3 class="font-bold text-lg text-red-700 dark:text-red-400 mb-2">Hapus Akun</h3>
                            <p class="text-sm text-red-600 mb-6">Tindakan ini permanen. Semua data akan hilang.</p>
                            <button onclick="openDeleteAccountModal()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">Hapus Akun Saya</button>
                        </div>
                    </div>

                    <!-- VIEW 2: SECURITY (Keamanan) -->
                    <div id="view-security" class="content-section hidden">
                        <!-- Change Password -->
                        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm mb-6">
                            <h3 class="font-bold text-lg text-dark dark:text-white mb-6">Ganti Password</h3>
                            <form id="passwordForm" class="space-y-4">
                                @csrf
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Password Saat Ini</label>
                                    <input type="password" name="current_password" id="current_password" required class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-dark dark:text-white rounded-lg text-sm focus:outline-none focus:border-primary">
                                    <span class="error-message text-red-500 text-xs mt-1 hidden" id="error_current_password"></span>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Password Baru</label>
                                        <input type="password" name="password" id="password" required class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-dark dark:text-white rounded-lg text-sm focus:outline-none focus:border-primary">
                                        <span class="error-message text-red-500 text-xs mt-1 hidden" id="error_password"></span>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Konfirmasi Password Baru</label>
                                        <input type="password" name="password_confirmation" id="password_confirmation" required class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-dark dark:text-white rounded-lg text-sm focus:outline-none focus:border-primary">
                                        <span class="error-message text-red-500 text-xs mt-1 hidden" id="error_password_confirmation"></span>
                                    </div>
                                </div>
                                <div class="flex justify-end pt-2">
                                    <button type="submit" class="bg-primary hover:bg-emerald-600 text-white px-6 py-2 rounded-lg text-sm font-medium transition">Update Password</button>
                                </div>
                            </form>
                        </div>

                        <!-- 2FA -->
                        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm mb-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-bold text-lg text-dark dark:text-white">Autentikasi Dua Faktor (2FA)</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Tambahkan lapisan keamanan ekstra dengan kode OTP.</p>
                                </div>
                                <div class="relative inline-block w-10 mr-2 align-middle select-none transition duration-200 ease-in">
                                    <input type="checkbox" name="toggle" id="toggle-2fa" class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer border-gray-300"/>
                                    <label for="toggle-2fa" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
                                </div>
                            </div>
                        </div>

                        <!-- Login Activity -->
                        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                            <h3 class="font-bold text-lg text-dark dark:text-white mb-4">Riwayat Login</h3>
                            <div class="space-y-4">
                                <!-- Item 1 -->
                                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-white dark:bg-gray-800 rounded-full flex items-center justify-center text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-600">
                                            <i class="fa-solid fa-laptop"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-dark dark:text-white">Perangkat Ini</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Aktif Sekarang</p>
                                        </div>
                                    </div>
                                    <span class="text-xs bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 px-2 py-1 rounded font-bold">Online</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- VIEW 3: NOTIFICATIONS (Notifikasi) -->
                    <div id="view-notifications" class="content-section hidden">
                        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                            <div class="border-b border-gray-100 dark:border-gray-700 pb-4 mb-4">
                                <h3 class="font-bold text-lg text-dark dark:text-white">Pengaturan Notifikasi</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Pilih bagaimana kami menghubungi Anda.</p>
                            </div>

                            <!-- Section: Account Activity -->
                            <div class="mb-6">
                                <h4 class="text-sm font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-4">Aktivitas Akun</h4>
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-medium text-dark dark:text-white">Peringatan Keamanan</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Notifikasi login baru atau perubahan password.</p>
                                        </div>
                                        <div class="relative inline-block w-10 mr-2 align-middle select-none transition duration-200 ease-in">
                                            <input type="checkbox" id="notify_security" name="notify_security" value="1" {{ ($user->notify_security ?? true) ? 'checked' : '' }} class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white dark:bg-gray-800 border-4 appearance-none cursor-pointer border-gray-300 dark:border-gray-600"/>
                                            <label for="notify_security" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 dark:bg-gray-600 cursor-pointer"></label>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-medium text-dark dark:text-white">Peringatan Budget</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Jika pengeluaran melebihi 80% dari budget.</p>
                                        </div>
                                        <div class="relative inline-block w-10 mr-2 align-middle select-none transition duration-200 ease-in">
                                            <input type="checkbox" id="notify_budget" name="notify_budget" value="1" {{ ($user->notify_budget ?? true) ? 'checked' : '' }} class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white dark:bg-gray-800 border-4 appearance-none cursor-pointer border-gray-300 dark:border-gray-600"/>
                                            <label for="notify_budget" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 dark:bg-gray-600 cursor-pointer"></label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section: Marketing -->
                            <div>
                                <h4 class="text-sm font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-4">Berita & Update</h4>
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-medium text-dark dark:text-white">Laporan Mingguan</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Ringkasan pengeluaran dikirim ke email.</p>
                                        </div>
                                        <div class="relative inline-block w-10 mr-2 align-middle select-none transition duration-200 ease-in">
                                            <input type="checkbox" id="notify_weekly_report" name="notify_weekly_report" value="1" {{ ($user->notify_weekly_report ?? false) ? 'checked' : '' }} class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white dark:bg-gray-800 border-4 appearance-none cursor-pointer border-gray-300 dark:border-gray-600"/>
                                            <label for="notify_weekly_report" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 dark:bg-gray-600 cursor-pointer"></label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end pt-6 mt-6 border-t border-gray-100 dark:border-gray-700">
                                <button type="button" onclick="updateNotificationPreferences()" class="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 px-6 py-2 rounded-lg text-sm font-medium transition">Simpan</button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>

    <!-- Toast Notification -->
    <div id="toast" class="fixed bottom-5 right-5 bg-dark text-white px-6 py-3 rounded-xl shadow-2xl transform translate-y-24 opacity-0 transition-all duration-300 flex items-center gap-3 z-50">
        <i class="fa-solid fa-circle-check text-emerald-400"></i>
        <span id="toast-msg">Perubahan berhasil disimpan!</span>
    </div>

    <!-- Delete Account Modal -->
    <div id="deleteAccountModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md">
            <div class="p-6">
                <h3 class="text-xl font-bold text-red-700 mb-2">Hapus Akun</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">Tindakan ini tidak dapat dibatalkan. Semua data Anda akan dihapus secara permanen.</p>
                
                <form id="deleteAccountForm" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Masukkan password untuk konfirmasi</label>
                        <input type="password" name="password" id="delete_password" required class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-dark dark:text-white rounded-lg text-sm focus:outline-none focus:border-red-500">
                        <span class="error-message text-red-500 text-xs mt-1 hidden" id="error_delete_password"></span>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Ketik "HAPUS" untuk konfirmasi</label>
                        <input type="text" name="confirm_text" id="confirm_text" required class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-dark dark:text-white rounded-lg text-sm focus:outline-none focus:border-red-500" placeholder="HAPUS">
                        <span class="error-message text-red-500 text-xs mt-1 hidden" id="error_confirm_text"></span>
                    </div>
                    
                    <div class="flex gap-3 pt-4">
                        <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white px-4 py-2.5 rounded-lg text-sm font-medium transition">
                            Hapus Akun
                        </button>
                        <button type="button" onclick="closeDeleteAccountModal()" class="px-4 py-2.5 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript Logic -->
    <script>
        // Switch Settings View
        function switchSettings(viewId, btnElement) {
            // 1. Hide all content sections
            document.querySelectorAll('.content-section').forEach(el => {
                el.classList.add('hidden');
            });

            // 2. Show target section
            document.getElementById('view-' + viewId).classList.remove('hidden');

            // 3. Update Sidebar Styles (Active State)
            document.querySelectorAll('.nav-item').forEach(el => {
                // Reset styling to inactive
                el.className = 'nav-item w-full flex items-center gap-3 px-3 py-2.5 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-dark dark:hover:text-white rounded-lg font-medium transition group text-left';
            });

            // 4. Set Active styling on clicked button
            if (btnElement) {
                btnElement.className = 'nav-item w-full flex items-center gap-3 px-3 py-2.5 bg-emerald-50 text-emerald-700 rounded-lg font-medium transition text-left';
            }

            // 5. Update Header Title based on view
            const titles = {
                'profile': 'Profil Saya',
                'security': 'Keamanan & Login',
                'notifications': 'Pengaturan Notifikasi'
            };
            document.getElementById('page-title').innerText = titles[viewId] || 'Pengaturan';
        }

        // Dark mode settings
        const isDarkMode = {{ $user->dark_mode ? 'true' : 'false' }};
        
        // Apply dark mode on page load
        if (isDarkMode) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }

        // Show Toast Notification
        function showToast(message) {
            const toast = document.getElementById('toast');
            const msgElement = document.getElementById('toast-msg');
            
            if(message) msgElement.innerText = message;

            toast.classList.remove('translate-y-24', 'opacity-0');
            setTimeout(() => {
                toast.classList.add('translate-y-24', 'opacity-0');
            }, 3000);
        }

        // Handle Photo Upload
        async function handlePhotoUpload(event) {
            const file = event.target.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('photo', file);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

            const photoImg = document.getElementById('profile-photo');
            const originalSrc = photoImg.src;

            // Show loading
            photoImg.style.opacity = '0.5';

            try {
                const response = await fetch('{{ route("profile.photo") }}', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    photoImg.src = data.photo_url + '?t=' + new Date().getTime();
                    showToast(data.message);
                } else {
                    showToast(data.message || 'Gagal mengupload foto');
                    photoImg.src = originalSrc;
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Terjadi kesalahan saat mengupload foto');
                photoImg.src = originalSrc;
            } finally {
                photoImg.style.opacity = '1';
                event.target.value = '';
            }
        }

        // Update Profile Form
        document.getElementById('profileForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            clearErrors();

            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';

            try {
                const response = await fetch('{{ route("profile.update") }}', {
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
                    showToast(data.message);
                    // Update name in header if needed
                    if (data.user) {
                        const nameParts = data.user.name.split(' ', 2);
                        document.getElementById('first_name').value = nameParts[0] || '';
                        document.getElementById('last_name').value = nameParts[1] || '';
                        document.getElementById('email').value = data.user.email;
                        if (data.user.bio) {
                            document.getElementById('bio').value = data.user.bio;
                        }
                    }
                } else if (data.errors) {
                    Object.keys(data.errors).forEach(field => {
                        const errorMessage = Array.isArray(data.errors[field]) ? data.errors[field][0] : data.errors[field];
                        showError(field, errorMessage);
                    });
                } else {
                    showToast(data.message || 'Gagal memperbarui profil');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Terjadi kesalahan. Silakan coba lagi.');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });

        // Update Preferences
        async function updatePreferences() {
            const form = document.getElementById('preferencesForm');
            const formData = new FormData(form);
            formData.append('dark_mode', document.getElementById('toggle-dark').checked ? '1' : '0');

            try {
                const response = await fetch('{{ route("profile.preferences") }}', {
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
                    showToast(data.message);
                    // Apply dark mode immediately
                    if (document.getElementById('toggle-dark').checked) {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                    }
                    // Reload page after a short delay to apply currency changes
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showToast(data.message || 'Gagal memperbarui preferensi');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Terjadi kesalahan. Silakan coba lagi.');
            }
        }

        // Currency change handler
        document.getElementById('currency').addEventListener('change', updatePreferences);

        // Update Notification Preferences
        async function updateNotificationPreferences() {
            const formData = new FormData();
            formData.append('notify_security', document.getElementById('notify_security').checked ? '1' : '0');
            formData.append('notify_budget', document.getElementById('notify_budget').checked ? '1' : '0');
            formData.append('notify_weekly_report', document.getElementById('notify_weekly_report').checked ? '1' : '0');

            try {
                const response = await fetch('{{ route("profile.notifications") }}', {
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
                    showToast(data.message || 'Preferensi notifikasi berhasil diperbarui!');
                } else {
                    showToast(data.message || 'Gagal memperbarui preferensi notifikasi');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Terjadi kesalahan. Silakan coba lagi.');
            }
        }

        // Change Password Form
        document.getElementById('passwordForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            clearErrors();

            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mengupdate...';

            try {
                const response = await fetch('{{ route("profile.password") }}', {
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
                    showToast(data.message);
                    this.reset();
                } else if (data.errors) {
                    Object.keys(data.errors).forEach(field => {
                        const errorMessage = Array.isArray(data.errors[field]) ? data.errors[field][0] : data.errors[field];
                        showError(field, errorMessage);
                    });
                } else {
                    showToast(data.message || 'Gagal mengubah password');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Terjadi kesalahan. Silakan coba lagi.');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });

        // Delete Account Modal
        function openDeleteAccountModal() {
            document.getElementById('deleteAccountModal').classList.remove('hidden');
        }

        function closeDeleteAccountModal() {
            document.getElementById('deleteAccountModal').classList.add('hidden');
            document.getElementById('deleteAccountForm').reset();
            clearErrors();
        }

        document.getElementById('deleteAccountForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            clearErrors();

            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menghapus...';

            try {
                const response = await fetch('{{ route("profile.delete") }}', {
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
                    showToast(data.message);
                    setTimeout(() => {
                        window.location.href = data.redirect || '{{ route("landing") }}';
                    }, 1500);
                } else if (data.errors) {
                    Object.keys(data.errors).forEach(field => {
                        const errorMessage = Array.isArray(data.errors[field]) ? data.errors[field][0] : data.errors[field];
                        showError('delete_' + field, errorMessage);
                    });
                } else {
                    showToast(data.message || 'Gagal menghapus akun');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Terjadi kesalahan. Silakan coba lagi.');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });

        // Error handling functions
        function clearErrors() {
            document.querySelectorAll('.error-message').forEach(el => {
                el.classList.add('hidden');
                el.textContent = '';
            });
        }

        function showError(field, message) {
            const errorEl = document.getElementById('error_' + field);
            if (errorEl) {
                errorEl.textContent = message;
                errorEl.classList.remove('hidden');
            }
        }
    </script>
</body>
</html>

