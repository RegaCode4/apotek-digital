<?php

use App\Livewire\Inventaris\MedicineIndex;
use App\Models\Category;
use App\Models\Medicine;
use App\Models\StockMutation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('guests are redirected from medicine index', function () {
    $this->get(route('inventaris.medicines.index'))
        ->assertRedirect(route('sistem.login'));
});

test('cashier cannot access medicine index', function () {
    $cashier = User::factory()->create(['role' => 'cashier']);

    $this->actingAs($cashier)
        ->get(route('inventaris.medicines.index'))
        ->assertForbidden();
});

test('pharmacist can access medicine index page', function () {
    $pharmacist = User::factory()->create(['role' => 'pharmacist']);

    $this->actingAs($pharmacist)
        ->get(route('inventaris.medicines.index'))
        ->assertOk()
        ->assertSee('Daftar Obat')
        ->assertSee('Tambah Obat');
});

test('medicine index lists medicines with category name from relation', function () {
    $pharmacist = User::factory()->create(['role' => 'pharmacist']);
    $category = Category::create(['name' => 'Analgesik', 'description' => null]);
    $medicine = Medicine::factory()->create([
        'name' => 'Paracetamol 500mg',
        'generic_name' => 'Paracetamol',
        'category_id' => $category->id,
    ]);

    Livewire::actingAs($pharmacist)
        ->test(MedicineIndex::class)
        ->assertSee('Paracetamol 500mg')
        ->assertSee('Paracetamol')
        ->assertSee('Analgesik');
});

test('medicine index search filters by name and generic name', function () {
    $pharmacist = User::factory()->create(['role' => 'pharmacist']);

    Medicine::factory()->create(['name' => 'Paracetamol 500mg', 'generic_name' => 'Paracetamol']);
    Medicine::factory()->create(['name' => 'Amoxicillin 500mg', 'generic_name' => 'Amoxicillin']);

    Livewire::actingAs($pharmacist)
        ->test(MedicineIndex::class)
        ->set('search', 'Amoxicillin')
        ->assertSee('Amoxicillin 500mg')
        ->assertDontSee('Paracetamol 500mg');
});

test('medicine index filters by category_id', function () {
    $pharmacist = User::factory()->create(['role' => 'pharmacist']);

    $catA = Category::create(['name' => 'Analgesik', 'description' => null]);
    $catB = Category::create(['name' => 'Vitamin', 'description' => null]);

    Medicine::factory()->create([
        'name' => 'Obat A',
        'category_id' => $catA->id,
        'requires_prescription' => true,
    ]);

    Medicine::factory()->create([
        'name' => 'Obat B',
        'category_id' => $catB->id,
        'requires_prescription' => false,
    ]);

    Livewire::actingAs($pharmacist)
        ->test(MedicineIndex::class)
        ->set('categoryId', (string) $catA->id)
        ->assertSee('Obat A')
        ->assertDontSee('Obat B')
        ->set('categoryId', '')
        ->set('requiresPrescription', '1')
        ->assertSee('Obat A')
        ->assertDontSee('Obat B');
});

test('medicine index paginates fifteen medicines per page', function () {
    $pharmacist = User::factory()->create(['role' => 'pharmacist']);

    Medicine::factory()->count(16)->create();

    Livewire::actingAs($pharmacist)
        ->test(MedicineIndex::class)
        ->assertViewHas('medicines', fn ($medicines) => $medicines->count() === 15);
});

test('medicine index shows low stock and expiring soon badges', function () {
    $pharmacist = User::factory()->create(['role' => 'pharmacist']);

    Medicine::factory()->create([
        'name' => 'Stok Rendah',
        'stock' => 3,
        'min_stock' => 10,
        'expiry_date' => now()->addMonths(6),
    ]);

    Medicine::factory()->create([
        'name' => 'Segera Kedaluwarsa',
        'stock' => 50,
        'min_stock' => 10,
        'expiry_date' => now()->addMonth(),
    ]);

    $this->actingAs($pharmacist)
        ->get(route('inventaris.medicines.index'))
        ->assertOk()
        ->assertSee('bg-red-100', false)
        ->assertSee('bg-amber-100', false);
});

test('medicine index shows delete confirmation modal with medicine name', function () {
    $pharmacist = User::factory()->create(['role' => 'pharmacist']);
    $medicine = Medicine::factory()->create(['name' => 'Obat Untuk Dihapus']);

    Livewire::actingAs($pharmacist)
        ->test(MedicineIndex::class)
        ->call('confirmDelete', $medicine->id)
        ->assertSet('showDeleteModal', true)
        ->assertSet('deleteMedicineName', 'Obat Untuk Dihapus')
        ->assertSee('Obat Untuk Dihapus');
});

test('medicine index deletes medicine and stock mutations when confirmed', function () {
    $pharmacist = User::factory()->create(['role' => 'pharmacist']);
    $medicine = Medicine::factory()->create(['name' => 'Obat Hapus']);

    StockMutation::query()->create([
        'medicine_id' => $medicine->id,
        'type' => 'in',
        'quantity' => 10,
        'created_by' => $pharmacist->id,
    ]);

    Livewire::actingAs($pharmacist)
        ->test(MedicineIndex::class)
        ->call('confirmDelete', $medicine->id)
        ->call('deleteConfirmed')
        ->assertSet('showDeleteModal', false)
        ->assertSet('successMessage', 'Obat berhasil dihapus.')
        ->assertDontSee('Obat Hapus');

    expect(Medicine::query()->find($medicine->id))->toBeNull();
    expect(StockMutation::query()->count())->toBe(0);
});

test('medicine index prevents delete when medicine has sale items', function () {
    $pharmacist = User::factory()->create(['role' => 'pharmacist']);
    $medicine = Medicine::factory()->create(['name' => 'Obat Terjual']);

    $cashier = User::factory()->create(['role' => 'cashier']);
    $sale = DB::table('sales')->insertGetId([
        'invoice_no' => 'INV-TEST-001',
        'buyer_name' => 'Test Buyer',
        'cashier_id' => $cashier->id,
        'payment_method' => 'cash',
        'subtotal' => 10000,
        'discount_amount' => 0,
        'tax_amount' => 0,
        'grand_total' => 10000,
        'sale_date' => now(),
    ]);

    DB::table('sale_items')->insert([
        'sale_id' => $sale,
        'medicine_id' => $medicine->id,
        'quantity' => 1,
        'unit_price' => 10000,
        'subtotal' => 10000,
    ]);

    Livewire::actingAs($pharmacist)
        ->test(MedicineIndex::class)
        ->call('confirmDelete', $medicine->id)
        ->call('deleteConfirmed')
        ->assertSet('errorMessage', 'Obat tidak bisa dihapus karena sudah pernah dijual. Nonaktifkan saja.')
        ->assertSee('Obat tidak bisa dihapus karena sudah pernah dijual. Nonaktifkan saja.');

    expect(Medicine::query()->find($medicine->id))->not->toBeNull();
});
