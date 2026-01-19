<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    DashboardController,
    LoginController,
    TrafficFlowController,
    ProfileController,
    NotificationController
};

// --- Halaman Public ---
Route::get('/', [LoginController::class, 'index'])->name('login');
Route::post('/login', [LoginController::class, 'process'])->name('login.process');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// --- Halaman Private (Harus Login) ---
Route::middleware(['auth'])->group(function () {
    //dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/stats', [DashboardController::class, 'getLiveStats'])->name('dashboard.stats');
    Route::get('/dashboard/interface-history', [DashboardController::class, 'getInterfaceHistory'])->name('interface.history');
    //traffic flow
    Route::get('/traffic', [TrafficFlowController::class, 'index'])->name('traffic.index');
    //profile
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile/update', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    //notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    // Mark as read
    Route::get('/notifications/read/{id}', [NotificationController::class, 'markAsRead'])->name('notifications.read');

});