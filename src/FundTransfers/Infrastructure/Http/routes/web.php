<?php

use Illuminate\Support\Facades\Route;
use Src\FundTransfers\Infrastructure\Http\Controllers\FundTransferController;

Route::prefix('v1/financial/fund-transfers')->middleware(['web'])->group(function () {
    Route::get('/', [FundTransferController::class, 'index'])->name('fund-transfers.index');
    Route::get('/create', [FundTransferController::class, 'create'])->name('fund-transfers.create');
    Route::post('/', [FundTransferController::class, 'store'])->name('fund-transfers.store');
    Route::get('/{id}', [FundTransferController::class, 'show'])->name('fund-transfers.show');
    Route::get('/{id}/edit', [FundTransferController::class, 'edit'])->name('fund-transfers.edit');
    Route::put('/{id}', [FundTransferController::class, 'update'])->name('fund-transfers.update');
    Route::delete('/{id}', [FundTransferController::class, 'destroy'])->name('fund-transfers.destroy');
});

