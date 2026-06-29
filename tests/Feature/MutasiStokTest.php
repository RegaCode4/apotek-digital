<?php

/** Feature test untuk halaman Mutasi Stok: akses, filter, pagination, dan export CSV. */

use App\Livewire\Inventaris\MutasiStok;
use App\Models\Medicine;
use App\Models\StockMutation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createStockMutation(array $attributes = []): StockMutation
{
    $user = User::factory()->create();
    $medicine = Medicine::factory()->create();

    return StockMutation::query()->create(array_merge([
        'medicine_id' => $medicine->id,
        'type' => 'in',
        'quantity' => 10,
        'notes' => 'Stok masuk awal',
        'created_by' => $user->id,
        'created_at' => now(),
    ], $attributes));
}

// Test: guest diarahkan ke login
test('guests are redirected from mutasi stok page', function () {
    $this->get(route('inventaris.mutasi'))
        ->assertRedirect(route('sistem.login'));
});

// Test: cashier tidak bisa akses mutasi stok
test('cashier cannot access mutasi stok page', function () {
    $cashier = User::factory()->create(['role' => 'cashier']);

    $this->actingAs($cashier)
        ->get(route('inventaris.mutasi'))
        ->assertForbidden();
});

// Test: pharmacist bisa melihat tabel mutasi stok
test('pharmacist can view mutasi stok table', function () {
    $pharmacist = User::factory()->create(['role' => 'pharmacist']);
    $mutation = createStockMutation([
        'type' => 'adjustment',
        'quantity' => -3,
        'notes' => 'Koreksi stok opname',
        'created_by' => $pharmacist->id,
    ]);

    Livewire::actingAs($pharmacist)
        ->test(MutasiStok::class)
        ->assertSee($mutation->medicine->name)
        ->assertSee('Penyesuaian')
        ->assertSee('Koreksi stok opname')
        ->assertSee($pharmacist->name);
});

// Test: filter mutasi stok berdasarkan tipe, search, dan range tanggal
test('mutasi stok filters by type search and date range', function () {
    $pharmacist = User::factory()->create(['role' => 'pharmacist']);

    $medicineA = Medicine::factory()->create(['name' => 'Paracetamol Mutasi']);
    $medicineB = Medicine::factory()->create(['name' => 'Amoxicillin Mutasi']);

    $mutationA = StockMutation::query()->create([
        'medicine_id' => $medicineA->id,
        'type' => 'in',
        'quantity' => 5,
        'notes' => 'Masuk A',
        'created_by' => $pharmacist->id,
    ]);
    $mutationA->created_at = now()->subDays(5);
    $mutationA->save();

    $mutationB = StockMutation::query()->create([
        'medicine_id' => $medicineB->id,
        'type' => 'out',
        'quantity' => -2,
        'notes' => 'Keluar B',
        'created_by' => $pharmacist->id,
    ]);
    $mutationB->created_at = now();
    $mutationB->save();

    Livewire::actingAs($pharmacist)
        ->test(MutasiStok::class)
        ->set('type', 'out')
        ->assertSee('Amoxicillin Mutasi')
        ->assertDontSee('Paracetamol Mutasi')
        ->set('type', '')
        ->set('search', 'Paracetamol')
        ->assertSee('Paracetamol Mutasi')
        ->assertDontSee('Amoxicillin Mutasi')
        ->set('search', '')
        ->set('dateFrom', now()->subDay()->toDateString())
        ->set('dateTo', now()->toDateString())
        ->assertSee('Amoxicillin Mutasi')
        ->assertDontSee('Paracetamol Mutasi');
});

// Test: pagination 20 item per halaman, diurutkan terbaru
test('mutasi stok paginates twenty items ordered by latest', function () {
    $pharmacist = User::factory()->create(['role' => 'pharmacist']);
    $medicine = Medicine::factory()->create();

    foreach (range(1, 21) as $index) {
        $mutation = StockMutation::query()->create([
            'medicine_id' => $medicine->id,
            'type' => 'in',
            'quantity' => $index,
            'notes' => "Mutasi {$index}",
            'created_by' => $pharmacist->id,
        ]);
        $mutation->created_at = now()->subMinutes(21 - $index);
        $mutation->save();
    }

    Livewire::actingAs($pharmacist)
        ->test(MutasiStok::class)
        ->assertViewHas('mutations', fn ($mutations) => $mutations->count() === 20
            && $mutations->first()->notes === 'Mutasi 21'
            && $mutations->last()->notes === 'Mutasi 2');
});

// Test: export CSV mutasi stok
test('mutasi stok can export csv', function () {
    $pharmacist = User::factory()->create(['role' => 'pharmacist']);

    createStockMutation([
        'type' => 'in',
        'notes' => 'Export test',
        'created_by' => $pharmacist->id,
    ]);

    $response = Livewire::actingAs($pharmacist)
        ->test(MutasiStok::class)
        ->call('exportCsv');

    $response->assertFileDownloaded();
    expect($response->effects['download']['name'] ?? '')->toEndWith('.csv');
});
