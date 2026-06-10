<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

test('auth.apotek middleware redirects guests to sistem login', function () {
    Route::middleware(['auth.apotek'])->get('/_test/auth-apotek', fn () => 'protected');

    $response = $this->get('/_test/auth-apotek');

    $response->assertRedirect(route('sistem.login'));
});

test('auth.apotek middleware allows authenticated users', function () {
    Route::middleware(['auth.apotek'])->get('/_test/auth-apotek', fn () => 'protected');

    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/_test/auth-apotek');

    $response->assertOk();
    $response->assertSee('protected');
});

test('role middleware allows users with matching role', function () {
    Route::middleware(['auth.apotek', 'role:admin,pharmacist'])->get('/_test/role-protected', fn () => 'role ok');

    $admin = User::factory()->create(['role' => 'admin']);
    $pharmacist = User::factory()->create(['role' => 'pharmacist']);

    $this->actingAs($admin)->get('/_test/role-protected')->assertOk();
    $this->actingAs($pharmacist)->get('/_test/role-protected')->assertOk();
});

test('role middleware aborts with 403 for users without matching role', function () {
    Route::middleware(['auth.apotek', 'role:admin,pharmacist'])->get('/_test/role-protected', fn () => 'role ok');

    $cashier = User::factory()->create(['role' => 'cashier']);

    $response = $this->actingAs($cashier)->get('/_test/role-protected');

    $response->assertForbidden();
});
