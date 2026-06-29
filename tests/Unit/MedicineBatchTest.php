<?php

/** Unit test untuk model MedicineBatch: fillable, casts, dan scope FEFO. */

use App\Models\MedicineBatch;
use Tests\TestCase;

uses(TestCase::class);

// Test: fillable attributes medicine batch
test('medicine batch has correct fillable attributes', function () {
    $medicineBatch = new MedicineBatch;

    expect($medicineBatch->getFillable())->toBe([
        'medicine_id',
        'batch_number',
        'quantity',
        'expiry_date',
    ]);
});

// Test: casts medicine batch
test('medicine batch has correct casts', function () {
    $medicineBatch = new MedicineBatch;

    expect($medicineBatch->getCasts())
        ->toHaveKey('expiry_date', 'date');
});

// Test: scope FEFO mengurutkan batch berdasarkan expiry_date ASC
test('fefo scope orders batches by expiry date ascending', function () {
    $query = MedicineBatch::fefo();

    expect($query->toSql())->toContain('order by "expiry_date" asc');
});
