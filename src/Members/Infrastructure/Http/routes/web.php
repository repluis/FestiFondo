<?php

use Illuminate\Support\Facades\Route;
use Src\Members\Infrastructure\Http\Controllers\MembersController;

Route::prefix('v1/financial/members')->middleware(['web'])->group(function () {
    Route::get('/',             [MembersController::class, 'index'])->name('members.index');
    Route::get('/create',       [MembersController::class, 'create'])->name('members.create');
    Route::post('/',            [MembersController::class, 'store'])->name('members.store');
    Route::get('/{uuid}',       [MembersController::class, 'show'])->name('members.show');
    Route::get('/{uuid}/edit',  [MembersController::class, 'edit'])->name('members.edit');
    Route::put('/{uuid}',       [MembersController::class, 'update'])->name('members.update');
    Route::delete('/{uuid}',          [MembersController::class, 'destroy'])->name('members.destroy');
    Route::patch('/{uuid}/activate',  [MembersController::class, 'activate'])->name('members.activate');
});
