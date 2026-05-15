<?php

use App\Http\Controllers\DailyReportController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('reports.index');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
    Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
    Route::get('/reports/import', [DailyReportController::class, 'import'])->name('reports.import');
    Route::post('/reports/import', [DailyReportController::class, 'storeImport'])->name('reports.import.store');
    Route::get('/weekly-report', [DailyReportController::class, 'weekly'])->name('reports.weekly');
    Route::get('/weekly-report/export', [DailyReportController::class, 'exportWeekly'])->name('reports.weekly.export');
    Route::get('/monthly-report', [DailyReportController::class, 'monthly'])->name('reports.monthly');
    Route::get('/monthly-report/export', [DailyReportController::class, 'exportMonthly'])->name('reports.monthly.export');
    Route::get('/reports/{report}/export', [DailyReportController::class, 'export'])->name('reports.export');
    Route::resource('reports', DailyReportController::class);
});
