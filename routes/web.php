<?php

/**
 * File: web.php
 *
 * Semua route web (browser) aplikasi apotek-digital.
 * Mencakup autentikasi, dashboard, inventaris, POS, laporan, dan manajemen pengguna.
 */

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Pos\StrukController;
use App\Livewire\Admin\UserManagement;
use App\Livewire\Inventaris\CategoryManagement;
use App\Livewire\Inventaris\MedicineIndex;
use App\Livewire\Inventaris\MutasiStok;
use App\Livewire\Inventaris\StokOpname;
use App\Livewire\Laporan\LaporanPage;
use App\Livewire\Pos\KasirPage;
use App\Livewire\Pos\RiwayatTransaksi;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

// Redirect root ke halaman login
Route::redirect('/', '/sistem/login')->name('home');

// Route group: seluruh sistem aplikasi (login, dashboard, dll)
Route::prefix('sistem')->name('sistem.')->group(function () {
    // Autentikasi — halaman login & logout
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Route yang membutuhkan login (auth.apotek)
    Route::middleware(['auth.apotek'])->group(function () {
        // Dashboard utama — menampilkan penjualan terbaru & ringkasan pembayaran hari ini
        Route::get('/dashboard', function () {
            $recentSales = Sale::query()
                ->with('cashier')
                ->orderByDesc('sale_date')
                ->limit(5)
                ->get();

            $paymentSummary = DB::table('sales')
                ->select('payment_method', DB::raw('SUM(grand_total) as total'), DB::raw('COUNT(*) as count'))
                ->whereDate('sale_date', today())
                ->groupBy('payment_method')
                ->orderByDesc('total')
                ->get();

            return view('sistem.dashboard', compact('recentSales', 'paymentSummary'));
        })->name('dashboard');
    });

    // Inventaris — hanya admin & pharmacist
    Route::middleware(['auth.apotek', 'role:admin,pharmacist'])->group(function () {
        Route::get('/inventaris', fn () => view('sistem.inventaris'))->name('inventaris');
    });

    // POS — cashier, admin, pharmacist
    Route::middleware(['auth.apotek', 'role:cashier,admin,pharmacist'])->group(function () {
        Route::get('/pos', fn () => view('sistem.pos'))->name('pos');
    });

    // Manajemen pengguna — khusus admin
    Route::middleware(['auth.apotek', 'role:admin'])->group(function () {
        Route::get('/users', fn () => view('sistem.users'))->name('users');
    });
});

// Daftar obat — admin & pharmacist
Route::middleware(['auth.apotek', 'role:admin,pharmacist'])
    ->get('/sistem/inventaris/obat', MedicineIndex::class)
    ->name('inventaris.medicines.index');

// Stok opname — admin & pharmacist
Route::middleware(['auth.apotek', 'role:admin,pharmacist'])
    ->get('/sistem/inventaris/stok-opname', StokOpname::class)
    ->name('inventaris.stok-opname');

// Mutasi stok — admin & pharmacist
Route::middleware(['auth.apotek', 'role:admin,pharmacist'])
    ->get('/sistem/inventaris/mutasi', MutasiStok::class)
    ->name('inventaris.mutasi');

// Manajemen kategori — admin & pharmacist
Route::middleware(['auth.apotek', 'role:admin,pharmacist'])
    ->get('/sistem/inventaris/kategori', CategoryManagement::class)
    ->name('inventaris.kategori');

// Halaman kasir POS — cashier, admin, pharmacist
Route::middleware(['auth.apotek', 'role:admin,pharmacist,cashier'])
    ->get('/sistem/pos', KasirPage::class)
    ->name('pos.kasir');

// Cetak struk — semua role yang sudah login
Route::middleware(['auth.apotek'])
    ->get('/sistem/pos/struk/{sale}', StrukController::class)
    ->name('pos.struk');

// Riwayat transaksi POS — cashier, admin, pharmacist
Route::middleware(['auth.apotek', 'role:admin,pharmacist,cashier'])
    ->get('/sistem/pos/riwayat', RiwayatTransaksi::class)
    ->name('pos.riwayat');

// Manajemen pengguna (admin) — khusus admin
Route::middleware(['auth.apotek', 'role:admin'])
    ->get('/sistem/admin/users', UserManagement::class)
    ->name('admin.users');

// Laporan — admin & pharmacist
Route::middleware(['auth.apotek', 'role:admin,pharmacist'])
    ->get('/sistem/laporan', LaporanPage::class)
    ->name('laporan.index');

// Dashboard bawaan Laravel (auth + verified email)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
