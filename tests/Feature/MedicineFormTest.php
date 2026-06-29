<?php

/** Feature test untuk form obat (MedicineForm): create, edit, validasi, dan stock mutation. */

use App\Livewire\Inventaris\MedicineForm;
use App\Livewire\Inventaris\MedicineIndex;
use App\Models\Medicine;
use App\Models\StockMutation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

// Test: membuat obat baru dan mencatat stock mutation awal
test('medicine form can create a new medicine and record initial stock mutation', function () {
    $pharmacist = User::factory()->create(['role' => 'pharmacist']);

    Livewire::actingAs($pharmacist)
        ->test(MedicineForm::class)
        ->call('open')
        ->assertSet('show', true)
        ->set('name', 'Paracetamol 500mg')
        ->set('generic_name', 'Paracetamol')
        ->set('unit', 'tablet')
        ->set('price', '15000')
        ->set('stock', 50)
        ->set('min_stock', 10)
        ->call('save')
        ->assertSet('show', false)
        ->assertDispatched('medicine-saved');

    $medicine = Medicine::query()->where('name', 'Paracetamol 500mg')->first();

    expect($medicine)->not->toBeNull();
    expect($medicine->stock)->toBe(50);

    $mutation = StockMutation::query()->first();

    expect($mutation)
        ->medicine_id->toBe($medicine->id)
        ->type->toBe('in')
        ->quantity->toBe(50)
        ->created_by->toBe($pharmacist->id);
});

// Test: mengedit obat dan mencatat mutasi jika stok bertambah
test('medicine form can edit medicine and record stock increase mutation only', function () {
    $pharmacist = User::factory()->create(['role' => 'pharmacist']);

    $medicine = Medicine::factory()->create([
        'name' => 'Amoxicillin 500mg',
        'stock' => 20,
        'min_stock' => 10,
        'price' => 25000,
    ]);

    Livewire::actingAs($pharmacist)
        ->test(MedicineForm::class)
        ->call('open', $medicine->id)
        ->assertSet('name', 'Amoxicillin 500mg')
        ->set('stock', 35)
        ->call('save')
        ->assertDispatched('medicine-saved');

    expect($medicine->fresh()->stock)->toBe(35);

    $mutation = StockMutation::query()->first();

    expect($mutation)
        ->type->toBe('in')
        ->quantity->toBe(15)
        ->created_by->toBe($pharmacist->id);
});

// Test: tidak mencatat mutasi jika stok tidak bertambah saat edit
test('medicine form does not record stock mutation when stock is not increased on edit', function () {
    $pharmacist = User::factory()->create(['role' => 'pharmacist']);

    $medicine = Medicine::factory()->create([
        'stock' => 20,
        'price' => 25000,
    ]);

    Livewire::actingAs($pharmacist)
        ->test(MedicineForm::class)
        ->call('open', $medicine->id)
        ->set('stock', 15)
        ->call('save');

    expect(StockMutation::query()->count())->toBe(0);
    expect($medicine->fresh()->stock)->toBe(15);
});

// Test: validasi field required pada form obat
test('medicine form validates required fields', function () {
    $pharmacist = User::factory()->create(['role' => 'pharmacist']);

    Livewire::actingAs($pharmacist)
        ->test(MedicineForm::class)
        ->call('open')
        ->set('name', '')
        ->set('price', '-1')
        ->set('stock', -5)
        ->call('save')
        ->assertHasErrors(['name', 'price', 'stock']);
});

// Test: form mengirim event sukses setelah simpan
test('medicine form dispatches success message after save', function () {
    $pharmacist = User::factory()->create(['role' => 'pharmacist']);

    Livewire::actingAs($pharmacist)
        ->test(MedicineForm::class)
        ->call('open')
        ->set('name', 'Vitamin C 1000')
        ->set('unit', 'tablet')
        ->set('price', '30000')
        ->set('stock', 10)
        ->set('min_stock', 5)
        ->call('save')
        ->assertDispatched('medicine-saved', message: 'Obat berhasil ditambahkan.');
});

// Test: index menampilkan pesan sukses setelah event medicine-saved
test('medicine index shows success message after medicine saved event', function () {
    $pharmacist = User::factory()->create(['role' => 'pharmacist']);

    Livewire::actingAs($pharmacist)
        ->test(MedicineIndex::class)
        ->call('refreshMedicines', 'Obat berhasil ditambahkan.')
        ->assertSee('Obat berhasil ditambahkan.');
});

// Test: index merefresh daftar setelah event medicine-saved
test('medicine index refreshes list after medicine saved event', function () {
    $pharmacist = User::factory()->create(['role' => 'pharmacist']);

    Livewire::actingAs($pharmacist)
        ->test(MedicineForm::class)
        ->call('open')
        ->set('name', 'Vitamin C 1000')
        ->set('unit', 'tablet')
        ->set('price', '30000')
        ->set('stock', 10)
        ->set('min_stock', 5)
        ->call('save');

    Livewire::actingAs($pharmacist)
        ->test(MedicineIndex::class)
        ->assertSee('Vitamin C 1000');
});
