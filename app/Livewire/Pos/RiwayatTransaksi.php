<?php

namespace App\Livewire\Pos;

use App\Models\Sale;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.sistem')]
#[Title('Riwayat Transaksi')]
/** Riwayat Transaksi — daftar penjualan dengan filter dan pagination. */
class RiwayatTransaksi extends Component
{
    use WithPagination;

    // ── Filter ────────────────────────────────────────────────
    public string $search = '';

    public string $paymentMethod = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    // ── State UI ─────────────────────────────────────────────
    /** @var int|null ID baris penjualan yang diperluas */
    public ?int $expandedSaleId = null;

    // ── Reset halaman saat filter berubah ─────────────────────

    /** Reset halaman saat filter search berubah. */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /** Reset halaman saat filter payment method berubah. */
    public function updatingPaymentMethod(): void
    {
        $this->resetPage();
    }

    /** Reset halaman saat filter tanggal awal berubah. */
    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    /** Reset halaman saat filter tanggal akhir berubah. */
    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    // ── Aksi ─────────────────────────────────────────────────

    /**
     * Membuka/menutup accordion detail untuk baris penjualan.
     * Mengklik baris yang sama akan menutupnya kembali.
     */
    public function toggleDetail(int $saleId): void
    {
        $this->expandedSaleId = $this->expandedSaleId === $saleId ? null : $saleId;
    }

    // ── Properti komputasi ────────────────────────────────────

    /**
     * @return LengthAwarePaginator<int, Sale>
     */
    #[Computed]
    public function sales(): LengthAwarePaginator
    {
        return $this->filteredQuery()
            ->with(['cashier', 'saleItems'])
            ->withCount('saleItems')
            ->orderByDesc('sale_date')
            ->paginate(20);
    }

    // ── Tampilan ───────────────────────────────────────────────

    /** Render halaman riwayat transaksi. */
    public function render(): View
    {
        return view('livewire.pos.riwayat-transaksi', [
            'sales' => $this->sales,
        ]);
    }

    // ── Pembantu ──────────────────────────────────────────────

    /**
     * @return Builder<Sale>
     */
    protected function filteredQuery(): Builder
    {
        // Ambil objek user yang sedang login untuk keperluan otorisasi filter
        /** @var User $user */
        $user = Auth::user();

        return Sale::query()
            // Aturan Otorisasi: Jika user adalah kasir (cashier), batasi hanya melihat transaksi yang dia input sendiri
            ->when($user->role === 'cashier', fn (Builder $q): Builder => $q->where('cashier_id', $user->id))

            // Filter Pencarian: Mencari berdasarkan nomor invoice atau nama pembeli jika form search diisi
            ->when(
                $this->search !== '',
                fn (Builder $q): Builder => $q->where(function (Builder $q): void {
                    $q->where('invoice_no', 'like', '%'.$this->search.'%')
                        ->orWhere('buyer_name', 'like', '%'.$this->search.'%');
                })
            )

            // Filter Metode Pembayaran: Hanya tampilkan data dengan metode pembayaran tertentu jika dipilih (cash/bpjs/dll)
            ->when(
                $this->paymentMethod !== '',
                fn (Builder $q): Builder => $q->where('payment_method', $this->paymentMethod)
            )

            // Filter Rentang Tanggal: Batasi transaksi mulai dari tanggal awal (dateFrom)
            ->when($this->dateFrom !== '', fn (Builder $q): Builder => $q->whereDate('sale_date', '>=', $this->dateFrom))

            // Filter Rentang Tanggal: Batasi transaksi sampai dengan tanggal akhir (dateTo)
            ->when($this->dateTo !== '', fn (Builder $q): Builder => $q->whereDate('sale_date', '<=', $this->dateTo));
    }
}
