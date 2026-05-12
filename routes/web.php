<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CashTransactionController;
use App\Http\Controllers\ConstructionPaymentController;
use App\Http\Controllers\DailyReportController;
use App\Http\Controllers\ReportCompanyController;
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
    Route::resource('construction-payments', ConstructionPaymentController::class)
        ->only(['index', 'store', 'update', 'destroy']);
    Route::resource('daily-reports', DailyReportController::class)
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
