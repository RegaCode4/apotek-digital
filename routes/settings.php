<?php

/**
 * File: settings.php
 *
 * Route untuk pengaturan profil, tampilan, dan keamanan pengguna.
 */

use Illuminate\Support\Facades\Route;

// Pengaturan profil — membutuhkan login
Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::livewire('settings/profile', 'pages::settings.profile')->name('profile.edit');
});

// Pengaturan tambahan — login + verified email
Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('settings/appearance', 'pages::settings.appearance')->name('appearance.edit');

    // Keamanan — membutuhkan konfirmasi password
    Route::livewire('settings/security', 'pages::settings.security')
        ->middleware([
            'password.confirm',
        ])
        ->name('security.edit');
});
