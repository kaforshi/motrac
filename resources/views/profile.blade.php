<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ auth()->user() && auth()->user()->dark_mode ? 'dark' : '' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <title>{{ __('Account Settings') }} - Motrac</title>
    
    <!-- Vite CSS & JS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="font-sans text-slate-800 dark:text-gray-100 bg-gray-50 dark:bg-gray-900 flex h-screen overflow-hidden">

    <!-- Mobile Sidebar Overlay -->
    <div id="mobileSidebarOverlay" class="hidden fixed inset-0 bg-black bg-opacity-50 z-40 md:hidden" onclick="toggleMobileSidebar()"></div>
    
    <!-- ================= Sidebar ================= -->
    <aside id="sidebar" class="fixed md:relative w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 flex flex-col z-50 md:z-10 transition-all duration-300 -translate-x-full md:translate-x-0 h-screen flex-shrink-0">
        <!-- Logo -->
        <a href="{{ route('dashboard') }}" class="h-16 flex items-center gap-2 px-6 border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
            <img src="{{ asset('logo.png') }}" alt="Motrac" class="h-8 w-auto">
            <span class="text-lg font-extrabold tracking-tight text-dark dark:text-white" style="font-family: 'Inter', sans-serif; font-weight: 800;">Motrac</span>
        </a>

        <!-- Menu -->
        <div class="flex-1 overflow-y-auto py-6 px-4 space-y-1">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-dark dark:hover:text-white rounded-lg font-medium transition group">
                <i class="fa-solid fa-arrow-left w-5 text-center group-hover:text-primary"></i> {{ __('Back to Dashboard') }}
            </a>
            
            <div class="my-4 border-t border-gray-100 dark:border-gray-700"></div>

            <p class="px-2 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-2">{{ __('Settings') }}</p>
            
            <a id="nav-profile" href="{{ route('profile') }}" class="nav-item w-full flex items-center gap-3 px-3 py-2.5 {{ request()->routeIs('profile') ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-dark dark:hover:text-white' }} rounded-lg font-medium transition text-left">
                <i class="fa-regular fa-user w-5 text-center"></i> {{ __('My Profile') }}
            </a>
            <a id="nav-security" href="{{ route('profile.security') }}" class="nav-item w-full flex items-center gap-3 px-3 py-2.5 {{ request()->routeIs('profile.security') ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-dark dark:hover:text-white' }} rounded-lg font-medium transition group text-left">
                <i class="fa-solid fa-shield-halved w-5 text-center group-hover:text-primary"></i> {{ __('Security') }}
            </a>
            <a id="nav-notifications" href="{{ route('profile.notifications') }}" class="nav-item w-full flex items-center gap-3 px-3 py-2.5 {{ request()->routeIs('profile.notifications') ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-dark dark:hover:text-white' }} rounded-lg font-medium transition group text-left">
                <i class="fa-regular fa-bell w-5 text-center group-hover:text-primary"></i> {{ __('Notifications') }}
            </a>
        </div>
    </aside>

    <!-- ================= Main Content ================= -->
    <main class="flex-1 flex flex-col h-screen overflow-hidden relative">
        
        <!-- Header -->
        <header class="bg-white dark:bg-gray-800 h-16 border-b border-gray-200 dark:border-gray-700 flex-shrink-0 px-4 sm:px-8 flex items-center justify-between z-20">
            <div class="flex items-center gap-4">
                <button onclick="toggleMobileSidebar()" class="md:hidden bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-300 hover:text-dark dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg p-2 transition"><i class="fa-solid fa-bars text-xl"></i></button>
                <h2 class="text-lg font-bold text-dark dark:text-white" id="page-title">{{ __('My Profile') }}</h2>
            </div>
            
            <!-- User Menu -->
            <div class="flex items-center gap-3">
                <!-- Language Toggle Switch -->
                <div class="flex items-center bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-full p-1">
                    <button onclick="switchLanguage('id')" class="px-3 py-1.5 rounded-full text-sm font-semibold transition-all {{ app()->getLocale() === 'id' ? 'bg-dark dark:bg-gray-700 text-white' : 'bg-transparent hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400' }}">
                        ID
                    </button>
                    <button onclick="switchLanguage('en')" class="px-3 py-1.5 rounded-full text-sm font-semibold transition-all {{ app()->getLocale() === 'en' ? 'bg-dark dark:bg-gray-700 text-white' : 'bg-transparent hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400' }}">
                        EN
                    </button>
                </div>
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
                                <p class="text-xs text-gray-400 dark:text-gray-500 uppercase font-medium">{{ __('Transactions') }}</p>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-dark dark:text-white">{{ $accountCount }}</p>
                                <p class="text-xs text-gray-400 uppercase font-medium">{{ __('Wallets') }}</p>
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
                                <h3 class="font-bold text-lg text-dark dark:text-white">{{ __('Personal Information') }}</h3>
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
                                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('First Name') }}</label>
                                        <input type="text" name="first_name" id="first_name" value="{{ $firstName }}" required class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-dark dark:text-white rounded-lg text-sm focus:outline-none focus:border-primary">
                                        <span class="error-message text-red-500 text-xs mt-1 hidden" id="error_first_name"></span>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('Last Name') }}</label>
                                        <input type="text" name="last_name" id="last_name" value="{{ $lastName }}" class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-dark dark:text-white rounded-lg text-sm focus:outline-none focus:border-primary">
                                        <span class="error-message text-red-500 text-xs mt-1 hidden" id="error_last_name"></span>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('Email') }}</label>
                                    <div class="relative">
                                        <i class="fa-regular fa-envelope absolute left-3 top-2.5 text-gray-400 text-xs"></i>
                                        <input type="email" name="email" id="email" value="{{ $user->email }}" required class="pl-9 w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-dark dark:text-white rounded-lg text-sm focus:outline-none focus:border-primary">
                                    </div>
                                    <span class="error-message text-red-500 text-xs mt-1 hidden" id="error_email"></span>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('Short Bio') }}</label>
                                    <textarea name="bio" id="bio" rows="3" class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-dark dark:text-white rounded-lg text-sm focus:outline-none focus:border-primary" placeholder="{{ __('Write a short bio about yourself...') }}">{{ $user->bio ?? '' }}</textarea>
                                    <span class="error-message text-red-500 text-xs mt-1 hidden" id="error_bio"></span>
                                </div>
                                <div class="flex justify-end pt-2">
                                    <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg text-sm font-medium transition">{{ __('Save Changes') }}</button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Preferences -->
                        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm mb-6">
                            <h3 class="font-bold text-lg text-dark dark:text-white mb-6">{{ __('Application Preferences') }}</h3>
                            <form id="preferencesForm" class="space-y-5">
                                @csrf
                                <div class="flex items-center justify-between">
                                    <div><p class="font-medium text-sm text-dark dark:text-white">{{ __('Primary Currency') }}</p><p class="text-xs text-gray-400 dark:text-gray-500">{{ __('Default currency for reports.') }}</p></div>
                                    <select name="currency" id="currency" class="bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-dark dark:text-white text-sm rounded-lg px-3 py-1.5 focus:outline-none">
                                        <option value="IDR" {{ ($user->currency ?? 'IDR') === 'IDR' ? 'selected' : '' }}>IDR (Rupiah)</option>
                                        <option value="USD" {{ ($user->currency ?? 'IDR') === 'USD' ? 'selected' : '' }}>USD (Dollar)</option>
                                    </select>
                                </div>
                                <hr class="border-gray-100 dark:border-gray-700">
                                <div class="flex items-center justify-between">
                                    <div><p class="font-medium text-sm text-dark dark:text-white">{{ __('Dark Mode') }}</p><p class="text-xs text-gray-400 dark:text-gray-500">{{ __('Change theme appearance.') }}</p></div>
                                    <div class="relative inline-block w-10 mr-2 align-middle select-none transition duration-200 ease-in">
                                        <input type="checkbox" name="dark_mode" id="toggle-dark" {{ ($user->dark_mode ?? false) ? 'checked' : '' }} class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer border-gray-300" onchange="updatePreferences()"/>
                                        <label for="toggle-dark" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Danger Zone -->
                        <div class="bg-red-50 dark:bg-red-900/20 rounded-2xl border border-red-100 dark:border-red-800 p-6">
                            <h3 class="font-bold text-lg text-red-700 dark:text-red-400 mb-2">{{ __('Delete Account') }}</h3>
                            <p class="text-sm text-red-600 mb-6">{{ __('This action is permanent. All data will be lost.') }}</p>
                            <button onclick="openDeleteAccountModal()" class="bg-primary text-white px-4 py-2 rounded-lg text-sm font-medium transition">{{ __('Delete My Account') }}</button>
                        </div>
                    </div>

                    <!-- VIEW 2: SECURITY (Keamanan) -->
                    <div id="view-security" class="content-section hidden">
                        <!-- Change Password -->
                        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm mb-6">
                            <h3 class="font-bold text-lg text-dark dark:text-white mb-6">{{ __('Change Password') }}</h3>
                            <form id="passwordForm" class="space-y-4">
                                @csrf
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('Current Password') }}</label>
                                    <input type="password" name="current_password" id="current_password" required class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-dark dark:text-white rounded-lg text-sm focus:outline-none focus:border-primary">
                                    <span class="error-message text-red-500 text-xs mt-1 hidden" id="error_current_password"></span>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('New Password') }}</label>
                                        <input type="password" name="password" id="password" required class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-dark dark:text-white rounded-lg text-sm focus:outline-none focus:border-primary">
                                        <span class="error-message text-red-500 text-xs mt-1 hidden" id="error_password"></span>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('Confirm New Password') }}</label>
                                        <input type="password" name="password_confirmation" id="password_confirmation" required class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-dark dark:text-white rounded-lg text-sm focus:outline-none focus:border-primary">
                                        <span class="error-message text-red-500 text-xs mt-1 hidden" id="error_password_confirmation"></span>
                                    </div>
                                </div>
                                <div class="flex justify-end pt-2">
                                    <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg text-sm font-medium transition">{{ __('Update Password') }}</button>
                                </div>
                            </form>
                        </div>

                        <!-- 2FA -->
                        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm mb-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-bold text-lg text-dark dark:text-white">{{ __('Two-Factor Authentication (2FA)') }}</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('Add an extra layer of security with OTP code.') }}</p>
                                </div>
                                <div class="relative inline-block w-10 mr-2 align-middle select-none transition duration-200 ease-in">
                                    <input type="checkbox" name="toggle" id="toggle-2fa" class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer border-gray-300"/>
                                    <label for="toggle-2fa" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
                                </div>
                            </div>
                        </div>

                        <!-- Login Activity -->
                        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                            <h3 class="font-bold text-lg text-dark dark:text-white mb-4">{{ __('Login History') }}</h3>
                            <div class="space-y-4">
                                <!-- Item 1 -->
                                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-white dark:bg-gray-800 rounded-full flex items-center justify-center text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-600">
                                            <i class="fa-solid fa-laptop"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-dark dark:text-white">{{ __('This Device') }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Active Now') }}</p>
                                        </div>
                                    </div>
                                    <span class="text-xs bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 px-2 py-1 rounded font-bold">{{ __('Online') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- VIEW 3: NOTIFICATIONS (Notifikasi) -->
                    <div id="view-notifications" class="content-section hidden">
                        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                            <div class="border-b border-gray-100 dark:border-gray-700 pb-4 mb-4">
                                <h3 class="font-bold text-lg text-dark dark:text-white">{{ __('Notification Settings') }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Choose how we contact you.') }}</p>
                            </div>

                            <!-- Section: Account Activity -->
                            <div class="mb-6">
                                <h4 class="text-sm font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-4">{{ __('Account Activity') }}</h4>
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-medium text-dark dark:text-white">{{ __('Security Alert') }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('New login or password change notifications.') }}</p>
                                        </div>
                                        <div class="relative inline-block w-10 mr-2 align-middle select-none transition duration-200 ease-in">
                                            <input type="checkbox" id="notify_security" name="notify_security" value="1" {{ ($user->notify_security ?? true) ? 'checked' : '' }} class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white dark:bg-gray-800 border-4 appearance-none cursor-pointer border-gray-300 dark:border-gray-600"/>
                                            <label for="notify_security" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 dark:bg-gray-600 cursor-pointer"></label>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-medium text-dark dark:text-white">{{ __('Budget Alert') }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('If expenses exceed 80% of budget.') }}</p>
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
                                <h4 class="text-sm font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-4">{{ __('News & Updates') }}</h4>
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-medium text-dark dark:text-white">{{ __('Weekly Report') }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Expense summary sent to email.') }}</p>
                                        </div>
                                        <div class="relative inline-block w-10 mr-2 align-middle select-none transition duration-200 ease-in">
                                            <input type="checkbox" id="notify_weekly_report" name="notify_weekly_report" value="1" {{ ($user->notify_weekly_report ?? false) ? 'checked' : '' }} class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white dark:bg-gray-800 border-4 appearance-none cursor-pointer border-gray-300 dark:border-gray-600"/>
                                            <label for="notify_weekly_report" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 dark:bg-gray-600 cursor-pointer"></label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end pt-6 mt-6 border-t border-gray-100 dark:border-gray-700">
                                <button type="button" onclick="updateNotificationPreferences()" class="bg-primary text-white px-6 py-2 rounded-lg text-sm font-medium transition">{{ __('Save') }}</button>
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
                <h3 class="text-xl font-bold text-red-700 mb-2">{{ __('Delete Account') }}</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">{{ __('This action cannot be undone. All your data will be permanently deleted.') }}</p>
                
                <form id="deleteAccountForm" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('Enter password to confirm') }}</label>
                        <input type="password" name="password" id="delete_password" required class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-dark dark:text-white rounded-lg text-sm focus:outline-none focus:border-red-500">
                        <span class="error-message text-red-500 text-xs mt-1 hidden" id="error_delete_password"></span>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('Type "DELETE" to confirm') }}</label>
                        <input type="text" name="confirm_text" id="confirm_text" required class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-dark dark:text-white rounded-lg text-sm focus:outline-none focus:border-red-500" placeholder="{{ __('DELETE') }}">
                        <span class="error-message text-red-500 text-xs mt-1 hidden" id="error_confirm_text"></span>
                    </div>
                    
                    <div class="flex gap-3 pt-4">
                        <button type="submit" class="flex-1 bg-primary text-white px-4 py-2.5 rounded-lg text-sm font-medium transition">
                            {{ __('Delete Account') }}
                        </button>
                        <button type="button" onclick="closeDeleteAccountModal()" class="px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                            {{ __('Cancel') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript Logic -->
    <script>
        // Suppress non-critical console errors from browser extensions
        (function() {
            const originalError = console.error;
            console.error = function(...args) {
                // Filter out errors from browser extensions (autofill, etc.)
                const errorStr = args.join(' ');
                if (errorStr.includes('autofill') || 
                    errorStr.includes('ERR_BLOCKED_BY_CLIENT') ||
                    errorStr.includes('extension') ||
                    errorStr.includes('Missing typeId or itemId')) {
                    // Suppress these errors - they're from browser extensions, not our app
                    return;
                }
                originalError.apply(console, args);
            };
        })();
        
        // Function to toggle mobile sidebar
        function toggleMobileSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobileSidebarOverlay');
            if (sidebar && overlay) {
                const isHidden = sidebar.classList.contains('-translate-x-full');
                if (isHidden) {
                    sidebar.classList.remove('-translate-x-full');
                    overlay.classList.remove('hidden');
                    document.body.style.overflow = 'hidden'; // Prevent body scroll when sidebar is open
                } else {
                    sidebar.classList.add('-translate-x-full');
                    overlay.classList.add('hidden');
                    document.body.style.overflow = ''; // Restore body scroll
                }
            }
        }
        
        // Ensure sidebar is properly positioned on desktop - More robust for cross-browser
        function ensureDesktopLayout() {
            const sidebar = document.getElementById('sidebar');
            if (sidebar && window.innerWidth >= 768) {
                // Force sidebar to be relative on desktop with vendor prefixes
                sidebar.style.position = 'relative';
                sidebar.style.transform = 'translateX(0)';
                sidebar.style.webkitTransform = 'translateX(0)';
                sidebar.style.mozTransform = 'translateX(0)';
                sidebar.style.msTransform = 'translateX(0)';
                sidebar.style.left = 'auto';
                sidebar.style.right = 'auto';
                sidebar.style.top = 'auto';
                sidebar.style.bottom = 'auto';
                sidebar.classList.remove('-translate-x-full');
                
                // Ensure body is flex
                document.body.style.display = 'flex';
                document.body.style.webkitDisplay = 'flex';
                
                // Ensure main content is flex-1
                const main = document.querySelector('main');
                if (main) {
                    main.style.flex = '1';
                    main.style.webkitFlex = '1';
                    main.style.minWidth = '0';
                }
            } else if (sidebar && window.innerWidth < 768) {
                // On mobile, ensure sidebar is hidden by default
                if (!sidebar.classList.contains('-translate-x-full')) {
                    sidebar.classList.add('-translate-x-full');
                }
            }
        }
        
        // Run immediately on load
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', ensureDesktopLayout);
        } else {
            ensureDesktopLayout();
        }
        
        // Run after a short delay to ensure Tailwind is loaded
        setTimeout(ensureDesktopLayout, 100);
        setTimeout(ensureDesktopLayout, 500);
        
        // Close sidebar when clicking on menu items (mobile only)
        document.addEventListener('DOMContentLoaded', function() {
            ensureDesktopLayout();
            
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobileSidebarOverlay');
            const menuItems = sidebar?.querySelectorAll('.nav-item, a[href]');
            
            if (menuItems) {
                menuItems.forEach(item => {
                    item.addEventListener('click', function() {
                        // Only close on mobile
                        if (window.innerWidth < 768) {
                            if (sidebar && overlay) {
                                sidebar.classList.add('-translate-x-full');
                                overlay.classList.add('hidden');
                                document.body.style.overflow = '';
                            }
                        }
                    });
                });
            }
        });
        
        // Re-check on window resize
        window.addEventListener('resize', function() {
            ensureDesktopLayout();
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

        // Switch Settings View (kept for compatibility, but now using links)
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
                btnElement.className = 'nav-item w-full flex items-center gap-3 px-3 py-2.5 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 rounded-lg font-medium transition text-left';
            }

            // 5. Update Header Title based on view
            const titles = {
                'profile': 'Profil Saya',
                'security': 'Keamanan & Login',
                'notifications': 'Pengaturan Notifikasi'
            };
            document.getElementById('page-title').innerText = titles[viewId] || 'Pengaturan';
        }

        // Set initial view based on URL path
        document.addEventListener('DOMContentLoaded', function() {
            const pathname = window.location.pathname;
            let defaultView = 'profile';
            let navButton = null;
            
            // Determine view based on pathname
            if (pathname.includes('/profile/security') || pathname === '/profile/security') {
                defaultView = 'security';
                navButton = document.getElementById('nav-security');
            } else if (pathname.includes('/profile/notifications') || pathname === '/profile/notifications') {
                defaultView = 'notifications';
                navButton = document.getElementById('nav-notifications');
            } else {
                // Default view is Profile
                defaultView = 'profile';
                navButton = document.getElementById('nav-profile');
            }
            
            // Switch to the appropriate view
            if (navButton && typeof switchSettings === 'function') {
                switchSettings(defaultView, navButton);
            }
        });

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
                const response = await fetch('{{ route("profile.notifications.update") }}', {
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

