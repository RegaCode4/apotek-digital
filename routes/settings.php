<?php

/**
 * File: settings.php
 *
 * Route untuk pengaturan profil, tampilan, dan keamanan pengguna.
 */

use Illuminate\Support\Facades\Route;

/**
 * Grup rute untuk pengaturan profil pengguna.
 * Membutuhkan autentikasi (harus login).
 */
Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::livewire('settings/profile', 'pages::settings.profile')->name('profile.edit');
});

/**
 * Grup rute untuk pengaturan tambahan (tampilan dan keamanan).
 * Membutuhkan autentikasi dan verifikasi email.
 */
Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('settings/appearance', 'pages::settings.appearance')->name('appearance.edit');

    /**
     * Rute untuk pengaturan keamanan (misal: ubah password).
     * Membutuhkan konfirmasi password sebelum mengaksesnya.
     */
    Route::livewire('settings/security', 'pages::settings.security')
        ->middleware([
            'password.confirm',
        ])
        ->name('security.edit');
});
