<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Public\LoginController;
use App\Http\Controllers\Public\RecoveryController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/', [LoginController::class, 'index'])->name('public.login.index');
    Route::post('/login', [LoginController::class, 'perform'])->name('public.login.perform');

    Route::get('/recovery', [RecoveryController::class, 'index'])->name('public.recovery.index');
    Route::post('/recovery', [RecoveryController::class, 'perform'])->name('public.recovery.perform');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard.index');
});
