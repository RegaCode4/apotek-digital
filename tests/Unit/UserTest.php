<?php

/** Unit test untuk model User: fillable, hidden, casts, role helpers, dan scope. */

use App\Models\User;
use Tests\TestCase;

uses(TestCase::class);

// Test: fillable attributes user
test('user has correct fillable attributes', function () {
    $user = new User;

    expect($user->getFillable())->toContain('name', 'email', 'password', 'role', 'is_active');
});

// Test: hidden attributes user
test('user has correct hidden attributes', function () {
    $user = new User;

    expect($user->getHidden())->toContain('password', 'remember_token');
});

// Test: casts user
test('user has correct casts', function () {
    $user = new User;

    expect($user->getCasts())
        ->toHaveKey('is_active', 'boolean')
        ->toHaveKey('password', 'hashed');
});

// Test: helper isAdmin, isPharmacist, isCashier
test('user role helper methods work correctly', function () {
    $admin = new User(['role' => 'admin']);
    $pharmacist = new User(['role' => 'pharmacist']);
    $cashier = new User(['role' => 'cashier']);

    expect($admin->isAdmin())->toBeTrue();
    expect($admin->isPharmacist())->toBeFalse();
    expect($admin->isCashier())->toBeFalse();

    expect($pharmacist->isAdmin())->toBeFalse();
    expect($pharmacist->isPharmacist())->toBeTrue();
    expect($pharmacist->isCashier())->toBeFalse();

    expect($cashier->isAdmin())->toBeFalse();
    expect($cashier->isPharmacist())->toBeFalse();
    expect($cashier->isCashier())->toBeTrue();
});

// Test: scope active memfilter user berdasarkan is_active
test('active scope filters is_active', function () {
    $query = User::active();

    expect($query->toSql())->toContain('is_active');
});
