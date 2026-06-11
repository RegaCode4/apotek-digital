<?php

namespace App\Services;

use App\Models\Medicine;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMutation;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PosService
{
    /**
     * Process a full POS transaction within a single database transaction.
     *
     * @param  array<int, array{medicine_id: int, quantity: int, unit_price: float, discount: float, prescription_no: ?string}>  $cartItems
     * @param  array{buyer_name: string, payment_method: string, subtotal: float, discount_amount: float, tax_amount: float, grand_total: float, bpjs_claim_no: ?string, notes: ?string}  $saleData
     *
     * @throws RuntimeException when any cart item has insufficient stock
     */
    public function processTransaction(array $cartItems, array $saleData, int $cashierId): Sale
    {
        return DB::transaction(function () use ($cartItems, $saleData, $cashierId): Sale {
            // Lock all affected medicine rows to prevent race conditions (§7 Risk #1)
            $medicineIds = array_column($cartItems, 'medicine_id');

            /** @var \Illuminate\Database\Eloquent\Collection<int, Medicine> $medicines */
            $medicines = Medicine::whereIn('id', $medicineIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // Validate stock availability for every item before writing anything
            foreach ($cartItems as $item) {
                $medicine = $medicines->get($item['medicine_id']);

                if (! $medicine) {
                    throw new RuntimeException("Obat dengan ID {$item['medicine_id']} tidak ditemukan.");
                }

                if ($medicine->stock < $item['quantity']) {
                    throw new RuntimeException(
                        "Stok {$medicine->name} tidak mencukupi. Tersedia: {$medicine->stock}, diminta: {$item['quantity']}."
                    );
                }
            }

            // Create the sale header
            $sale = Sale::create([
                ...$saleData,
                'cashier_id' => $cashierId,
                'invoice_no' => Sale::generateInvoiceNo(),
            ]);

            // Create line items, deduct stock, and record mutations
            foreach ($cartItems as $item) {
                $medicine = $medicines->get($item['medicine_id']);

                $subtotal = ($item['unit_price'] * $item['quantity']) - ($item['discount'] ?? 0);

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'medicine_id' => $item['medicine_id'],
                    'prescription_no' => $item['prescription_no'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'] ?? 0,
                    'subtotal' => $subtotal,
                ]);

                $medicine->decrement('stock', $item['quantity']);

                StockMutation::create([
                    'medicine_id' => $item['medicine_id'],
                    'type' => 'out',
                    'quantity' => $item['quantity'],
                    'reference_id' => $sale->id,
                    'notes' => "Penjualan {$sale->invoice_no}",
                    'created_by' => $cashierId,
                ]);
            }

            return $sale->fresh();
        });
    }

    /**
     * Validate that a prescription number is provided when required.
     *
     * Returns true  — medicine does not require a prescription, or one was supplied.
     * Returns false — medicine requires a prescription but none was provided.
     */
    public function validatePrescription(int $medicineId, ?string $prescriptionNo): bool
    {
        $requiresPrescription = Medicine::where('id', $medicineId)
            ->value('requires_prescription');

        if (! $requiresPrescription) {
            return true;
        }

        return ! empty($prescriptionNo);
    }
}
