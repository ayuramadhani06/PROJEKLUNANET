<?php

use App\Http\Controllers\{
    DashboardController,
    LoginController,
    TrafficFlowController,
    ProfileController
};

Route::get('/', [LoginController::class, 'index'])->name('login');
Route::post('/login', [LoginController::class, 'process'])->name('login.process');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->name('dashboard');

Route::get('/traffic', [TrafficFlowController::class, 'index'])
    ->name('traffic.index');

Route::get('/profile', [ProfileController::class, 'index'])
    ->name('profile.index');

