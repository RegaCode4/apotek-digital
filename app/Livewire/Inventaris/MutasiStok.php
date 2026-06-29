<?php

namespace App\Livewire\Inventaris;

use App\Models\StockMutation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('layouts.sistem')]
#[Title('Riwayat Mutasi Stok')]
/** Riwayat Mutasi Stok — filter, search, pagination, dan export CSV. */
class MutasiStok extends Component
{
    use WithPagination;

    public string $type = '';

    public string $search = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    /** Mereset halaman saat filter tipe berubah. */
    public function updatingType(): void
    {
        $this->resetPage();
    }

    /** Mereset halaman saat filter pencarian berubah. */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /** Mereset halaman saat filter tanggal awal berubah. */
    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    /** Mereset halaman saat filter tanggal akhir berubah. */
    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    /** Ekspor data mutasi ke file CSV. */
    public function exportCsv(): StreamedResponse
    {
        $filename = 'mutasi-stok-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            fputcsv($handle, [
                'Tanggal',
                'Nama Obat',
                'Tipe',
                'Jumlah',
                'Referensi',
                'Catatan',
                'Dicatat Oleh',
            ]);

            $this->filteredQuery()
                ->orderByDesc('created_at')
                ->chunk(100, function ($mutations) use ($handle): void {
                    foreach ($mutations as $mutation) {
                        fputcsv($handle, [
                            $mutation->created_at?->format('Y-m-d H:i:s'),
                            $mutation->medicine?->name,
                            $this->typeLabel($mutation->type),
                            $mutation->quantity,
                            $mutation->reference_id,
                            $mutation->notes,
                            $mutation->createdBy?->name,
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * @return LengthAwarePaginator<int, StockMutation>
     */
    public function getMutationsProperty(): LengthAwarePaginator
    {
        return $this->filteredQuery()
            ->orderByDesc('created_at')
            ->paginate(20);
    }

    /** Ubah kode tipe ke label bahasa Indonesia. */
    public function typeLabel(string $type): string
    {
        return match ($type) {
            'in' => 'Masuk',
            'out' => 'Keluar',
            'adjustment' => 'Penyesuaian',
            'expired_return' => 'Retur Kedaluwarsa',
            default => $type,
        };
    }

    /** Menampilkan halaman riwayat mutasi stok. */
    public function render(): View
    {
        return view('livewire.inventaris.mutasi-stok', [
            'mutations' => $this->mutations,
        ]);
    }

    /**
     * @return Builder<StockMutation>
     */
    protected function filteredQuery(): Builder
    {
        return StockMutation::query()
            ->with(['medicine', 'createdBy'])
            ->when($this->type !== '', fn (Builder $query): Builder => $query->where('type', $this->type))
            ->when($this->search !== '', function (Builder $query): void {
                $query->whereHas('medicine', function (Builder $query): void {
                    $query->where('name', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->dateFrom !== '', fn (Builder $query): Builder => $query->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo !== '', fn (Builder $query): Builder => $query->whereDate('created_at', '<=', $this->dateTo));
    }
}
