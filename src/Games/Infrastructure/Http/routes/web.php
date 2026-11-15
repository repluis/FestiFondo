<?php

use Illuminate\Support\Facades\Route;
use Src\Games\Infrastructure\Http\Controllers\GamesController;

// Públicas: la página (los invitados ven un aviso) y el enlace personal de cada participante
Route::prefix('v1/games/secret-santa')->middleware(['web'])->group(function () {
    Route::get('/',          [GamesController::class, 'secretSanta'])->name('games.secret-santa');
    Route::get('/r/{token}', [GamesController::class, 'revealPage'])->name('games.secret-santa.reveal-page');
    Route::post('/r/{token}', [GamesController::class, 'reveal'])->middleware('throttle:30,1')->name('games.secret-santa.reveal');
});

// Solo admin (sesión iniciada): sortear, historial y restaurar enlaces
Route::prefix('v1/games/secret-santa')->middleware(['web', 'auth'])->group(function () {
    Route::get('/campaigns/{uuid}/members',     [GamesController::class, 'campaignMembers'])->name('games.secret-santa.members');
    Route::get('/campaigns/{uuid}/latest-draw', [GamesController::class, 'latestDraw'])->name('games.secret-santa.latest');
    Route::get('/campaigns/{uuid}/exclusions',    [GamesController::class, 'exclusions'])->name('games.secret-santa.exclusions');
    Route::post('/campaigns/{uuid}/exclusions',   [GamesController::class, 'addExclusion'])->name('games.secret-santa.exclusions.add');
    Route::delete('/campaigns/{uuid}/exclusions', [GamesController::class, 'removeExclusion'])->name('games.secret-santa.exclusions.remove');
    Route::post('/draw',                        [GamesController::class, 'drawSecretSanta'])->name('games.secret-santa.draw');
    Route::get('/draws',                        [GamesController::class, 'history'])->name('games.secret-santa.history');
    Route::get('/draws/{uuid}',                 [GamesController::class, 'showDraw'])->name('games.secret-santa.show-draw');
    Route::post('/r/{token}/reset',             [GamesController::class, 'resetView'])->name('games.secret-santa.reset');
});
