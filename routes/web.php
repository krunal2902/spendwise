<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecurringExpenseController;
use App\Http\Controllers\TransferController;
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
