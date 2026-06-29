<?php

/** Unit test untuk model Medicine: fillable, casts, dan scope (lowStock, expiringSoon). */

use App\Models\Medicine;
use Tests\TestCase;

uses(TestCase::class);

// Test: fillable attributes medicine
test('medicine has correct fillable attributes', function () {
    $medicine = new Medicine;

    expect($medicine->getFillable())->toBe([
        'name',
        'generic_name',
        'category_id',
        'manufacturer',
        'unit',
        'price',
        'stock',
        'min_stock',
        'expiry_date',
        'requires_prescription',
        'description',
    ]);
});

// Test: casts medicine
test('medicine has correct casts', function () {
    $medicine = new Medicine;

    expect($medicine->getCasts())
        ->toHaveKey('requires_prescription', 'boolean')
        ->toHaveKey('expiry_date', 'date:Y-m-d')
        ->toHaveKey('price', 'decimal:2');
});

// Test: scope lowStock memfilter stok <= min_stock
test('low stock scope filters medicines at or below minimum stock', function () {
    $query = Medicine::lowStock();

    expect($query->toSql())->toContain('"stock" <= "min_stock"');
});

// Test: scope expiringSoon memfilter obat berdasarkan expiry_date
test('expiring soon scope filters medicines by expiry date', function () {
    $query = Medicine::expiringSoon(6);

    expect($query->toSql())->toContain('"expiry_date"');
});
