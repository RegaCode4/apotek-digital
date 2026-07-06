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
use App\Http\Controllers\Inventaris\CetakStokOpnameController;
use App\Livewire\Laporan\LaporanPage;
use App\Livewire\Pos\KasirPage;
use App\Livewire\Pos\RiwayatTransaksi;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

// Redirect root ke halaman login
/**
 * Redirect halaman utama root ke halaman login sistem.
 */
Route::redirect('/', '/sistem/login')->name('home');

/**
 * Grup rute untuk seluruh sistem aplikasi (login, dashboard, dan lainnya).
 */
Route::prefix('sistem')->name('sistem.')->group(function () {
    
    /**
     * Menampilkan halaman login.
     */
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    
    /**
     * Memproses permintaan login pengguna.
     */
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    
    /**
     * Memproses proses logout pengguna.
     */
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    /**
     * Grup rute yang membutuhkan autentikasi (harus login).
     */
    Route::middleware(['auth.apotek'])->group(function () {
        
        /**
         * Menampilkan dashboard utama.
         * Menyiapkan data 5 penjualan terakhir dan ringkasan pembayaran hari ini.
         */
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

    /**
     * Grup rute untuk modul Inventaris.
     * Hanya dapat diakses oleh role: admin, pharmacist.
     */
    Route::middleware(['auth.apotek', 'role:admin,pharmacist'])->group(function () {
        Route::get('/inventaris', fn () => view('sistem.inventaris'))->name('inventaris');
    });

    /**
     * Grup rute untuk modul Point of Sale (POS).
     * Dapat diakses oleh role: cashier, admin, pharmacist.
     */
    Route::middleware(['auth.apotek', 'role:cashier,admin,pharmacist'])->group(function () {
        Route::get('/pos', fn () => view('sistem.pos'))->name('pos');
    });

    /**
     * Grup rute untuk modul Manajemen Pengguna.
     * Hanya dapat diakses oleh role: admin.
     */
    Route::middleware(['auth.apotek', 'role:admin'])->group(function () {
        Route::get('/users', fn () => view('sistem.users'))->name('users');
    });
});

/**
 * Rute untuk menampilkan daftar obat.
 * Dapat diakses oleh role: admin, pharmacist.
 */
Route::middleware(['auth.apotek', 'role:admin,pharmacist'])
    ->get('/sistem/inventaris/obat', MedicineIndex::class)
    ->name('inventaris.medicines.index');

/**
 * Rute untuk melakukan stok opname.
 * Dapat diakses oleh role: admin, pharmacist.
 */
Route::middleware(['auth.apotek', 'role:admin,pharmacist'])
    ->get('/sistem/inventaris/stok-opname', StokOpname::class)
    ->name('inventaris.stok-opname');

/**
 * Rute untuk mencetak laporan stok opname berdasarkan timestamp.
 * Dapat diakses oleh role: admin, pharmacist.
 */
Route::middleware(['auth.apotek', 'role:admin,pharmacist'])
    ->get('/sistem/inventaris/stok-opname/cetak/{timestamp}', CetakStokOpnameController::class)
    ->name('inventaris.stok-opname.cetak');

/**
 * Rute untuk melakukan mutasi stok obat.
 * Dapat diakses oleh role: admin, pharmacist.
 */
Route::middleware(['auth.apotek', 'role:admin,pharmacist'])
    ->get('/sistem/inventaris/mutasi', MutasiStok::class)
    ->name('inventaris.mutasi');

/**
 * Rute untuk mengelola kategori obat.
 * Dapat diakses oleh role: admin, pharmacist.
 */
Route::middleware(['auth.apotek', 'role:admin,pharmacist'])
    ->get('/sistem/inventaris/kategori', CategoryManagement::class)
    ->name('inventaris.kategori');

/**
 * Rute halaman kasir (POS).
 * Dapat diakses oleh role: admin, pharmacist, cashier.
 */
Route::middleware(['auth.apotek', 'role:admin,pharmacist,cashier'])
    ->get('/sistem/pos', KasirPage::class)
    ->name('pos.kasir');

/**
 * Rute untuk mencetak struk transaksi.
 * Dapat diakses oleh semua pengguna yang sudah login (auth.apotek).
 */
Route::middleware(['auth.apotek'])
    ->get('/sistem/pos/struk/{sale}', StrukController::class)
    ->name('pos.struk');

/**
 * Rute untuk melihat riwayat transaksi POS.
 * Dapat diakses oleh role: admin, pharmacist, cashier.
 */
Route::middleware(['auth.apotek', 'role:admin,pharmacist,cashier'])
    ->get('/sistem/pos/riwayat', RiwayatTransaksi::class)
    ->name('pos.riwayat');

/**
 * Rute untuk mengelola data pengguna (User Management).
 * Hanya dapat diakses oleh role: admin.
 */
Route::middleware(['auth.apotek', 'role:admin'])
    ->get('/sistem/admin/users', UserManagement::class)
    ->name('admin.users');

/**
 * Rute untuk melihat laporan transaksi atau inventaris.
 * Dapat diakses oleh role: admin, pharmacist.
 */
Route::middleware(['auth.apotek', 'role:admin,pharmacist'])
    ->get('/sistem/laporan', LaporanPage::class)
    ->name('laporan.index');

/**
 * Rute bawaan Laravel untuk menampilkan dashboard (membutuhkan autentikasi & verifikasi email).
 */
Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
