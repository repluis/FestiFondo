<?php

use Illuminate\Support\Facades\Route;
use Src\Auth\Infrastructure\Http\Controllers\AuthController;

Route::middleware('web')->group(function () {
    Route::get('/login',    [AuthController::class, 'create'])->name('auth.login');
    Route::post('/login',   [AuthController::class, 'store'])->name('auth.login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('auth.register');
    Route::post('/register',[AuthController::class, 'register'])->name('auth.register.store');
    Route::post('/logout',  [AuthController::class, 'destroy'])->name('auth.logout');
});
