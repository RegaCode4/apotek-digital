<?php

use App\Models\PurchaseOrder;
use Tests\TestCase;

uses(TestCase::class);

test('purchase order has correct fillable attributes', function () {
    $purchaseOrder = new PurchaseOrder;

    expect($purchaseOrder->getFillable())->toBe([
        'supplier_id',
        'order_date',
        'status',
        'total_amount',
        'received_by',
        'notes',
    ]);
});

test('purchase order has correct casts', function () {
    $purchaseOrder = new PurchaseOrder;

    expect($purchaseOrder->getCasts())
        ->toHaveKey('order_date', 'date')
        ->toHaveKey('total_amount', 'decimal:2');
});
