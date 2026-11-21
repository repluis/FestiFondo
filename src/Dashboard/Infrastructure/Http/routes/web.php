<?php

use Illuminate\Support\Facades\Route;
use Src\Dashboard\Infrastructure\Http\Controllers\DashboardController;

// Inicio: público (los invitados ven la portada sin datos)
Route::middleware('web')->get('/', [DashboardController::class, 'home'])->name('home');

// Dashboard: solo con sesión
Route::middleware(['web', 'auth'])->get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
