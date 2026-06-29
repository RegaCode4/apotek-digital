<?php

/** Feature test untuk route sistem: akses berdasarkan role (guest, cashier, pharmacist, admin). */

use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Mock DashboardService so dashboard routes don't crash on SQLite (DATE_FORMAT).
 */
function mockDashboardService(): void
{
    $mock = Mockery::mock(DashboardService::class);
    $mock->shouldReceive('getTodayRevenue')->andReturn(0.0)->byDefault();
    $mock->shouldReceive('getTodayTransactionCount')->andReturn(0)->byDefault();
    $mock->shouldReceive('getLowStockMedicines')->andReturn(collect())->byDefault();
    $mock->shouldReceive('getExpiringSoonMedicines')->andReturn(collect())->byDefault();
    $mock->shouldReceive('getSalesChartData')->andReturn(['labels' => [], 'data' => []])->byDefault();
    $mock->shouldReceive('getTopSellingMedicines')->andReturn(collect())->byDefault();
    app()->instance(DashboardService::class, $mock);
}

// Test: guest diarahkan ke login untuk semua route sistem
test('guests are redirected from protected sistem routes to login', function (string $routeName) {
    $response = $this->get(route($routeName));

    $response->assertRedirect(route('sistem.login'));
})->with([
    'dashboard' => ['sistem.dashboard'],
    'inventaris' => ['sistem.inventaris'],
    'laporan' => ['laporan.index'],
    'pos (kasir)' => ['pos.kasir'],
    'users' => ['sistem.users'],
    'medicines index' => ['inventaris.medicines.index'],
    'stok opname' => ['inventaris.stok-opname'],
    'mutasi stok' => ['inventaris.mutasi'],
]);

// Test: dashboard menampilkan nama dan role user yang login
test('dashboard shows authenticated user name and role', function () {
    mockDashboardService();

    $user = User::factory()->create([
        'name' => 'Budi Apoteker',
        'role' => 'pharmacist',
    ]);

    $response = $this->actingAs($user)->get(route('sistem.dashboard'));

    $response->assertOk();
    $response->assertSee('Budi Apoteker');
    $response->assertSee('pharmacist');
    $response->assertSee('Selamat datang');
});

// Test: admin bisa akses semua modul sistem
test('admin can access all sistem modules', function (string $routeName) {
    mockDashboardService();

    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get(route($routeName))->assertOk();
})->with([
    'dashboard' => ['sistem.dashboard'],
    'inventaris' => ['sistem.inventaris'],
    'laporan' => ['laporan.index'],
    'pos (kasir)' => ['pos.kasir'],
    'users' => ['sistem.users'],
    'medicines index' => ['inventaris.medicines.index'],
    'stok opname' => ['inventaris.stok-opname'],
    'mutasi stok' => ['inventaris.mutasi'],
]);

// Test: pharmacist bisa akses dashboard, inventaris, laporan, dan pos
test('pharmacist can access dashboard inventaris laporan and pos', function (string $routeName) {
    mockDashboardService();

    $pharmacist = User::factory()->create(['role' => 'pharmacist']);

    $this->actingAs($pharmacist)->get(route($routeName))->assertOk();
})->with([
    'dashboard' => ['sistem.dashboard'],
    'inventaris' => ['sistem.inventaris'],
    'laporan' => ['laporan.index'],
    'pos (kasir)' => ['pos.kasir'],
    'medicines index' => ['inventaris.medicines.index'],
    'stok opname' => ['inventaris.stok-opname'],
    'mutasi stok' => ['inventaris.mutasi'],
]);

// Test: pharmacist tidak bisa akses user management
test('pharmacist cannot access user management', function () {
    $pharmacist = User::factory()->create(['role' => 'pharmacist']);

    $this->actingAs($pharmacist)->get(route('sistem.users'))->assertForbidden();
});

// Test: cashier hanya bisa akses dashboard dan pos
test('cashier can access dashboard and pos only', function () {
    mockDashboardService();

    $cashier = User::factory()->create(['role' => 'cashier']);

    $this->actingAs($cashier)->get(route('sistem.dashboard'))->assertOk();
    $this->actingAs($cashier)->get(route('pos.kasir'))->assertOk();
});

// Test: cashier tidak bisa akses inventaris, laporan, atau user management
test('cashier cannot access inventaris laporan or user management', function (string $routeName) {
    $cashier = User::factory()->create(['role' => 'cashier']);

    $this->actingAs($cashier)->get(route($routeName))->assertForbidden();
})->with([
    'inventaris' => ['sistem.inventaris'],
    'laporan' => ['laporan.index'],
    'users' => ['sistem.users'],
    'medicines index' => ['inventaris.medicines.index'],
    'stok opname' => ['inventaris.stok-opname'],
    'mutasi stok' => ['inventaris.mutasi'],
]);
