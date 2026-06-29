<?php

/** Feature test untuk halaman login sistem: render, autentikasi, validasi, dan logout. */

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

// Test: halaman login dapat dirender
test('sistem login screen can be rendered', function () {
    $response = $this->get(route('sistem.login'));

    $response->assertOk();
    $response->assertSee('Login Sistem');
});

// Test: user bisa login dengan kredensial valid
test('sistem users can authenticate using the login screen', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
        'is_active' => true,
    ]);

    $response = $this->post(route('sistem.login.post'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect('/sistem/dashboard');

    $this->assertAuthenticatedAs($user);
});

// Test: user gagal login dengan password salah
test('sistem users cannot authenticate with incorrect password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
        'is_active' => true,
    ]);

    $response = $this->post(route('sistem.login.post'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrors(['email']);
    $this->assertGuest();
});

// Test: user nonaktif tidak bisa login
test('sistem users cannot authenticate if they are inactive', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
        'is_active' => false,
    ]);

    $response = $this->post(route('sistem.login.post'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertSessionHasErrors(['email']);
    $this->assertGuest();
});

// Test: user bisa logout
test('sistem users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('sistem.logout'));

    $response->assertRedirect('/sistem/login');
    $this->assertGuest();
});
