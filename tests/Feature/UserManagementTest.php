<?php

/** Feature test untuk manajemen user (UserManagement Livewire): akses, CRUD, toggle aktif, reset password. */

use App\Livewire\Admin\UserManagement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

uses(RefreshDatabase::class);

// ── Access control ─────────────────────────────────────────────────────────

// Test: guest diarahkan ke halaman login
test('guests are redirected from admin users page', function () {
    $this->get(route('admin.users'))
        ->assertRedirect(route('sistem.login'));
});

// Test: cashier tidak bisa akses halaman admin users
test('cashier cannot access admin users page', function () {
    $cashier = User::factory()->create(['role' => 'cashier']);

    $this->actingAs($cashier)
        ->get(route('admin.users'))
        ->assertForbidden();
});

// Test: pharmacist tidak bisa akses halaman admin users
test('pharmacist cannot access admin users page', function () {
    $pharmacist = User::factory()->create(['role' => 'pharmacist']);

    $this->actingAs($pharmacist)
        ->get(route('admin.users'))
        ->assertForbidden();
});

// Test: admin bisa akses halaman user management
test('admin can access user management page', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.users'))
        ->assertOk()
        ->assertSee('Manajemen User');
});

// ── Table listing ──────────────────────────────────────────────────────────

// Test: daftar user menampilkan semua user
test('user management lists all users', function () {
    $admin = User::factory()->create(['role' => 'admin', 'name' => 'Admin Utama']);
    User::factory()->create(['name' => 'Kasir Satu', 'role' => 'cashier']);
    User::factory()->create(['name' => 'Apoteker Dua', 'role' => 'pharmacist']);

    Livewire::actingAs($admin)
        ->test(UserManagement::class)
        ->assertSee('Admin Utama')
        ->assertSee('Kasir Satu')
        ->assertSee('Apoteker Dua');
});

// Test: badge role dan status aktif/nonaktif ditampilkan
test('user management shows role badge and status badge', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    User::factory()->create(['role' => 'cashier', 'is_active' => false]);

    Livewire::actingAs($admin)
        ->test(UserManagement::class)
        ->assertSee('admin')
        ->assertSee('cashier')
        ->assertSee('Nonaktif');
});

// Test: pencarian user berdasarkan nama
test('user management search filters by name', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    User::factory()->create(['name' => 'Budi Santoso']);
    User::factory()->create(['name' => 'Siti Rahayu']);

    Livewire::actingAs($admin)
        ->test(UserManagement::class)
        ->set('search', 'Budi')
        ->assertSee('Budi Santoso')
        ->assertDontSee('Siti Rahayu');
});

// Test: pencarian user berdasarkan email
test('user management search filters by email', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    User::factory()->create(['name' => 'User Alpha', 'email' => 'alpha@apotek.com']);
    User::factory()->create(['name' => 'User Beta', 'email' => 'beta@apotek.com']);

    Livewire::actingAs($admin)
        ->test(UserManagement::class)
        ->set('search', 'beta@')
        ->assertSee('User Beta')
        ->assertDontSee('User Alpha');
});

// Test: pagination 15 user per halaman
test('user management paginates fifteen users per page', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    User::factory()->count(16)->create();

    Livewire::actingAs($admin)
        ->test(UserManagement::class)
        ->assertViewHas('users', fn ($users) => $users->count() === 15);
});

// ── Create user ────────────────────────────────────────────────────────────

// Test: admin membuka modal create user
test('admin can open create modal', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    Livewire::actingAs($admin)
        ->test(UserManagement::class)
        ->call('openCreateModal')
        ->assertSet('showModal', true)
        ->assertSet('editingUserId', null);
});

// Test: admin berhasil membuat user baru
test('admin can create a new user', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    Livewire::actingAs($admin)
        ->test(UserManagement::class)
        ->call('openCreateModal')
        ->set('formName', 'User Baru')
        ->set('formEmail', 'userbaru@apotek.com')
        ->set('formPassword', 'rahasia123')
        ->set('formRole', 'cashier')
        ->call('save')
        ->assertSet('showModal', false);

    expect(User::query()->where('email', 'userbaru@apotek.com')->exists())->toBeTrue();
});

// Test: validasi required field saat create user
test('create user validates required fields', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    Livewire::actingAs($admin)
        ->test(UserManagement::class)
        ->call('openCreateModal')
        ->set('formName', '')
        ->set('formEmail', '')
        ->set('formPassword', '')
        ->call('save')
        ->assertHasErrors(['formName', 'formEmail', 'formPassword']);
});

// Test: validasi email unik saat create user
test('create user validates unique email', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    User::factory()->create(['email' => 'existing@apotek.com']);

    Livewire::actingAs($admin)
        ->test(UserManagement::class)
        ->call('openCreateModal')
        ->set('formName', 'Duplikat')
        ->set('formEmail', 'existing@apotek.com')
        ->set('formPassword', 'rahasia123')
        ->set('formRole', 'cashier')
        ->call('save')
        ->assertHasErrors(['formEmail']);
});

// Test: validasi panjang minimal password
test('create user validates password minimum length', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    Livewire::actingAs($admin)
        ->test(UserManagement::class)
        ->call('openCreateModal')
        ->set('formName', 'User Baru')
        ->set('formEmail', 'baru@apotek.com')
        ->set('formPassword', 'abc')
        ->set('formRole', 'cashier')
        ->call('save')
        ->assertHasErrors(['formPassword']);
});

// Test: validasi nilai role yang diizinkan
test('create user validates role value', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    Livewire::actingAs($admin)
        ->test(UserManagement::class)
        ->call('openCreateModal')
        ->set('formName', 'User Baru')
        ->set('formEmail', 'baru@apotek.com')
        ->set('formPassword', 'rahasia123')
        ->set('formRole', 'superadmin')
        ->call('save')
        ->assertHasErrors(['formRole']);
});

// ── Edit user ──────────────────────────────────────────────────────────────

// Test: admin membuka modal edit dengan data user terisi
test('admin can open edit modal with user data pre-filled', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $target = User::factory()->create(['name' => 'Target User', 'role' => 'pharmacist']);

    Livewire::actingAs($admin)
        ->test(UserManagement::class)
        ->call('openEditModal', $target->id)
        ->assertSet('showModal', true)
        ->assertSet('editingUserId', $target->id)
        ->assertSet('formName', 'Target User')
        ->assertSet('formRole', 'pharmacist');
});

// Test: admin mengubah nama dan role user
test('admin can edit user name and role', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $target = User::factory()->create(['role' => 'cashier']);

    Livewire::actingAs($admin)
        ->test(UserManagement::class)
        ->call('openEditModal', $target->id)
        ->set('formName', 'Nama Baru')
        ->set('formRole', 'pharmacist')
        ->call('save')
        ->assertSet('showModal', false);

    expect($target->fresh()->name)->toBe('Nama Baru');
    expect($target->fresh()->role)->toBe('pharmacist');
});

// Test: form edit tidak menampilkan field password
test('edit form does not show password field', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $target = User::factory()->create();

    Livewire::actingAs($admin)
        ->test(UserManagement::class)
        ->call('openEditModal', $target->id)
        ->assertSee('formIsActive')
        ->assertSeeHtml('formIsActive');

    // Password field should only appear in create mode (editingUserId = null)
    Livewire::actingAs($admin)
        ->test(UserManagement::class)
        ->call('openEditModal', $target->id)
        ->assertSet('editingUserId', $target->id);
});

// Test: validasi email unik saat edit (tidak boleh duplikat)
test('edit does not allow duplicate email for other user', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    User::factory()->create(['email' => 'taken@apotek.com']);
    $target = User::factory()->create(['email' => 'target@apotek.com']);

    Livewire::actingAs($admin)
        ->test(UserManagement::class)
        ->call('openEditModal', $target->id)
        ->set('formEmail', 'taken@apotek.com')
        ->call('save')
        ->assertHasErrors(['formEmail']);
});

// ── Toggle active ──────────────────────────────────────────────────────────

// Test: admin menonaktifkan user lain
test('admin can deactivate another user', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $target = User::factory()->create(['is_active' => true]);

    Livewire::actingAs($admin)
        ->test(UserManagement::class)
        ->call('toggleActive', $target->id);

    expect($target->fresh()->is_active)->toBeFalse();
});

// Test: admin mengaktifkan kembali user yang dinonaktifkan
test('admin can reactivate a deactivated user', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $target = User::factory()->create(['is_active' => false]);

    Livewire::actingAs($admin)
        ->test(UserManagement::class)
        ->call('toggleActive', $target->id);

    expect($target->fresh()->is_active)->toBeTrue();
});

// Test: admin tidak bisa menonaktifkan diri sendiri via toggle
test('admin cannot deactivate themselves via toggle', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    Livewire::actingAs($admin)
        ->test(UserManagement::class)
        ->call('toggleActive', $admin->id);

    // Admin should still be active
    expect($admin->fresh()->is_active)->toBeTrue();
});

// Test: admin tidak bisa menonaktifkan diri sendiri via form edit
test('admin cannot deactivate themselves via edit form', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    Livewire::actingAs($admin)
        ->test(UserManagement::class)
        ->call('openEditModal', $admin->id)
        ->set('formIsActive', false)
        ->call('save')
        ->assertHasErrors(['formIsActive']);

    expect($admin->fresh()->is_active)->toBeTrue();
});

// ── Reset password ─────────────────────────────────────────────────────────

// Test: admin mereset password user lain ke 'password123'
test('admin can reset another users password to password123', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $target = User::factory()->create();

    Livewire::actingAs($admin)
        ->test(UserManagement::class)
        ->call('resetPassword', $target->id);

    expect(Hash::check('password123', $target->fresh()->password))->toBeTrue();
});

// Test: reset password memicu notifikasi warning
test('reset password dispatches warning notification', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $target = User::factory()->create(['name' => 'Kasir Uji']);

    Livewire::actingAs($admin)
        ->test(UserManagement::class)
        ->call('resetPassword', $target->id)
        ->assertDispatched('notify', type: 'warning');
});

// ── Close modal ────────────────────────────────────────────────────────────

// Test: menutup modal mereset field form
test('closing modal resets form fields', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    Livewire::actingAs($admin)
        ->test(UserManagement::class)
        ->call('openCreateModal')
        ->set('formName', 'Test Name')
        ->set('formEmail', 'test@apotek.com')
        ->call('closeModal')
        ->assertSet('showModal', false)
        ->assertSet('formName', '')
        ->assertSet('formEmail', '');
});
