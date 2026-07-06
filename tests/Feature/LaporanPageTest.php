<?php

/** Feature test untuk halaman Laporan (LaporanPage): akses, tab navigasi, filter, dan export CSV. */

use App\Livewire\Laporan\LaporanPage;
use App\Models\Medicine;
use App\Models\Sale;
use App\Models\StockMutation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

// ─────────────────────────────────────────────────────────────────────────────
// Access Control
// ─────────────────────────────────────────────────────────────────────────────

// Test: guest diarahkan ke login
test('guests are redirected from laporan page', function () {
    $this->get(route('laporan.index'))
        ->assertRedirect(route('sistem.login'));
});

// Test: cashier tidak bisa akses halaman laporan
test('cashier cannot access laporan page', function () {
    $cashier = User::factory()->create(['role' => 'cashier']);

    $this->actingAs($cashier)
        ->get(route('laporan.index'))
        ->assertForbidden();
});

// Test: admin bisa akses halaman laporan
test('admin can access laporan page', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('laporan.index'))
        ->assertOk();
});

// Test: pharmacist bisa akses halaman laporan
test('pharmacist can access laporan page', function () {
    $pharmacist = User::factory()->create(['role' => 'pharmacist']);

    $this->actingAs($pharmacist)
        ->get(route('laporan.index'))
        ->assertOk();
});

// ─────────────────────────────────────────────────────────────────────────────
// Tab Navigation
// ─────────────────────────────────────────────────────────────────────────────

// Test: tab default adalah penjualan saat mount
test('default active tab is penjualan on mount', function () {
    $user = User::factory()->create(['role' => 'admin']);

    Livewire::actingAs($user)
        ->test(LaporanPage::class)
        ->assertSet('activeTab', 'penjualan');
});

// Test: setTab mengganti tab aktif dan mereset filter
test('setTab changes active tab and resets filters', function () {
    $user = User::factory()->create(['role' => 'admin']);

    Livewire::actingAs($user)
        ->test(LaporanPage::class)
        ->set('paymentMethod', 'cash')
        ->set('mutationType', 'in')
        ->call('setTab', 'stok')
        ->assertSet('activeTab', 'stok')
        ->assertSet('paymentMethod', '')
        ->assertSet('mutationType', '');
});

// Test: setTab mereset dateFrom dan dateTo
test('setTab resets dateFrom and dateTo', function () {
    $user = User::factory()->create(['role' => 'admin']);

    Livewire::actingAs($user)
        ->test(LaporanPage::class)
        ->set('dateFrom', '2024-01-01')
        ->set('dateTo', '2024-01-31')
        ->call('setTab', 'kedaluwarsa')
        ->assertSet('activeTab', 'kedaluwarsa')
        ->assertSet('dateFrom', '')
        ->assertSet('dateTo', '');
});

// Test: mount mengisi dateFrom dengan awal bulan berjalan
test('mount sets dateFrom to start of current month', function () {
    $user = User::factory()->create(['role' => 'admin']);

    Livewire::actingAs($user)
        ->test(LaporanPage::class)
        ->assertSet('dateFrom', now()->startOfMonth()->format('Y-m-d'))
        ->assertSet('dateTo', now()->endOfMonth()->format('Y-m-d'));
});

// ─────────────────────────────────────────────────────────────────────────────
// Tab 1 — Laporan Penjualan
// ─────────────────────────────────────────────────────────────────────────────

// Test: tab penjualan menampilkan data sales
test('tab penjualan shows sales data', function () {
    $cashier = User::factory()->create(['role' => 'cashier']);
    $admin = User::factory()->create(['role' => 'admin']);

    $sale = Sale::factory()->create([
        'buyer_name' => 'Pasien Uji',
        'cashier_id' => $cashier->id,
        'payment_method' => 'cash',
        'grand_total' => 75000,
        'sale_date' => now(),
    ]);

    Livewire::actingAs($admin)
        ->test(LaporanPage::class)
        ->assertSet('activeTab', 'penjualan')
        ->assertSee($sale->invoice_no)
        ->assertSee('Pasien Uji')
        ->assertSee($cashier->name);
});

// Test: filter penjualan berdasarkan metode pembayaran
test('tab penjualan filters by payment method', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $cashSale = Sale::factory()->create(['payment_method' => 'cash', 'sale_date' => now()]);
    $bpjsSale = Sale::factory()->create(['payment_method' => 'bpjs', 'sale_date' => now()]);

    Livewire::actingAs($admin)
        ->test(LaporanPage::class)
        ->set('paymentMethod', 'cash')
        ->assertSee($cashSale->invoice_no)
        ->assertDontSee($bpjsSale->invoice_no);
});

// Test: filter penjualan berdasarkan range tanggal
test('tab penjualan filters by date range', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $oldSale = Sale::factory()->create([
        'sale_date' => now()->subMonths(2),
        'buyer_name' => 'Pembeli Lama',
    ]);
    $newSale = Sale::factory()->create([
        'sale_date' => now(),
        'buyer_name' => 'Pembeli Baru',
    ]);

    Livewire::actingAs($admin)
        ->test(LaporanPage::class)
        ->set('dateFrom', now()->subDay()->toDateString())
        ->set('dateTo', now()->toDateString())
        ->assertSee('Pembeli Baru')
        ->assertDontSee('Pembeli Lama');
});

// Test: summary penjualan menghitung dan menjumlah dengan benar
test('sales summary counts and sums correctly', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    Sale::factory()->count(3)->create([
        'payment_method' => 'cash',
        'grand_total' => 50000,
        'sale_date' => now(),
    ]);

    Livewire::actingAs($admin)
        ->test(LaporanPage::class)
        ->assertViewHas('salesSummary', fn ($summary) => $summary['total_transaksi'] >= 3
            && $summary['total_pendapatan'] >= 150000
        );
});

// Test: export CSV laporan penjualan
test('penjualan csv export downloads a csv file', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    Sale::factory()->create(['sale_date' => now()]);

    $response = Livewire::actingAs($admin)
        ->test(LaporanPage::class)
        ->call('exportSalesCsv');

    $response->assertFileDownloaded();
    expect($response->effects['download']['name'] ?? '')->toStartWith('laporan-penjualan-');
    expect($response->effects['download']['name'] ?? '')->toEndWith('.csv');
});

// ─────────────────────────────────────────────────────────────────────────────
// Tab 2 — Laporan Stok & Mutasi
// ─────────────────────────────────────────────────────────────────────────────

// Test: tab stok menampilkan data mutasi
test('tab stok shows mutation data', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $medicine = Medicine::factory()->create(['name' => 'Obat Uji Mutasi']);

    StockMutation::query()->create([
        'medicine_id' => $medicine->id,
        'type' => 'in',
        'quantity' => 20,
        'created_by' => $admin->id,
        'notes' => 'Restock bulanan',
    ]);

    Livewire::actingAs($admin)
        ->test(LaporanPage::class)
        ->call('setTab', 'stok')
        ->assertSee('Obat Uji Mutasi')
        ->assertSee('Masuk')
        ->assertSee('Restock bulanan');
});

// Test: filter stok berdasarkan tipe mutasi
test('tab stok filters by mutation type', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $medicine = Medicine::factory()->create();

    StockMutation::query()->create([
        'medicine_id' => $medicine->id,
        'type' => 'in',
        'quantity' => 10,
        'created_by' => $admin->id,
        'notes' => 'Masuk saja',
    ]);
    StockMutation::query()->create([
        'medicine_id' => $medicine->id,
        'type' => 'out',
        'quantity' => -5,
        'created_by' => $admin->id,
        'notes' => 'Keluar saja',
    ]);

    Livewire::actingAs($admin)
        ->test(LaporanPage::class)
        ->call('setTab', 'stok')
        ->set('mutationType', 'in')
        ->assertSee('Masuk saja')
        ->assertDontSee('Keluar saja');
});

// Test: summary mutasi mengagregasi masuk/keluar dengan benar
test('mutations summary aggregates in and out correctly', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $medicine = Medicine::factory()->create();

    StockMutation::query()->create([
        'medicine_id' => $medicine->id,
        'type' => 'in',
        'quantity' => 30,
        'created_by' => $admin->id,
    ]);
    StockMutation::query()->create([
        'medicine_id' => $medicine->id,
        'type' => 'out',
        'quantity' => -12,
        'created_by' => $admin->id,
    ]);

    Livewire::actingAs($admin)
        ->test(LaporanPage::class)
        ->call('setTab', 'stok')
        ->assertViewHas('mutationsSummary', fn ($summary) => $summary['total_masuk'] == 30
            && $summary['total_keluar'] == 12
        );
});

// Test: export CSV laporan mutasi stok
test('mutations csv export downloads a csv file', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $medicine = Medicine::factory()->create();
    StockMutation::query()->create([
        'medicine_id' => $medicine->id,
        'type' => 'in',
        'quantity' => 5,
        'created_by' => $admin->id,
    ]);

    $response = Livewire::actingAs($admin)
        ->test(LaporanPage::class)
        ->call('setTab', 'stok')
        ->call('exportMutationsCsv');

    $response->assertFileDownloaded();
    expect($response->effects['download']['name'] ?? '')->toStartWith('laporan-stok-mutasi-');
});

// ─────────────────────────────────────────────────────────────────────────────
// Tab 3 — Laporan Pendapatan per Metode
// ─────────────────────────────────────────────────────────────────────────────

// Test: tab pendapatan menampilkan kartu metode pembayaran
test('tab pendapatan shows payment method cards', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    Sale::factory()->count(2)->create([
        'payment_method' => 'cash',
        'grand_total' => 100000,
        'sale_date' => now(),
    ]);
    Sale::factory()->create([
        'payment_method' => 'bpjs',
        'grand_total' => 50000,
        'sale_date' => now(),
    ]);

    Livewire::actingAs($admin)
        ->test(LaporanPage::class)
        ->call('setTab', 'pendapatan')
        ->assertSee('Cash')
        ->assertSee('BPJS');
});

// Test: summary pembayaran mengelompokkan total per metode
test('payment summary groups totals correctly by method', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    Sale::factory()->count(3)->create([
        'payment_method' => 'transfer',
        'grand_total' => 25000,
        'sale_date' => now(),
    ]);

    // Re-set filters after setTab clears them
    Livewire::actingAs($admin)
        ->test(LaporanPage::class)
        ->call('setTab', 'pendapatan')
        ->set('dateFrom', now()->startOfMonth()->toDateString())
        ->set('dateTo', now()->endOfMonth()->toDateString())
        ->assertViewHas('paymentSummary', fn ($summary) => $summary->has('transfer')
            && $summary->get('transfer')->jumlah_transaksi >= 3
        );
});

// Test: export CSV laporan pendapatan per metode
test('payment csv export downloads a csv file', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = Livewire::actingAs($admin)
        ->test(LaporanPage::class)
        ->call('setTab', 'pendapatan')
        ->call('exportPaymentCsv');

    $response->assertFileDownloaded();
    expect($response->effects['download']['name'] ?? '')->toStartWith('laporan-pendapatan-metode-');
});

// ─────────────────────────────────────────────────────────────────────────────
// Tab 4 — Laporan Kedaluwarsa & Low Stock
// ─────────────────────────────────────────────────────────────────────────────

// Test: tab kedaluwarsa menampilkan obat yang akan kedaluwarsa
test('tab kedaluwarsa shows expiring medicines', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $expiringMedicine = Medicine::factory()->create([
        'name' => 'Obat Mau Kadaluarsa',
        'expiry_date' => now()->addWeeks(3),
    ]);
    $safeMedicine = Medicine::factory()->create([
        'name' => 'Obat Masih Aman',
        'expiry_date' => now()->addYear(),
    ]);

    Livewire::actingAs($admin)
        ->test(LaporanPage::class)
        ->call('setTab', 'kedaluwarsa')
        ->assertSee('Obat Mau Kadaluarsa')
        ->assertDontSee('Obat Masih Aman');
});

// Test: tab kedaluwarsa menampilkan obat dengan stok rendah
test('tab kedaluwarsa shows low stock medicines', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $lowMedicine = Medicine::factory()->create([
        'name' => 'Obat Stok Habis',
        'stock' => 3,
        'min_stock' => 10,
        'expiry_date' => now()->addYears(2), // tidak expired, supaya tidak muncul di expiring
    ]);
    $safeMedicine = Medicine::factory()->create([
        'name' => 'Obat Stok Lebih Dari Cukup',
        'stock' => 999,
        'min_stock' => 10,
        'expiry_date' => now()->addYears(2),
    ]);

    Livewire::actingAs($admin)
        ->test(LaporanPage::class)
        ->call('setTab', 'kedaluwarsa')
        ->assertSee('Obat Stok Habis')
        ->assertDontSee('Obat Stok Lebih Dari Cukup');
});

// Test: badge merah untuk obat yang akan kedaluwarsa <= 30 hari
test('expiryBadgeClass returns red for medicines expiring within 30 days', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $component = Livewire::actingAs($admin)->test(LaporanPage::class);

    expect($component->instance()->expiryBadgeClass(now()->addDays(15)->toDateString()))
        ->toBe('bg-[var(--color-danger-soft)] text-[var(--color-danger)]');
});

// Test: badge amber untuk obat yang akan kedaluwarsa 30-90 hari
test('expiryBadgeClass returns amber for medicines expiring in 30 to 90 days', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $component = Livewire::actingAs($admin)->test(LaporanPage::class);

    expect($component->instance()->expiryBadgeClass(now()->addDays(60)->toDateString()))
        ->toBe('bg-[var(--color-warning-soft)] text-[var(--color-warning)]');
});

// Test: export CSV laporan obat kedaluwarsa
test('expiring csv export downloads a csv file', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = Livewire::actingAs($admin)
        ->test(LaporanPage::class)
        ->call('setTab', 'kedaluwarsa')
        ->call('exportExpiringCsv');

    $response->assertFileDownloaded();
    expect($response->effects['download']['name'] ?? '')->toStartWith('laporan-obat-kedaluwarsa-');
});

// Test: export CSV laporan low stock
test('low stock csv export downloads a csv file', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = Livewire::actingAs($admin)
        ->test(LaporanPage::class)
        ->call('setTab', 'kedaluwarsa')
        ->call('exportLowStockCsv');

    $response->assertFileDownloaded();
    expect($response->effects['download']['name'] ?? '')->toStartWith('laporan-low-stock-');
});

// ─────────────────────────────────────────────────────────────────────────────
// Helpers
// ─────────────────────────────────────────────────────────────────────────────

// Test: typeLabel mengembalikan label Bahasa Indonesia yang benar
test('typeLabel returns correct Indonesian labels', function (string $type, string $expected) {
    $admin = User::factory()->create(['role' => 'admin']);

    $component = Livewire::actingAs($admin)->test(LaporanPage::class);

    expect($component->instance()->typeLabel($type))->toBe($expected);
})->with([
    ['in', 'Masuk'],
    ['out', 'Keluar'],
    ['adjustment', 'Penyesuaian'],
    ['expired_return', 'Retur Kedaluwarsa'],
]);
