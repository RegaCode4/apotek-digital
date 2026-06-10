<?php

use App\Models\StockMutation;
use Tests\TestCase;

uses(TestCase::class);

test('stock mutation has correct fillable attributes', function () {
    $stockMutation = new StockMutation;

    expect($stockMutation->getFillable())->toBe([
        'medicine_id',
        'type',
        'quantity',
        'reference_id',
        'notes',
        'created_by',
    ]);
});

test('stock mutation has correct casts', function () {
    $stockMutation = new StockMutation;

    expect($stockMutation->getCasts())
        ->toHaveKey('created_at', 'datetime');
});
