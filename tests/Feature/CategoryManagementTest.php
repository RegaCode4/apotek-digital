<?php

/** Feature test untuk manajemen kategori (CategoryManagement): akses, CRUD, dan search. */

use App\Livewire\Inventaris\CategoryManagement;
use App\Models\Category;
use App\Models\Medicine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

// ── Route access ──────────────────────────────────────────────────────────────

// Test: admin bisa akses halaman kategori
test('admin can access category management page', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('inventaris.kategori'))
        ->assertOk();
});

// Test: pharmacist bisa akses halaman kategori
test('pharmacist can access category management page', function () {
    $pharmacist = User::factory()->create(['role' => 'pharmacist']);

    $this->actingAs($pharmacist)
        ->get(route('inventaris.kategori'))
        ->assertOk();
});

// Test: cashier tidak bisa akses halaman kategori
test('cashier cannot access category management page', function () {
    $cashier = User::factory()->create(['role' => 'cashier']);

    $this->actingAs($cashier)
        ->get(route('inventaris.kategori'))
        ->assertForbidden();
});

// ── Create ────────────────────────────────────────────────────────────────────

// Test: membuat kategori baru
test('can create a new category', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    Livewire::actingAs($admin)
        ->test(CategoryManagement::class)
        ->call('openCreateModal')
        ->assertSet('showModal', true)
        ->set('formName', 'Sistem Imun')
        ->set('formDescription', 'Obat untuk sistem kekebalan tubuh')
        ->call('save')
        ->assertSet('showModal', false)
        ->assertDispatched('notify');

    expect(Category::where('name', 'Sistem Imun')->exists())->toBeTrue();
});

// Test: validasi nama kategori wajib diisi
test('category name is required', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    Livewire::actingAs($admin)
        ->test(CategoryManagement::class)
        ->call('openCreateModal')
        ->set('formName', '')
        ->call('save')
        ->assertHasErrors(['formName']);
});

// Test: nama kategori harus unik saat create
test('category name must be unique on create', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    Category::create(['name' => 'Sistem Imun', 'description' => null]);

    Livewire::actingAs($admin)
        ->test(CategoryManagement::class)
        ->call('openCreateModal')
        ->set('formName', 'Sistem Imun')
        ->call('save')
        ->assertHasErrors(['formName']);
});

// ── Edit ──────────────────────────────────────────────────────────────────────

// Test: mengedit kategori yang sudah ada
test('can edit an existing category', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $category = Category::create(['name' => 'Lama', 'description' => null]);

    Livewire::actingAs($admin)
        ->test(CategoryManagement::class)
        ->call('openEditModal', $category->id)
        ->assertSet('formName', 'Lama')
        ->set('formName', 'Baru')
        ->set('formDescription', 'Deskripsi baru')
        ->call('save')
        ->assertSet('showModal', false);

    expect($category->fresh()->name)->toBe('Baru');
});

// Test: validasi nama unik saat edit, tapi boleh pakai nama yang sama
test('category name must be unique on edit but allows keeping same name', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $category = Category::create(['name' => 'Sistem Imun', 'description' => null]);
    Category::create(['name' => 'Lain', 'description' => null]);

    // Keeping the same name — should pass
    Livewire::actingAs($admin)
        ->test(CategoryManagement::class)
        ->call('openEditModal', $category->id)
        ->set('formName', 'Sistem Imun')
        ->call('save')
        ->assertHasNoErrors();
});

// ── Delete ────────────────────────────────────────────────────────────────────

// Test: menghapus kategori yang tidak memiliki obat
test('can delete a category with no medicines', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $category = Category::create(['name' => 'Hapus Saya', 'description' => null]);

    Livewire::actingAs($admin)
        ->test(CategoryManagement::class)
        ->call('confirmDelete', $category->id)
        ->assertSet('showDeleteConfirm', true)
        ->call('delete')
        ->assertSet('showDeleteConfirm', false)
        ->assertDispatched('notify');

    expect(Category::find($category->id))->toBeNull();
});

// Test: tidak bisa menghapus kategori yang masih dipakai obat
test('cannot delete a category that is used by medicines', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $category = Category::create(['name' => 'Dipakai', 'description' => null]);

    Medicine::factory()->create(['category_id' => $category->id]);

    Livewire::actingAs($admin)
        ->test(CategoryManagement::class)
        ->call('confirmDelete', $category->id)
        ->call('delete')
        ->assertSet('showDeleteConfirm', false)
        ->assertDispatched('notify');

    // Category must still exist
    expect(Category::find($category->id))->not->toBeNull();
});

// ── Search ────────────────────────────────────────────────────────────────────

// Test: mencari kategori berdasarkan nama
test('can search categories by name', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    Category::create(['name' => 'Sistem Kardiovaskular', 'description' => null]);
    Category::create(['name' => 'Sistem Pernapasan', 'description' => null]);

    Livewire::actingAs($admin)
        ->test(CategoryManagement::class)
        ->set('search', 'Kardio')
        ->assertSee('Sistem Kardiovaskular')
        ->assertDontSee('Sistem Pernapasan');
});
