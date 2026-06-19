<?php

use Illuminate\Support\Facades\Route;
use Src\Reports\Infrastructure\Http\Controllers\ReportsController;

Route::prefix('v1/reports')->middleware(['web'])->group(function () {
    Route::get('/', [ReportsController::class, 'index'])->name('reports.index');
    Route::get('/transactions', [ReportsController::class, 'transactions'])->name('reports.transactions');
});
