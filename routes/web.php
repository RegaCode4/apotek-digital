<?php

use App\Http\Controllers\Auth\AuthController;
use App\Livewire\Inventaris\MedicineIndex;
use App\Livewire\Inventaris\MutasiStok;
use App\Livewire\Inventaris\StokOpname;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::prefix('sistem')->name('sistem.')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware(['auth.apotek'])->group(function () {
        Route::get('/dashboard', fn () => view('sistem.dashboard'))->name('dashboard');
    });

    Route::middleware(['auth.apotek', 'role:admin,pharmacist'])->group(function () {
        Route::get('/inventaris', fn () => view('sistem.inventaris'))->name('inventaris');
        Route::get('/laporan', fn () => view('sistem.laporan'))->name('laporan');
    });

    Route::middleware(['auth.apotek', 'role:cashier,admin,pharmacist'])->group(function () {
        Route::get('/pos', fn () => view('sistem.pos'))->name('pos');
    });

    Route::middleware(['auth.apotek', 'role:admin'])->group(function () {
        Route::get('/users', fn () => view('sistem.users'))->name('users');
    });
});

Route::middleware(['auth.apotek', 'role:admin,pharmacist'])
    ->get('/sistem/inventaris/obat', MedicineIndex::class)
    ->name('inventaris.medicines.index');

Route::middleware(['auth.apotek', 'role:admin,pharmacist'])
    ->get('/sistem/inventaris/stok-opname', StokOpname::class)
    ->name('inventaris.stok-opname');

Route::middleware(['auth.apotek', 'role:admin,pharmacist'])
    ->get('/sistem/inventaris/mutasi', MutasiStok::class)
    ->name('inventaris.mutasi');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
