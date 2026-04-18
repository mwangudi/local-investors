<?php

use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () { return redirect('/admin/login');});

// Reports
Route::middleware(['auth'])->group(function () {
    Route::get('/reports/loans-pdf', [ReportController::class, 'loansReport'])->name('reports.loans-pdf');
});
