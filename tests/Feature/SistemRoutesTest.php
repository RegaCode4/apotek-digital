<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected from protected sistem routes to login', function (string $routeName) {
    $response = $this->get(route($routeName));

    $response->assertRedirect(route('sistem.login'));
})->with([
    'dashboard' => ['sistem.dashboard'],
    'inventaris' => ['sistem.inventaris'],
    'laporan' => ['sistem.laporan'],
    'pos' => ['sistem.pos'],
    'users' => ['sistem.users'],
    'medicines index' => ['inventaris.medicines.index'],
    'stok opname' => ['inventaris.stok-opname'],
    'mutasi stok' => ['inventaris.mutasi'],
]);

test('dashboard shows authenticated user name and role', function () {
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

test('admin can access all sistem modules', function (string $routeName) {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get(route($routeName))->assertOk();
})->with([
    'dashboard' => ['sistem.dashboard'],
    'inventaris' => ['sistem.inventaris'],
    'laporan' => ['sistem.laporan'],
    'pos' => ['sistem.pos'],
    'users' => ['sistem.users'],
    'medicines index' => ['inventaris.medicines.index'],
    'stok opname' => ['inventaris.stok-opname'],
    'mutasi stok' => ['inventaris.mutasi'],
]);

test('pharmacist can access dashboard inventaris laporan and pos', function (string $routeName) {
    $pharmacist = User::factory()->create(['role' => 'pharmacist']);

    $this->actingAs($pharmacist)->get(route($routeName))->assertOk();
})->with([
    'dashboard' => ['sistem.dashboard'],
    'inventaris' => ['sistem.inventaris'],
    'laporan' => ['sistem.laporan'],
    'pos' => ['sistem.pos'],
    'medicines index' => ['inventaris.medicines.index'],
    'stok opname' => ['inventaris.stok-opname'],
    'mutasi stok' => ['inventaris.mutasi'],
]);

test('pharmacist cannot access user management', function () {
    $pharmacist = User::factory()->create(['role' => 'pharmacist']);

    $this->actingAs($pharmacist)->get(route('sistem.users'))->assertForbidden();
});

test('cashier can access dashboard and pos only', function () {
    $cashier = User::factory()->create(['role' => 'cashier']);

    $this->actingAs($cashier)->get(route('sistem.dashboard'))->assertOk();
    $this->actingAs($cashier)->get(route('sistem.pos'))->assertOk();
});

test('cashier cannot access inventaris laporan or user management', function (string $routeName) {
    $cashier = User::factory()->create(['role' => 'cashier']);

    $this->actingAs($cashier)->get(route($routeName))->assertForbidden();
})->with([
    'inventaris' => ['sistem.inventaris'],
    'laporan' => ['sistem.laporan'],
    'users' => ['sistem.users'],
    'medicines index' => ['inventaris.medicines.index'],
    'stok opname' => ['inventaris.stok-opname'],
    'mutasi stok' => ['inventaris.mutasi'],
]);
