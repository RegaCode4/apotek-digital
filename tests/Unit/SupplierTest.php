<?php

use App\Models\Supplier;
use Tests\TestCase;

uses(TestCase::class);

test('supplier has correct fillable attributes', function () {
    $supplier = new Supplier;

    expect($supplier->getFillable())->toBe([
        'name',
        'contact_person',
        'phone',
        'address',
    ]);
});
