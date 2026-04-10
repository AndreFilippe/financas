<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\CreditCardController;
use App\Http\Controllers\InvestmentController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ShoppingListController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ActivityLogController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest')->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/cards', [DashboardController::class, 'cards'])->name('dashboard.cards');
    
    Route::get('/profile', [UserController::class, 'editProfile'])->name('profile.edit');
    Route::put('/profile/password', [UserController::class, 'updateProfile'])->name('profile.update');

    Route::resource('users', UserController::class);
    Route::resource('accounts', AccountController::class);
    Route::resource('categories', CategoryController::class);
    
    Route::get('/payable-transactions', [TransactionController::class, 'payable'])->name('transactions.payable');
    Route::post('/transactions/{transaction}/pay', [TransactionController::class, 'pay'])->name('transactions.pay');
    Route::post('/transactions/{transaction}/stop-recurrence', [TransactionController::class, 'stopRecurrence'])->name('transactions.stop-recurrence');
    Route::post('/transactions/replicate-recurrences', [TransactionController::class, 'replicateRecurrences'])->name('transactions.replicate-recurrences');
    Route::resource('transactions', TransactionController::class);
    
    Route::post('/credit-card-invoices/{invoice}/pay', [CreditCardController::class, 'payInvoice'])->name('credit-cards.pay-invoice');
    Route::post('/credit-card-transactions/{transaction}/update-category', [TransactionController::class, 'updateCardCategory'])->name('credit-card-transactions.update-category');
    Route::resource('credit-cards', CreditCardController::class);
    Route::post('/credit-cards/{credit_card}/transactions', [CreditCardController::class, 'addTransaction'])->name('credit-cards.transactions.store');
    Route::post('/credit-cards/{credit_card}/import', [CreditCardController::class, 'import'])->name('credit-cards.import');

    // Módulo de Compras
    Route::get('/shopping-lists/dashboard', [ShoppingListController::class, 'dashboard'])->name('shopping-lists.dashboard');
    Route::resource('shopping-lists', ShoppingListController::class);
    Route::post('/shopping-lists/{shopping_list}/items', [ShoppingListController::class, 'addItem'])->name('shopping-lists.items.add');
    Route::patch('/shopping-list-items/{item}', [ShoppingListController::class, 'updateItem'])->name('shopping-list-items.update');
    Route::delete('/shopping-list-items/{item}', [ShoppingListController::class, 'deleteItem'])->name('shopping-list-items.delete');
    Route::post('/shopping-lists/{shopping_list}/finish', [ShoppingListController::class, 'finish'])->name('shopping-lists.finish');
    Route::get('/shopping-lists/{shopping_list}/copy', [ShoppingListController::class, 'copy'])->name('shopping-lists.copy');
    Route::post('/shopping-lists/{shopping_list}/duplicate', [ShoppingListController::class, 'duplicate'])->name('shopping-lists.duplicate');
    
    Route::resource('investments', InvestmentController::class);
    
    Route::get('/logs', [ActivityLogController::class, 'index'])->name('logs.index');
});
