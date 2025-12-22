<?php

use App\Http\Controllers\{
    DashboardController,
    LoginController,
    TrafficFlowController
};

Route::get('/', [LoginController::class, 'index'])->name('login');
Route::post('/login', [LoginController::class, 'process'])->name('login.process');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->name('dashboard');

Route::get('/traffic-flow', [TrafficFlowController::class, 'index'])
    ->name('traffic.flow');

