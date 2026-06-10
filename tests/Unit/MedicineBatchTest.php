<?php

use App\Models\MedicineBatch;
use Tests\TestCase;

uses(TestCase::class);

test('medicine batch has correct fillable attributes', function () {
    $medicineBatch = new MedicineBatch;

    expect($medicineBatch->getFillable())->toBe([
        'medicine_id',
        'purchase_order_id',
        'batch_number',
        'quantity',
        'expiry_date',
    ]);
});

test('medicine batch has correct casts', function () {
    $medicineBatch = new MedicineBatch;

    expect($medicineBatch->getCasts())
        ->toHaveKey('expiry_date', 'date');
});

test('fefo scope orders batches by expiry date ascending', function () {
    $query = MedicineBatch::fefo();

    expect($query->toSql())->toContain('order by "expiry_date" asc');
});
