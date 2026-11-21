<?php

use Illuminate\Support\Facades\Route;
use Src\Dashboard\Infrastructure\Http\Controllers\DashboardController;

// Inicio: público (los invitados ven la portada sin datos)
Route::middleware('web')->get('/', [DashboardController::class, 'home'])->name('home');

// Transparencia financiera: pública, se comparte por enlace (solo nombres y total pagado sin mora)
Route::middleware('web')->get('/transparencia/{uuid}', [DashboardController::class, 'transparency'])->name('transparency');

// Dashboard: solo con sesión
Route::middleware(['web', 'auth'])->get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
