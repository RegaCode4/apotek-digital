<?php

use App\Models\Medicine;
use Tests\TestCase;

uses(TestCase::class);

test('medicine has correct fillable attributes', function () {
    $medicine = new Medicine;

    expect($medicine->getFillable())->toBe([
        'name',
        'generic_name',
        'category',
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

test('medicine has correct casts', function () {
    $medicine = new Medicine;

    expect($medicine->getCasts())
        ->toHaveKey('requires_prescription', 'boolean')
        ->toHaveKey('expiry_date', 'date')
        ->toHaveKey('price', 'decimal:2');
});

test('low stock scope filters medicines at or below minimum stock', function () {
    $query = Medicine::lowStock();

    expect($query->toSql())->toContain('"stock" <= "min_stock"');
});

test('expiring soon scope filters medicines by expiry date', function () {
    $query = Medicine::expiringSoon(6);

    expect($query->toSql())->toContain('"expiry_date"');
});
