<?php

namespace App\Livewire\Pos;

use App\Contracts\BpjsServiceInterface;
use App\Models\Medicine;
use App\Models\User;
use App\Services\PosService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use RuntimeException;

#[Layout('layouts.sistem')]
#[Title('POS / Kasir')]
class KasirPage extends Component
{
    // ── Panel kiri — pencarian obat ───────────────────────────
    public string $search = '';

    // ── Keranjang belanja ─────────────────────────────────────
    /**
     * @var array<int, array{
     *   medicine_id: int,
     *   name: string,
     *   unit_price: float,
     *   requires_prescription: bool,
     *   quantity: int,
     *   prescription_no: string,
     *   is_fornas: bool
     * }>
     */
    public array $cart = [];

    // ── Form checkout ─────────────────────────────────────────
    public string $buyerName = '';

    public string $paymentMethod = 'cash';

    public float $discountAmount = 0;

    public bool $taxEnabled = false;

    // ── BPJS state ────────────────────────────────────────────
    public string $bpjsNumber = '';

    /** @var array{status: string, name: string|null, kelas: string|null}|null */
    public ?array $bpjsVerification = null;

    public bool $bpjsVerified = false;

    // ── UI state ──────────────────────────────────────────────
    public bool $showSuccessModal = false;

    public string $lastInvoiceNo = '';

    public int $lastSaleId = 0;

    public ?string $errorMessage = null;

    // ── Constructor injection ─────────────────────────────────
    private BpjsServiceInterface $bpjs;

    public function boot(BpjsServiceInterface $bpjs): void
    {
        $this->bpjs = $bpjs;
    }

    // ── Watchers ──────────────────────────────────────────────

    /**
     * Reset BPJS state when payment method changes away from bpjs.
     */
    public function updatedPaymentMethod(): void
    {
        if ($this->paymentMethod !== 'bpjs') {
            $this->resetBpjs();
        }
    }

    // ── Computed — pencarian obat ─────────────────────────────

    /**
     * @return Collection<int, Medicine>
     */
    public function getSearchResultsProperty(): Collection
    {
        if (strlen($this->search) < 2) {
            return collect();
        }

        return Medicine::query()
            ->where(function (Builder $query): void {
                $query->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('generic_name', 'like', '%'.$this->search.'%');
            })
            ->orderByDesc('stock')
            ->orderBy('name')
            ->limit(20)
            ->get();
    }

    // ── Computed — kalkulasi keranjang ────────────────────────

    public function getCartSubtotalProperty(): float
    {
        return collect($this->cart)->sum(
            fn (array $item): float => $item['unit_price'] * $item['quantity']
        );
    }

    public function getTaxAmountProperty(): float
    {
        if (! $this->taxEnabled) {
            return 0;
        }

        return round(($this->cartSubtotal - $this->discountAmount) * 0.11, 2);
    }

    public function getGrandTotalProperty(): float
    {
        return max(0, $this->cartSubtotal - $this->discountAmount + $this->taxAmount);
    }

    // ── Aksi — keranjang ──────────────────────────────────────

    public function addToCart(int $medicineId): void
    {
        $medicine = Medicine::find($medicineId);

        if (! $medicine || $medicine->stock <= 0) {
            return;
        }

        // Jika sudah ada di keranjang, tambah qty saja
        foreach ($this->cart as $index => $item) {
            if ($item['medicine_id'] === $medicineId) {
                $this->cart[$index]['quantity']++;
                $this->search = '';

                return;
            }
        }

        // Cek fornas saat ditambahkan ke keranjang
        $isFornas = $this->paymentMethod === 'bpjs'
            ? $this->bpjs->isFornas($medicine->id)
            : true; // Tidak relevan untuk non-BPJS, default true agar badge tidak muncul

        $this->cart[] = [
            'medicine_id' => $medicine->id,
            'name' => $medicine->name,
            'unit_price' => (float) $medicine->price,
            'requires_prescription' => (bool) $medicine->requires_prescription,
            'quantity' => 1,
            'prescription_no' => '',
            'is_fornas' => $isFornas,
        ];

        $this->search = '';
    }

    public function updateQuantity(int $index, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->removeFromCart($index);

            return;
        }

        if (isset($this->cart[$index])) {
            $this->cart[$index]['quantity'] = $quantity;
        }
    }

    public function removeFromCart(int $index): void
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
    }

    // ── Aksi — BPJS ───────────────────────────────────────────

    public function verifyBpjs(): void
    {
        $this->bpjsVerification = null;
        $this->bpjsVerified = false;

        if (empty(trim($this->bpjsNumber))) {
            $this->errorMessage = 'Masukkan nomor BPJS terlebih dahulu.';

            return;
        }

        $this->errorMessage = null;
        $result = $this->bpjs->verifyMember($this->bpjsNumber);
        $this->bpjsVerification = $result;
        $this->bpjsVerified = $result['status'] === 'aktif';

        // Re-evaluate is_fornas for all existing cart items
        if ($this->bpjsVerified) {
            foreach ($this->cart as $index => $item) {
                $this->cart[$index]['is_fornas'] = $this->bpjs->isFornas($item['medicine_id']);
            }
        }
    }

    // ── Aksi — proses transaksi ───────────────────────────────

    public function processTransaction(PosService $posService): void
    {
        $this->errorMessage = null;

        $this->validate([
            'buyerName' => ['required', 'string', 'max:100'],
            'paymentMethod' => ['required', 'in:cash,transfer,bpjs,insurance'],
            'discountAmount' => ['numeric', 'min:0'],
        ]);

        if (empty($this->cart)) {
            $this->errorMessage = 'Keranjang belanja masih kosong.';

            return;
        }

        // Blokir jika BPJS belum terverifikasi atau tidak aktif
        if ($this->paymentMethod === 'bpjs' && ! $this->bpjsVerified) {
            $this->errorMessage = 'Verifikasi peserta BPJS harus dilakukan dan statusnya aktif sebelum memproses transaksi.';

            return;
        }

        foreach ($this->cart as $item) {
            if ($item['requires_prescription'] && empty($item['prescription_no'])) {
                $this->errorMessage = "No. Resep wajib diisi untuk obat: {$item['name']}.";

                return;
            }
        }

        try {
            /** @var User $user */
            $user = Auth::user();

            $sale = $posService->processTransaction(
                cartItems: $this->buildCartItems(),
                saleData: $this->buildSaleData(),
                cashierId: $user->id,
            );

            $this->lastInvoiceNo = $sale->invoice_no;
            $this->lastSaleId = $sale->id;
            $this->resetCart();
            $this->showSuccessModal = true;
        } catch (RuntimeException $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function closeSuccessModal(): void
    {
        $this->showSuccessModal = false;
        $this->lastInvoiceNo = '';
    }

    // ── Render ────────────────────────────────────────────────

    public function render(): View
    {
        return view('livewire.pos.kasir-page', [
            'searchResults' => $this->searchResults,
            'cartSubtotal' => $this->cartSubtotal,
            'taxAmount' => $this->taxAmount,
            'grandTotal' => $this->grandTotal,
        ]);
    }

    // ── Private helpers ───────────────────────────────────────

    /**
     * @return array<int, array{medicine_id: int, quantity: int, unit_price: float, discount: float, prescription_no: ?string}>
     */
    private function buildCartItems(): array
    {
        return collect($this->cart)->map(fn (array $item): array => [
            'medicine_id' => $item['medicine_id'],
            'quantity' => $item['quantity'],
            'unit_price' => $item['unit_price'],
            'discount' => 0.0,
            'prescription_no' => $item['prescription_no'] ?: null,
        ])->values()->all();
    }

    /**
     * @return array{buyer_name: string, payment_method: string, subtotal: float, discount_amount: float, tax_amount: float, grand_total: float, bpjs_claim_no: string|null, notes: null}
     */
    private function buildSaleData(): array
    {
        return [
            'buyer_name' => $this->buyerName,
            'payment_method' => $this->paymentMethod,
            'subtotal' => $this->cartSubtotal,
            'discount_amount' => $this->discountAmount,
            'tax_amount' => $this->taxAmount,
            'grand_total' => $this->grandTotal,
            'bpjs_claim_no' => $this->paymentMethod === 'bpjs' ? $this->bpjsNumber : null,
            'notes' => null,
        ];
    }

    private function resetBpjs(): void
    {
        $this->bpjsNumber = '';
        $this->bpjsVerification = null;
        $this->bpjsVerified = false;

        // Reset is_fornas flags — no longer relevant
        foreach ($this->cart as $index => $item) {
            $this->cart[$index]['is_fornas'] = true;
        }
    }

    private function resetCart(): void
    {
        $this->cart = [];
        $this->buyerName = '';
        $this->paymentMethod = 'cash';
        $this->discountAmount = 0;
        $this->taxEnabled = false;
        $this->errorMessage = null;
        $this->lastSaleId = 0;
        $this->resetBpjs();
    }
}
