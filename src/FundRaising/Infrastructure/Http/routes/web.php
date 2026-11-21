<?php

use Illuminate\Support\Facades\Route;
use Src\FundRaising\Infrastructure\Http\Controllers\FundRaisingController;

Route::prefix('v1/financial/fund-raising')->middleware(['web', 'auth'])->group(function () {
    Route::get('/',                 [FundRaisingController::class, 'index'])->name('fund-raising.index');
    Route::post('/process-charges/preview', [FundRaisingController::class, 'previewCharges'])->name('fund-raising.process-charges.preview');
    Route::post('/process-charges',         [FundRaisingController::class, 'processCharges'])->name('fund-raising.process-charges');
    Route::post('/executions/{id}/revert',  [FundRaisingController::class, 'revertExecution'])->whereNumber('id')->name('fund-raising.executions.revert');
});
