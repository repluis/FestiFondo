<?php

use Illuminate\Support\Facades\Route;
use Src\Campaigns\Infrastructure\Http\Controllers\CampaignController;

Route::prefix('v1/financial/campaigns')->middleware(['web'])->group(function () {
    Route::get('/',                  [CampaignController::class, 'index'])->name('campaigns.index');
    Route::get('/create',            [CampaignController::class, 'create'])->name('campaigns.create');
    Route::post('/',                 [CampaignController::class, 'store'])->name('campaigns.store');
    Route::get('/{uuid}',            [CampaignController::class, 'show'])->name('campaigns.show');
    Route::get('/{uuid}/edit',       [CampaignController::class, 'edit'])->name('campaigns.edit');
    Route::put('/{uuid}',            [CampaignController::class, 'update'])->name('campaigns.update');
    Route::delete('/{uuid}',         [CampaignController::class, 'destroy'])->name('campaigns.destroy');

    // Campaign members
    Route::post('/{uuid}/members',                [CampaignController::class, 'enrollMembers'])->name('campaigns.members.enroll');
    Route::delete('/{uuid}/members/{memberUuid}', [CampaignController::class, 'removeMember'])->name('campaigns.members.remove');
    Route::get('/{uuid}/available-members',                    [CampaignController::class, 'availableMembers'])->name('campaigns.members.available');
    Route::get('/{uuid}/members/{memberOid}/transactions',     [CampaignController::class, 'memberTransactions'])->name('campaigns.members.transactions');
});
