<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionTemplateController;
use App\Http\Controllers\UserSettingsController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', [LandingPageController::class, 'index'])->name('landing');
Route::get('/language/{locale}', function ($locale, \Illuminate\Http\Request $request) {
    if (in_array($locale, ['id', 'en'])) {
        // Set locale in session using Session facade to ensure it's saved
        \Illuminate\Support\Facades\Session::put('locale', $locale);
        \Illuminate\Support\Facades\Session::save();
        
        // Set locale for current request
        app()->setLocale($locale);
        config(['app.locale' => $locale]);
        
        // Check if redirect parameter exists
        $redirect = $request->get('redirect');
        if ($redirect) {
            return redirect($redirect);
        }
        
        // Redirect back to previous page or dashboard/landing
        $previousUrl = url()->previous();
        if ($previousUrl && $previousUrl !== url()->current() && !str_contains($previousUrl, '/language/')) {
            return redirect($previousUrl);
        }
        
        // If no previous URL or same page, redirect to dashboard if authenticated, else landing
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }
        return redirect()->route('landing');
    }
    return back();
})->name('language.switch');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    
    // Password Reset Routes
    Route::get('/password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');
});

// Email Verification Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (\Illuminate\Foundation\Auth\EmailVerificationRequest $request) {
        $request->fulfill();
        
        // Logout user after verification
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        // Redirect to login with success message
        return redirect()->route('login')->with('success', __('Email verified successfully! Please login to continue.'));
    })->middleware(['signed'])->name('verification.verify');

    Route::post('/email/verification-notification', function (\Illuminate\Http\Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('message', __('Verification link sent!'));
    })->middleware(['throttle:6,1'])->name('verification.send');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    
    // Dashboard routes with different URLs - must be defined before resource routes
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/transactions', [DashboardController::class, 'index'])->name('transactions.index');
    Route::get('/wallets', [DashboardController::class, 'index'])->name('wallets.index');
    Route::get('/categories', [DashboardController::class, 'index'])->name('categories.index');
    Route::get('/reports', [DashboardController::class, 'index'])->name('reports.index');
    Route::get('/budget', [DashboardController::class, 'index'])->name('budget.index');
    Route::get('/debts', [DashboardController::class, 'index'])->name('debts.index');
    
    // Transactions - resource routes (index is already defined above)
    Route::get('/transactions/form-data', [TransactionController::class, 'getFormData'])->name('transactions.formData');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
    Route::get('/transactions/create', [TransactionController::class, 'create'])->name('transactions.create');
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
    Route::put('/transactions/{transaction}', [TransactionController::class, 'update'])->name('transactions.update');
    Route::patch('/transactions/{transaction}', [TransactionController::class, 'update']);
    Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');
    Route::get('/transactions/{transaction}/edit', [TransactionController::class, 'edit'])->name('transactions.edit');
    
    // Accounts
    Route::resource('accounts', AccountController::class);
    Route::post('/accounts/{id}/reconcile', [AccountController::class, 'reconcile'])->name('accounts.reconcile');
    
    // Categories - Note: categories.index route is defined above for dashboard view
    Route::get('/categories/{id}/detail', [CategoryController::class, 'show'])->name('categories.show');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::post('/categories/{id}', [CategoryController::class, 'update'])->name('categories.update'); // Support method spoofing
    Route::put('/categories/{id}', [CategoryController::class, 'update']); // Also support direct PUT
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    
    // Budgets
    Route::resource('budgets', BudgetController::class)->except(['show', 'edit']);
    Route::post('/budgets/rollover', [BudgetController::class, 'processRollover'])->name('budgets.rollover');
    
    // Debts - index is already defined above for dashboard view
    Route::get('/debts/{id}/detail', [DebtController::class, 'show'])->name('debts.show');
    Route::post('/debts', [DebtController::class, 'store'])->name('debts.store');
    Route::get('/debts/create', [DebtController::class, 'create'])->name('debts.create');
    Route::post('/debts/{id}', [DebtController::class, 'update'])->name('debts.update'); // Support method spoofing
    Route::put('/debts/{id}', [DebtController::class, 'update']); // Also support direct PUT
    Route::delete('/debts/{debt}', [DebtController::class, 'destroy'])->name('debts.destroy');
    Route::post('/debts/{id}/payment', [DebtController::class, 'addPayment'])->name('debts.payment');
    Route::post('/debts/{id}/reminder', [DebtController::class, 'createReminder'])->name('debts.reminder');
    Route::post('/debts/{id}/mark-paid', [DebtController::class, 'markAsPaid'])->name('debts.markPaid');
    
    // Reports - index is already defined above for dashboard view
    Route::get('/reports/trends', [ReportController::class, 'trends'])->name('reports.trends');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
    Route::get('/reports/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.exportPdf');
    
    // Templates
    Route::resource('templates', TransactionTemplateController::class)->except(['show', 'edit', 'update']);
    Route::post('/templates/{id}/use', [TransactionTemplateController::class, 'useTemplate'])->name('templates.use');
    
    // Settings
    Route::post('/settings', [UserSettingsController::class, 'updateSettings'])->name('settings.update');
    
    // Profile routes with different URLs
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::get('/profile/security', [ProfileController::class, 'index'])->name('profile.security');
    Route::get('/profile/notifications', [ProfileController::class, 'index'])->name('profile.notifications');
    
    // Profile actions
    Route::post('/profile/update', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo');
    Route::post('/profile/preferences', [ProfileController::class, 'updatePreferences'])->name('profile.preferences');
    Route::post('/profile/timezone', [ProfileController::class, 'updateTimezone'])->name('profile.timezone');
    Route::post('/profile/notifications/update', [ProfileController::class, 'updateNotificationPreferences'])->name('profile.notifications.update');
    Route::post('/profile/password', [ProfileController::class, 'changePassword'])->name('profile.password');
    Route::post('/profile/delete', [ProfileController::class, 'deleteAccount'])->name('profile.delete');
    
    // Notifications
    Route::get('/notifications', [DashboardController::class, 'getNotifications'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [DashboardController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [DashboardController::class, 'markAllAsRead'])->name('notifications.readAll');
});

