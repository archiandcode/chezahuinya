<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BuffetReportController;
use App\Http\Controllers\CashBalanceController;
use App\Http\Controllers\CashDirectoryController;
use App\Http\Controllers\CashTransactionController;
use App\Http\Controllers\ConstructionPaymentController;
use App\Http\Controllers\DailyReportController;
use App\Http\Controllers\DebtCreditReportController;
use App\Http\Controllers\RapReportController;
use App\Http\Controllers\ReportCompanyController;
use App\Http\Controllers\TradingStockBalanceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::resource('cash-transactions', CashTransactionController::class)
        ->only(['index', 'store', 'update', 'destroy']);
    Route::get('cash-directories', [CashDirectoryController::class, 'index'])->name('cash-directories.index');
    Route::post('cash-registers', [CashDirectoryController::class, 'storeRegister'])->name('cash-registers.store');
    Route::put('cash-registers/{cash_register}', [CashDirectoryController::class, 'updateRegister'])->name('cash-registers.update');
    Route::delete('cash-registers/{cash_register}', [CashDirectoryController::class, 'destroyRegister'])->name('cash-registers.destroy');
    Route::post('cash-companies', [CashDirectoryController::class, 'storeCompany'])->name('cash-companies.store');
    Route::put('cash-companies/{cash_company}', [CashDirectoryController::class, 'updateCompany'])->name('cash-companies.update');
    Route::delete('cash-companies/{cash_company}', [CashDirectoryController::class, 'destroyCompany'])->name('cash-companies.destroy');
    Route::post('cash-flow-categories', [CashDirectoryController::class, 'storeCashFlow'])->name('cash-flow-categories.store');
    Route::put('cash-flow-categories/{cash_flow_category}', [CashDirectoryController::class, 'updateCashFlow'])->name('cash-flow-categories.update');
    Route::delete('cash-flow-categories/{cash_flow_category}', [CashDirectoryController::class, 'destroyCashFlow'])->name('cash-flow-categories.destroy');
    Route::resource('construction-payments', ConstructionPaymentController::class)
        ->only(['index', 'store', 'update', 'destroy']);
    Route::resource('daily-reports', DailyReportController::class)
        ->only(['index', 'store', 'update', 'destroy']);
    Route::resource('cash-balances', CashBalanceController::class)
        ->only(['index', 'store', 'update', 'destroy']);
    Route::resource('trading-stock-balances', TradingStockBalanceController::class)
        ->only(['index', 'store', 'update', 'destroy']);
    Route::resource('rap-reports', RapReportController::class)
        ->only(['index', 'store', 'update', 'destroy']);
    Route::resource('debt-credit-reports', DebtCreditReportController::class)
        ->only(['index', 'store', 'update', 'destroy']);
    Route::resource('buffet-reports', BuffetReportController::class)
        ->only(['index', 'store', 'update', 'destroy']);
    Route::resource('report-companies', ReportCompanyController::class)
        ->only(['index', 'store', 'update', 'destroy']);
    Route::post('report-companies/{report_company}/accounts', [ReportCompanyController::class, 'storeAccount'])
        ->name('report-companies.accounts.store');
    Route::put('report-company-accounts/{account}', [ReportCompanyController::class, 'updateAccount'])
        ->name('report-company-accounts.update');
    Route::delete('report-company-accounts/{account}', [ReportCompanyController::class, 'destroyAccount'])
        ->name('report-company-accounts.destroy');

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});
