<?php

use App\Models\Medicine;
use App\Models\Sale;
use App\Models\StockMutation;
use App\Models\User;
use App\Services\PosService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

// ── Helpers ───────────────────────────────────────────────────────────────────

/**
 * Build a minimal cart item array for a given medicine.
 *
 * @return array{medicine_id: int, quantity: int, unit_price: float, discount: float, prescription_no: ?string}
 */
function cartItem(Medicine $medicine, int $quantity = 1, ?string $prescriptionNo = null): array
{
    return [
        'medicine_id' => $medicine->id,
        'quantity' => $quantity,
        'unit_price' => (float) $medicine->price,
        'discount' => 0.0,
        'prescription_no' => $prescriptionNo,
    ];
}

/**
 * Build minimal sale data for processTransaction().
 *
 * @return array{buyer_name: string, payment_method: string, subtotal: float, discount_amount: float, tax_amount: float, grand_total: float, bpjs_claim_no: ?string, notes: null}
 */
function saleData(
    float $subtotal = 50000,
    string $paymentMethod = 'cash',
    ?string $bpjsClaimNo = null,
): array {
    return [
        'buyer_name' => 'Test Pembeli',
        'payment_method' => $paymentMethod,
        'subtotal' => $subtotal,
        'discount_amount' => 0,
        'tax_amount' => 0,
        'grand_total' => $subtotal,
        'bpjs_claim_no' => $bpjsClaimNo,
        'notes' => null,
    ];
}

/**
 * Create an active cashier user.
 */
function cashier(): User
{
    return User::factory()->create([
        'role' => 'cashier',
        'is_active' => true,
        'password' => Hash::make('password'),
    ]);
}

// ── Tests ──────────────────────────────────────────────────────────────────────

test('test_successful_transaction_reduces_stock', function () {
    $medicine = Medicine::factory()->create(['stock' => 20, 'price' => 10000]);
    $cashier = cashier();
    $service = app(PosService::class);

    $service->processTransaction(
        cartItems: [cartItem($medicine, 5)],
        saleData: saleData(50000),
        cashierId: $cashier->id,
    );

    expect($medicine->fresh()->stock)->toBe(15);
});

test('test_transaction_fails_when_stock_insufficient', function () {
    $medicine = Medicine::factory()->create(['stock' => 3, 'price' => 10000]);
    $cashier = cashier();
    $service = app(PosService::class);

    expect(fn () => $service->processTransaction(
        cartItems: [cartItem($medicine, 10)],
        saleData: saleData(100000),
        cashierId: $cashier->id,
    ))->toThrow(RuntimeException::class, 'tidak mencukupi');

    // Stock must remain unchanged when transaction is rolled back
    expect($medicine->fresh()->stock)->toBe(3);
});

test('test_prescription_medicine_requires_prescription_no', function () {
    $medicine = Medicine::factory()->create([
        'stock' => 10,
        'price' => 15000,
        'requires_prescription' => true,
    ]);
    $cashier = cashier();
    $service = app(PosService::class);

    // PosService itself does not validate prescription — that's KasirPage's job.
    // This test verifies that PosService DOES save prescription_no to sale_items
    // and that validatePrescription() returns false when it's missing.
    expect($service->validatePrescription($medicine->id, null))->toBeFalse();
    expect($service->validatePrescription($medicine->id, 'RES-001'))->toBeTrue();

    // When processTransaction is called with a prescription_no, it is persisted
    $sale = $service->processTransaction(
        cartItems: [cartItem($medicine, 1, 'RES-001')],
        saleData: saleData(15000),
        cashierId: $cashier->id,
    );

    expect($sale->saleItems->first()->prescription_no)->toBe('RES-001');
});

test('test_transaction_creates_stock_mutation_record', function () {
    $medicine = Medicine::factory()->create(['stock' => 15, 'price' => 8000]);
    $cashier = cashier();
    $service = app(PosService::class);

    $sale = $service->processTransaction(
        cartItems: [cartItem($medicine, 3)],
        saleData: saleData(24000),
        cashierId: $cashier->id,
    );

    $mutation = StockMutation::where('medicine_id', $medicine->id)
        ->where('reference_id', $sale->id)
        ->first();

    expect($mutation)->not->toBeNull();
    expect($mutation->type)->toBe('out');
    expect($mutation->quantity)->toBe(3);
    expect($mutation->created_by)->toBe($cashier->id);
});

test('test_invoice_number_is_unique_per_day', function () {
    $medicine = Medicine::factory()->create(['stock' => 50, 'price' => 5000]);
    $cashier = cashier();
    $service = app(PosService::class);

    $sale1 = $service->processTransaction(
        cartItems: [cartItem($medicine, 1)],
        saleData: saleData(5000),
        cashierId: $cashier->id,
    );

    $sale2 = $service->processTransaction(
        cartItems: [cartItem($medicine, 1)],
        saleData: saleData(5000),
        cashierId: $cashier->id,
    );

    expect($sale1->invoice_no)->not->toBe($sale2->invoice_no);

    // Both should follow the INV-YYYYMMDD-NNN format for today
    $prefix = 'INV-'.now()->format('Ymd').'-';
    expect($sale1->invoice_no)->toStartWith($prefix);
    expect($sale2->invoice_no)->toStartWith($prefix);

    // Sequence should be strictly incrementing
    $seq1 = (int) substr($sale1->invoice_no, -3);
    $seq2 = (int) substr($sale2->invoice_no, -3);
    expect($seq2)->toBe($seq1 + 1);
});

test('test_bpjs_transaction_saves_claim_no', function () {
    $medicine = Medicine::factory()->create(['stock' => 10, 'price' => 20000]);
    $cashier = cashier();
    $service = app(PosService::class);

    $claimNo = '0001234567890';

    $sale = $service->processTransaction(
        cartItems: [cartItem($medicine, 1)],
        saleData: saleData(20000, 'bpjs', $claimNo),
        cashierId: $cashier->id,
    );

    expect($sale->payment_method)->toBe('bpjs');
    expect($sale->bpjs_claim_no)->toBe($claimNo);

    // Persisted to DB
    expect(Sale::find($sale->id)->bpjs_claim_no)->toBe($claimNo);
});
