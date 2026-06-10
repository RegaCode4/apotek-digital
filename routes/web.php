<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::prefix('sistem')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('sistem.login');
    Route::post('/login', [AuthController::class, 'login'])->name('sistem.login.post');
    Route::post('/logout', [AuthController::class, 'logout'])->name('sistem.logout');

    // Placeholder dashboard route for redirection target
    Route::get('/dashboard', function () {
        return 'Dashboard Sistem';
    })->name('dashboard.sistem');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
