<?php

namespace App\Livewire\Pos;

use App\Contracts\BpjsServiceInterface;
use App\Models\Category;
use App\Models\Medicine;
use App\Models\User;
use App\Services\PosService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use RuntimeException;

#[Layout('layouts.sistem')]
#[Title('POS / Kasir')]
/** POS / Kasir — halaman utama transaksi penjualan obat. */
class KasirPage extends Component
{
    // ── Panel kiri — pencarian obat ───────────────────────────
    public string $search = '';

    /** ID kategori aktif untuk filter; null = semua kategori. */
    public ?int $categoryId = null;

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

    // ── Injeksi konstruktor ─────────────────────────────────
    private BpjsServiceInterface $bpjs;

    public function boot(BpjsServiceInterface $bpjs): void
    {
        $this->bpjs = $bpjs;
    }

    // ── Pemantau ──────────────────────────────────────────────

    /**
     * Mereset state BPJS saat metode pembayaran berubah dari bpjs.
     */
    public function updatedPaymentMethod(): void
    {
        if ($this->paymentMethod !== 'bpjs') {
            $this->resetBpjs();
        }
    }

    // ── Computed — pencarian obat ─────────────────────────────

    /**
     * Apakah daftar sedang difilter (lewat teks pencarian atau kategori).
     */
    public function getIsFilteringProperty(): bool
    {
        return strlen($this->search) >= 2 || $this->categoryId !== null;
    }

    /**
     * Daftar kategori untuk filter.
     *
     * @return Collection<int, Category>
     */
    public function getCategoriesProperty(): Collection
    {
        return Category::query()->orderBy('name')->get();
    }

    /**
     * @return Collection<int, Medicine>
     */
    public function getSearchResultsProperty(): Collection
    {
        // Jika tidak ada pencarian atau filter yang aktif, kembalikan koleksi kosong
        if (! $this->isFiltering) {
            return collect();
        }

        // Jalankan query pencarian obat berdasarkan kategori dan kata kunci
        return Medicine::query()
            ->when($this->categoryId !== null, fn (Builder $q) => $q->where('category_id', $this->categoryId))
            ->when(strlen($this->search) >= 2, function (Builder $query): void {
                $term = $this->search;
                // Lakukan pencarian pada nama obat, nama generik, atau nama kategori
                $query->where(function (Builder $q) use ($term): void {
                    $q->where('name', 'like', '%'.$term.'%')
                        ->orWhere('generic_name', 'like', '%'.$term.'%')
                        ->orWhereHas('category', fn (Builder $c) => $c->where('name', 'like', '%'.$term.'%'));
                });
            })
            // Urutkan berdasarkan stok terbanyak dan nama obat
            ->orderByDesc('stock')
            ->orderBy('name')
            // Batasi hasil maksimal 40 obat untuk optimasi performa
            ->limit(40)
            ->get();
    }

    // ── Aksi — filter kategori ────────────────────────────────

    /** Set filter kategori aktif. */
    public function selectCategory(?int $categoryId): void
    {
        $this->categoryId = $this->categoryId === $categoryId ? null : $categoryId;
    }

    /**
     * Obat terlaris — ditampilkan saat belum ada pencarian.
     *
     * @return Collection<int, Medicine>
     */
    public function getTopMedicinesProperty(): Collection
    {
        // Ambil 8 ID obat terlaris berdasarkan total kuantitas yang terjual
        $topIds = DB::table('sale_items')
            ->select('medicine_id', DB::raw('SUM(quantity) as total_qty'))
            ->groupBy('medicine_id')
            ->orderByDesc('total_qty')
            ->limit(8)
            ->pluck('medicine_id');

        // Jika tidak ada data penjualan, kembalikan koleksi kosong
        if ($topIds->isEmpty()) {
            return collect();
        }

        // Ambil data detail obat berdasarkan ID yang didapat, lalu urutkan kembali sesuai dengan urutan penjualan tertinggi
        return Medicine::query()
            ->whereIn('id', $topIds)
            ->get()
            ->sortBy(fn (Medicine $m): int => $topIds->search($m->id))
            ->values();
    }

    // ── Computed — kalkulasi keranjang ────────────────────────

    /** Subtotal harga seluruh item di keranjang. */
    public function getCartSubtotalProperty(): float
    {
        return collect($this->cart)->sum(
            fn (array $item): float => $item['unit_price'] * $item['quantity']
        );
    }

    /** PPN 11% dari subtotal setelah diskon. */
    public function getTaxAmountProperty(): float
    {
        if (! $this->taxEnabled) {
            return 0;
        }

        return round(($this->cartSubtotal - $this->discountAmount) * 0.11, 2);
    }

    /** Total akhir = subtotal - diskon + pajak. */
    public function getGrandTotalProperty(): float
    {
        return max(0, $this->cartSubtotal - $this->discountAmount + $this->taxAmount);
    }

    // ── Aksi — keranjang ──────────────────────────────────────

    /** Tambah item ke keranjang belanja. */
    public function addToCart(int $medicineId): void
    {
        // Cari data obat berdasarkan ID, abaikan jika tidak ditemukan atau stok kosong
        $medicine = Medicine::find($medicineId);

        if (! $medicine || $medicine->stock <= 0) {
            return;
        }

        // Jika sudah ada di keranjang, tambah qty setelah validasi stok
        foreach ($this->cart as $index => $item) {
            if ($item['medicine_id'] === $medicineId) {
                // Periksa apakah penambahan kuantitas akan melebihi stok tersedia
                if ($medicine->stock < $item['quantity'] + 1) {
                    $this->dispatch('notify', type: 'warning', message: "Stok {$medicine->name} tidak mencukupi (tersedia: {$medicine->stock}).");

                    return;
                }

                // Tambahkan kuantitas dan reset form pencarian
                $this->cart[$index]['quantity']++;
                $this->search = '';

                return;
            }
        }

        // Cek fornas saat ditambahkan ke keranjang
        // Logika: Jika pembayaran BPJS, cek status Fornas obat tersebut
        $isFornas = $this->paymentMethod === 'bpjs'
            ? $this->bpjs->isFornas($medicine->id)
            : true; // Tidak relevan untuk non-BPJS, default true agar badge tidak muncul

        // Tambahkan item baru ke dalam struktur array keranjang belanja
        $this->cart[] = [
            'medicine_id' => $medicine->id,
            'name' => $medicine->name,
            'unit_price' => (float) $medicine->price,
            'requires_prescription' => (bool) $medicine->requires_prescription,
            'quantity' => 1,
            'prescription_no' => '',
            'is_fornas' => $isFornas,
        ];

        // Kosongkan kolom pencarian setelah item berhasil masuk keranjang
        $this->search = '';
    }

    /** Ubah jumlah item di keranjang berdasarkan index. */
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

    /** Hapus item dari keranjang. */
    public function removeFromCart(int $index): void
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
    }

    // ── Aksi — BPJS ───────────────────────────────────────────

    /** Verifikasi nomor BPJS ke layanan eksternal. */
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

        // Evaluasi ulang is_fornas untuk seluruh item di keranjang
        if ($this->bpjsVerified) {
            foreach ($this->cart as $index => $item) {
                $this->cart[$index]['is_fornas'] = $this->bpjs->isFornas($item['medicine_id']);
            }
        }
    }

    // ── Aksi — proses transaksi ───────────────────────────────

    /** Proses checkout — validasi, simpan transaksi via PosService. */
    public function processTransaction(PosService $posService): void
    {
        $this->errorMessage = null;

        // Validasi input form utama dari kasir
        $this->validate([
            'buyerName' => ['required', 'string', 'max:100'],
            'paymentMethod' => ['required', 'in:cash,transfer,bpjs,insurance'],
            'discountAmount' => ['numeric', 'min:0'],
        ]);

        // Pastikan ada item di keranjang sebelum memproses
        if (empty($this->cart)) {
            $this->errorMessage = 'Keranjang belanja masih kosong.';
            $this->dispatch('notify', type: 'warning', message: 'Keranjang belanja masih kosong.');

            return;
        }

        // Blokir jika BPJS belum terverifikasi atau tidak aktif (khusus pembayaran BPJS)
        if ($this->paymentMethod === 'bpjs' && ! $this->bpjsVerified) {
            $this->errorMessage = 'Verifikasi peserta BPJS harus dilakukan dan statusnya aktif sebelum memproses transaksi.';
            $this->dispatch('notify', type: 'error', message: $this->errorMessage);

            return;
        }

        // Validasi kelengkapan No. Resep untuk setiap obat keras/resep
        foreach ($this->cart as $item) {
            if ($item['requires_prescription'] && empty($item['prescription_no'])) {
                $this->errorMessage = "No. Resep wajib diisi untuk obat: {$item['name']}.";
                $this->dispatch('notify', type: 'error', message: $this->errorMessage);

                return;
            }
        }

        try {
            // Ambil data user/kasir yang sedang login
            /** @var User $user */
            $user = Auth::user();

            // Eksekusi proses transaksi di layer service (pemotongan stok, pencatatan DB)
            $sale = $posService->processTransaction(
                cartItems: $this->buildCartItems(),
                saleData: $this->buildSaleData(),
                cashierId: $user->id,
            );

            // Simpan data invoice untuk ditampilkan pada struk cetak
            $this->lastInvoiceNo = $sale->invoice_no;
            $this->lastSaleId = $sale->id;

            // Bersihkan form setelah sukses
            $this->resetCart();
            $this->showSuccessModal = true;

            $this->dispatch('notify', type: 'success', message: "Transaksi {$sale->invoice_no} berhasil disimpan.");
        } catch (RuntimeException $e) {
            // Tangkap exception (misal: stok tidak cukup) dan tampilkan alert
            $this->errorMessage = $e->getMessage();
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
    }

    /** Tutup modal sukses transaksi. */
    public function closeSuccessModal(): void
    {
        $this->showSuccessModal = false;
        $this->lastInvoiceNo = '';
        $this->lastSaleId = 0;
    }

    // ── Render ────────────────────────────────────────────────

    public function render(): View
    {
        return view('livewire.pos.kasir-page', [
            'searchResults' => $this->searchResults,
            'topMedicines' => $this->topMedicines,
            'categories' => $this->categories,
            'isFiltering' => $this->isFiltering,
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

    /** Reset state BPJS. */
    private function resetBpjs(): void
    {
        $this->bpjsNumber = '';
        $this->bpjsVerification = null;
        $this->bpjsVerified = false;

        // Reset flag is_fornas — sudah tidak relevan
        foreach ($this->cart as $index => $item) {
            $this->cart[$index]['is_fornas'] = true;
        }
    }

    /** Kosongkan seluruh keranjang dan reset form checkout. */
    private function resetCart(): void
    {
        $this->cart = [];
        $this->buyerName = '';
        $this->paymentMethod = 'cash';
        $this->discountAmount = 0;
        $this->taxEnabled = false;
        $this->errorMessage = null;
        $this->resetBpjs();
    }
}
