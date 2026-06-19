<?php

use Illuminate\Support\Facades\Route;
use Src\Transactions\Infrastructure\Http\Controllers\TransactionController;

Route::prefix('v1/financial/transactions')->middleware(['web'])->group(function () {
    Route::get('/',                [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/create',          [TransactionController::class, 'create'])->name('transactions.create');
    Route::post('/',               [TransactionController::class, 'store'])->name('transactions.store');
    Route::post('/apply-payment',  [TransactionController::class, 'applyPayment'])->name('transactions.apply-payment');
    Route::get('/{uuid}',          [TransactionController::class, 'show'])->name('transactions.show');
    Route::delete('/{uuid}',       [TransactionController::class, 'destroy'])->name('transactions.destroy');
});
