<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\GroupInvitationController;
use App\Http\Controllers\GroupMemberController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecurringExpenseController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    // Accounts
    Route::resource('accounts', AccountController::class);

    // Categories
    Route::resource('categories', CategoryController::class)->except(['show', 'destroy']);
    Route::patch('categories/{category}/toggle', [CategoryController::class, 'toggle'])->name('categories.toggle');
    Route::patch('categories/{category}/lock', [CategoryController::class, 'toggleLock'])->name('categories.lock');

    // Income
    Route::resource('incomes', IncomeController::class)->except(['show']);

    // Expenses
    Route::resource('expenses', ExpenseController::class)->except(['show']);

    // Transfers
    Route::resource('transfers', TransferController::class)->only(['index', 'create', 'store', 'destroy']);

    // Budgets
    Route::resource('budgets', BudgetController::class);
    Route::post('budgets/{budget}/category-budgets', [BudgetController::class, 'storeCategoryBudgets'])->name('budgets.category-budgets.store');

    // Recurring Expenses
    Route::resource('recurring-expenses', RecurringExpenseController::class)->except(['show']);
    Route::patch('recurring-expenses/{recurring_expense}/toggle', [RecurringExpenseController::class, 'toggle'])->name('recurring-expenses.toggle');

    // Activity Logs
    Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');

    // Groups
    Route::resource('groups', GroupController::class);
    Route::post('groups/join', [GroupController::class, 'join'])->name('groups.join');
    Route::delete('groups/{group}/leave', [GroupController::class, 'leave'])->name('groups.leave');
    Route::post('groups/{group}/regenerate-code', [GroupController::class, 'regenerateCode'])->name('groups.regenerate-code');
    Route::delete('groups/{group}/members/{user}', [GroupController::class, 'removeMember'])->name('groups.remove-member');
    Route::patch('groups/{group}/members/{user}/role', [GroupController::class, 'changeRole'])->name('groups.change-role');

    // Group Members (dedicated controller)
    Route::get('groups/{group}/members', [GroupMemberController::class, 'index'])->name('groups.members.index');
    Route::patch('groups/{group}/members/{user}/update-role', [GroupMemberController::class, 'updateRole'])->name('groups.members.role');
    Route::delete('groups/{group}/members/{user}/remove', [GroupMemberController::class, 'remove'])->name('groups.members.remove');

    // Group Expenses
    Route::resource('groups.expenses', \App\Http\Controllers\GroupExpenseController::class)->except(['show']);

    // Group Settlements & Balances
    Route::get('groups/{group}/balances', [\App\Http\Controllers\GroupSettlementController::class, 'index'])->name('groups.balances.index');
    Route::post('groups/{group}/settlements', [\App\Http\Controllers\GroupSettlementController::class, 'store'])->name('groups.settlements.store');
    Route::delete('groups/{group}/settlements/{settlement}', [\App\Http\Controllers\GroupSettlementController::class, 'destroy'])->name('groups.settlements.destroy');

    // Group Invitations
    Route::post('groups/{group}/invitations', [GroupInvitationController::class, 'store'])->name('groups.invitations.store');
    Route::delete('groups/{group}/invitations/{invitation}', [GroupInvitationController::class, 'revoke'])->name('groups.invitations.revoke');
    Route::get('invitations', [GroupInvitationController::class, 'myInvitations'])->name('invitations.mine');
    Route::post('invitations/{token}/accept', [GroupInvitationController::class, 'accept'])->name('invitations.accept');
    Route::delete('invitations/{token}/decline', [GroupInvitationController::class, 'decline'])->name('invitations.decline');

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/account', [ReportController::class, 'accountReport'])->name('account');
        Route::get('/category', [ReportController::class, 'categoryReport'])->name('category');
        Route::get('/budgets', [\App\Http\Controllers\BudgetReportController::class, 'index'])->name('budgets.index');
        Route::get('/budgets/{budget}', [\App\Http\Controllers\BudgetReportController::class, 'show'])->name('budgets.show');
    });

    // Exports
    Route::prefix('exports')->name('exports.')->group(function () {
        Route::get('/expenses/csv', [\App\Http\Controllers\ExportController::class, 'exportExpensesCsv'])->name('expenses.csv');
        Route::get('/incomes/csv', [\App\Http\Controllers\ExportController::class, 'exportIncomesCsv'])->name('incomes.csv');
        Route::get('/monthly-statement/pdf', [\App\Http\Controllers\ExportController::class, 'exportMonthlyStatementPdf'])->name('statement.pdf');
    });

    // Financial Health
    Route::get('/health-score', [\App\Http\Controllers\FinancialHealthController::class, 'show'])->name('health-score.show');

    // Alert Rules
    Route::resource('alert-rules', \App\Http\Controllers\AlertRuleController::class)->except(['create', 'show', 'edit']);
    Route::patch('alert-rules/{alert_rule}/toggle', [\App\Http\Controllers\AlertRuleController::class, 'toggle'])->name('alert-rules.toggle');

    // Notifications
    Route::get('notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('notifications/{notification}/read', [\App\Http\Controllers\NotificationController::class, 'markRead'])->name('notifications.mark-read');
    Route::post('notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');

    // Settings & Modes
    Route::get('/settings', [\App\Http\Controllers\SettingsController::class, 'index'])->name('settings.index');
    Route::patch('/settings/emergency-mode', [\App\Http\Controllers\SettingsController::class, 'toggleEmergencyMode'])->name('settings.emergency-mode.toggle');
    Route::patch('/settings/focus-mode', [\App\Http\Controllers\SettingsController::class, 'toggleFocusMode'])->name('settings.focus-mode.toggle');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin Routes
Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('users/{user}', [AdminUserController::class, 'show'])->name('users.show');
});

require __DIR__.'/auth.php';
