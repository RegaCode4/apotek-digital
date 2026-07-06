<?php

/** Feature test untuk Stok Opname: akses, adjustment stok, reason, dan timestamp. */

use App\Livewire\Inventaris\StokOpname;
use App\Models\Medicine;
use App\Models\StockMutation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

// Test: guest diarahkan ke login
test('guests are redirected from stok opname page', function () {
    $this->get(route('inventaris.stok-opname'))
        ->assertRedirect(route('sistem.login'));
});

// Test: cashier tidak bisa akses stok opname
test('cashier cannot access stok opname page', function () {
    $cashier = User::factory()->create(['role' => 'cashier']);

    $this->actingAs($cashier)
        ->get(route('inventaris.stok-opname'))
        ->assertForbidden();
});

// Test: pharmacist bisa akses halaman stok opname
test('pharmacist can access stok opname page', function () {
    $pharmacist = User::factory()->create(['role' => 'pharmacist']);
    Medicine::factory()->create();

    $this->actingAs($pharmacist)
        ->get(route('inventaris.stok-opname'))
        ->assertOk()
        ->assertSee('Stok Opname')
        ->assertSee('Simpan & Selesaikan SO', false);
});

// Test: menampilkan stok sistem dan selisih
test('stok opname shows medicines with system stock and difference', function () {
    $pharmacist = User::factory()->create(['role' => 'pharmacist']);
    $medicine = Medicine::factory()->create([
        'name' => 'Paracetamol Opname',
        'stock' => 20,
    ]);

    Livewire::actingAs($pharmacist)
        ->test(StokOpname::class)
        ->assertSet("physicalStocks.{$medicine->id}", null)
        ->set("physicalStocks.{$medicine->id}", 15)
        ->assertSee('Paracetamol Opname')
        ->assertSee('-5', false);
});

// Test: reason wajib diisi sebelum menyimpan adjustment
test('stok opname requires reason before saving', function () {
    $pharmacist = User::factory()->create(['role' => 'pharmacist']);
    $medicine = Medicine::factory()->create(['stock' => 10]);

    Livewire::actingAs($pharmacist)
        ->test(StokOpname::class)
        ->set("physicalStocks.{$medicine->id}", 12)
        ->call('saveAllAdjustments')
        ->assertHasErrors(['reason']);
});

// Test: menyimpan adjustment dan mencatat stock mutation
test('stok opname saves adjustments and records stock mutations', function () {
    $pharmacist = User::factory()->create(['role' => 'pharmacist']);

    $medicineA = Medicine::factory()->create(['name' => 'Obat A', 'stock' => 10]);
    $medicineB = Medicine::factory()->create(['name' => 'Obat B', 'stock' => 25]);

    Livewire::actingAs($pharmacist)
        ->test(StokOpname::class)
        ->set("physicalStocks.{$medicineA->id}", 15)
        ->set("physicalStocks.{$medicineB->id}", 25)
        ->set('reason', 'Stok opname rutin bulan ini')
        ->call('saveAllAdjustments')
        ->assertHasNoErrors()
        ->assertSet('successMessage', 'SO Selesai! 1 penyesuaian stok dicatat di ledger.');

    expect($medicineA->fresh()->stock)->toBe(15);
    expect($medicineB->fresh()->stock)->toBe(25);

    $mutation = StockMutation::query()->first();

    expect($mutation)
        ->medicine_id->toBe($medicineA->id)
        ->type->toBe('adjustment')
        ->quantity->toBe(5)
        ->notes->toBe('SO: Penyesuaian SO | Ref: Stok opname rutin bulan ini')
        ->created_by->toBe($pharmacist->id);

    expect(StockMutation::query()->count())->toBe(1);
});

// Test: timestamp stok opname terakhir ditampilkan setelah simpan
test('stok opname shows last opname timestamp after saving', function () {
    $pharmacist = User::factory()->create(['role' => 'pharmacist']);
    $medicine = Medicine::factory()->create(['stock' => 8]);

    Livewire::actingAs($pharmacist)
        ->test(StokOpname::class)
        ->set("physicalStocks.{$medicine->id}", 10)
        ->set('reason', 'Koreksi hasil hitung fisik')
        ->call('saveAllAdjustments');

    Livewire::actingAs($pharmacist)
        ->test(StokOpname::class)
        ->assertSee('Stok opname terakhir:')
        ->assertDontSee('Belum pernah dilakukan');
});
