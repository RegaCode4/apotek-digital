<?php

use App\Livewire\Admin\UserManagement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

uses(RefreshDatabase::class);

// ── Access control ─────────────────────────────────────────────────────────

test('guests are redirected from admin users page', function () {
    $this->get(route('admin.users'))
        ->assertRedirect(route('sistem.login'));
});

test('cashier cannot access admin users page', function () {
    $cashier = User::factory()->create(['role' => 'cashier']);

    $this->actingAs($cashier)
        ->get(route('admin.users'))
        ->assertForbidden();
});

test('pharmacist cannot access admin users page', function () {
    $pharmacist = User::factory()->create(['role' => 'pharmacist']);

    $this->actingAs($pharmacist)
        ->get(route('admin.users'))
        ->assertForbidden();
});

test('admin can access user management page', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.users'))
        ->assertOk()
        ->assertSee('Manajemen User');
});

// ── Table listing ──────────────────────────────────────────────────────────

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

test('user management shows role badge and status badge', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    User::factory()->create(['role' => 'cashier', 'is_active' => false]);

    Livewire::actingAs($admin)
        ->test(UserManagement::class)
        ->assertSee('admin')
        ->assertSee('cashier')
        ->assertSee('Nonaktif');
});

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

test('user management paginates fifteen users per page', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    User::factory()->count(16)->create();

    Livewire::actingAs($admin)
        ->test(UserManagement::class)
        ->assertViewHas('users', fn ($users) => $users->count() === 15);
});

// ── Create user ────────────────────────────────────────────────────────────

test('admin can open create modal', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    Livewire::actingAs($admin)
        ->test(UserManagement::class)
        ->call('openCreateModal')
        ->assertSet('showModal', true)
        ->assertSet('editingUserId', null);
});

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

test('admin can deactivate another user', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $target = User::factory()->create(['is_active' => true]);

    Livewire::actingAs($admin)
        ->test(UserManagement::class)
        ->call('toggleActive', $target->id);

    expect($target->fresh()->is_active)->toBeFalse();
});

test('admin can reactivate a deactivated user', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $target = User::factory()->create(['is_active' => false]);

    Livewire::actingAs($admin)
        ->test(UserManagement::class)
        ->call('toggleActive', $target->id);

    expect($target->fresh()->is_active)->toBeTrue();
});

test('admin cannot deactivate themselves via toggle', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    Livewire::actingAs($admin)
        ->test(UserManagement::class)
        ->call('toggleActive', $admin->id);

    // Admin should still be active
    expect($admin->fresh()->is_active)->toBeTrue();
});

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

test('admin can reset another users password to password123', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $target = User::factory()->create();

    Livewire::actingAs($admin)
        ->test(UserManagement::class)
        ->call('resetPassword', $target->id);

    expect(Hash::check('password123', $target->fresh()->password))->toBeTrue();
});

test('reset password dispatches warning notification', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $target = User::factory()->create(['name' => 'Kasir Uji']);

    Livewire::actingAs($admin)
        ->test(UserManagement::class)
        ->call('resetPassword', $target->id)
        ->assertDispatched('notify', type: 'warning');
});

// ── Close modal ────────────────────────────────────────────────────────────

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
