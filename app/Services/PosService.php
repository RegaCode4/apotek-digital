<?php

namespace App\Services;

use App\Models\Medicine;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMutation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Layanan pemrosesan transaksi POS dengan validasi stok dan manajemen inventaris.
 */
class PosService
{
    /**
     * Memproses transaksi POS penuh dalam satu transaksi database.
     *
     * @param  array<int, array{medicine_id: int, quantity: int, unit_price: float, discount: float, prescription_no: ?string}>  $cartItems
     * @param  array{buyer_name: string, payment_method: string, subtotal: float, discount_amount: float, tax_amount: float, grand_total: float, bpjs_claim_no: ?string, notes: ?string}  $saleData
     *
     * @throws RuntimeException ketika stok item keranjang tidak mencukupi
     */
    public function processTransaction(array $cartItems, array $saleData, int $cashierId): Sale
    {
        return DB::transaction(function () use ($cartItems, $saleData, $cashierId): Sale {
            // Mengunci semua baris obat yang terpengaruh untuk mencegah kondisi balapan (§7 Risk #1)
            $medicineIds = array_column($cartItems, 'medicine_id');

            /** @var Collection<int, Medicine> $medicines */
            $medicines = Medicine::whereIn('id', $medicineIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // Memvalidasi ketersediaan stok untuk setiap item sebelum menulis apa pun
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

            // Membuat header penjualan
            $sale = Sale::create([
                ...$saleData,
                'cashier_id' => $cashierId,
                'invoice_no' => Sale::generateInvoiceNo(),
            ]);

            // Membuat item baris, mengurangi stok, dan mencatat mutasi
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
     * Memvalidasi bahwa nomor resep diberikan saat diperlukan.
     *
     * Mengembalikan true  — obat tidak memerlukan resep, atau resep telah diberikan.
     * Mengembalikan false — obat memerlukan resep tetapi tidak ada yang diberikan.
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
