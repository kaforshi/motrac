<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
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

Route::get('/', [LandingPageController::class, 'index'])->name('landing');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Transactions
    Route::get('/transactions/form-data', [TransactionController::class, 'getFormData'])->name('transactions.formData');
    Route::resource('transactions', TransactionController::class);
    
    // Accounts
    Route::resource('accounts', AccountController::class);
    Route::post('/accounts/{id}/reconcile', [AccountController::class, 'reconcile'])->name('accounts.reconcile');
    
    // Categories
    Route::resource('categories', CategoryController::class)->except(['show', 'edit', 'update']);
    Route::post('/categories/{id}', [CategoryController::class, 'update'])->name('categories.update');
    
    // Budgets
    Route::resource('budgets', BudgetController::class)->except(['show', 'edit']);
    Route::post('/budgets/rollover', [BudgetController::class, 'processRollover'])->name('budgets.rollover');
    
    // Debts
    Route::get('/debts/{id}/detail', [DebtController::class, 'show'])->name('debts.show');
    Route::resource('debts', DebtController::class)->except(['show', 'edit', 'update']);
    Route::post('/debts/{id}/payment', [DebtController::class, 'addPayment'])->name('debts.payment');
    Route::post('/debts/{id}/reminder', [DebtController::class, 'createReminder'])->name('debts.reminder');
    Route::post('/debts/{id}/mark-paid', [DebtController::class, 'markAsPaid'])->name('debts.markPaid');
    
    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/trends', [ReportController::class, 'trends'])->name('reports.trends');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
    Route::get('/reports/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.exportPdf');
    
    // Templates
    Route::resource('templates', TransactionTemplateController::class)->except(['show', 'edit', 'update']);
    Route::post('/templates/{id}/use', [TransactionTemplateController::class, 'useTemplate'])->name('templates.use');
    
    // Settings
    Route::post('/settings', [UserSettingsController::class, 'updateSettings'])->name('settings.update');
    
    // Profile
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::post('/profile/update', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo');
    Route::post('/profile/preferences', [ProfileController::class, 'updatePreferences'])->name('profile.preferences');
    Route::post('/profile/notifications', [ProfileController::class, 'updateNotificationPreferences'])->name('profile.notifications');
    Route::post('/profile/password', [ProfileController::class, 'changePassword'])->name('profile.password');
    Route::post('/profile/delete', [ProfileController::class, 'deleteAccount'])->name('profile.delete');
    
    // Notifications
    Route::get('/notifications', [DashboardController::class, 'getNotifications'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [DashboardController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [DashboardController::class, 'markAllAsRead'])->name('notifications.readAll');
});

