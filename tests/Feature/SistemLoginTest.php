<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('sistem login screen can be rendered', function () {
    $response = $this->get(route('sistem.login'));

    $response->assertOk();
    $response->assertSee('Login Sistem');
});

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

test('sistem users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('sistem.logout'));

    $response->assertRedirect('/sistem/login');
    $this->assertGuest();
});
