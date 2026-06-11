<?php

namespace App\Livewire\Pos;

use App\Models\Sale;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.sistem')]
#[Title('Riwayat Transaksi')]
class RiwayatTransaksi extends Component
{
    use WithPagination;

    // ── Filters ──────────────────────────────────────────────
    public string $search = '';

    public string $paymentMethod = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    // ── UI state ─────────────────────────────────────────────
    /** @var int|null ID of the expanded sale row */
    public ?int $expandedSaleId = null;

    // ── Reset page on filter change ───────────────────────────

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPaymentMethod(): void
    {
        $this->resetPage();
    }

    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    // ── Actions ───────────────────────────────────────────────

    /**
     * Toggle the detail accordion for a sale row.
     * Clicking the same row again collapses it.
     */
    public function toggleDetail(int $saleId): void
    {
        $this->expandedSaleId = $this->expandedSaleId === $saleId ? null : $saleId;
    }

    // ── Computed properties ───────────────────────────────────

    /**
     * @return LengthAwarePaginator<int, Sale>
     */
    public function getSalesProperty(): LengthAwarePaginator
    {
        return $this->filteredQuery()
            ->with(['cashier', 'saleItems'])
            ->withCount('saleItems')
            ->orderByDesc('sale_date')
            ->paginate(20);
    }

    // ── Render ────────────────────────────────────────────────

    public function render(): View
    {
        return view('livewire.pos.riwayat-transaksi', [
            'sales' => $this->sales,
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────

    /**
     * @return Builder<Sale>
     */
    protected function filteredQuery(): Builder
    {
        /** @var User $user */
        $user = Auth::user();

        return Sale::query()
            // Cashier hanya bisa lihat transaksi miliknya
            ->when($user->role === 'cashier', fn (Builder $q): Builder => $q->where('cashier_id', $user->id))
            ->when(
                $this->search !== '',
                fn (Builder $q): Builder => $q->where(function (Builder $q): void {
                    $q->where('invoice_no', 'like', '%'.$this->search.'%')
                        ->orWhere('buyer_name', 'like', '%'.$this->search.'%');
                })
            )
            ->when(
                $this->paymentMethod !== '',
                fn (Builder $q): Builder => $q->where('payment_method', $this->paymentMethod)
            )
            ->when($this->dateFrom !== '', fn (Builder $q): Builder => $q->whereDate('sale_date', '>=', $this->dateFrom))
            ->when($this->dateTo !== '', fn (Builder $q): Builder => $q->whereDate('sale_date', '<=', $this->dateTo));
    }
}
