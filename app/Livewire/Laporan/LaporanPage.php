<?php

namespace App\Livewire\Laporan;

use App\Models\Medicine;
use App\Models\Sale;
use App\Models\StockMutation;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('layouts.sistem')]
#[Title('Laporan')]
class LaporanPage extends Component
{
    use WithPagination;

    public string $activeTab = 'penjualan';

    public string $dateFrom = '';

    public string $dateTo = '';

    public string $paymentMethod = '';

    public string $mutationType = '';

    public function mount(): void
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->endOfMonth()->format('Y-m-d');
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->paymentMethod = '';
        $this->mutationType = '';
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

    public function updatingPaymentMethod(): void
    {
        $this->resetPage();
    }

    public function updatingMutationType(): void
    {
        $this->resetPage();
    }

    /**
     * @return LengthAwarePaginator<int, Sale>
     */
    public function getSalesProperty(): LengthAwarePaginator
    {
        return $this->salesFilteredQuery()
            ->with('cashier')
            ->withCount('saleItems')
            ->orderByDesc('sale_date')
            ->paginate(20);
    }

    /**
     * @return array{total_transaksi: int, total_pendapatan: float|int}
     */
    public function getSalesSummaryProperty(): array
    {
        $result = $this->salesFilteredQuery()
            ->selectRaw('COUNT(*) as total_transaksi, SUM(grand_total) as total_pendapatan')
            ->first();

        return [
            'total_transaksi' => $result?->total_transaksi ?? 0,
            'total_pendapatan' => $result?->total_pendapatan ?? 0,
        ];
    }

    public function exportSalesCsv(): StreamedResponse
    {
        $filename = 'laporan-penjualan-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            fputcsv($handle, [
                'No. Invoice',
                'Tanggal',
                'Pembeli',
                'Kasir',
                'Total Item',
                'Grand Total',
                'Metode Bayar',
            ]);

            $this->salesFilteredQuery()
                ->with('cashier')
                ->withCount('saleItems')
                ->orderByDesc('sale_date')
                ->chunk(100, function ($sales) use ($handle): void {
                    foreach ($sales as $sale) {
                        fputcsv($handle, [
                            $sale->invoice_no,
                            $sale->sale_date->format('Y-m-d H:i:s'),
                            $sale->buyer_name,
                            $sale->cashier?->name,
                            $sale->sale_items_count,
                            $sale->grand_total,
                            strtoupper($sale->payment_method),
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * @return Builder<Sale>
     */
    protected function salesFilteredQuery(): Builder
    {
        return Sale::query()
            ->when($this->dateFrom !== '', fn (Builder $q): Builder => $q->whereDate('sale_date', '>=', $this->dateFrom))
            ->when($this->dateTo !== '', fn (Builder $q): Builder => $q->whereDate('sale_date', '<=', $this->dateTo))
            ->when($this->paymentMethod !== '', fn (Builder $q): Builder => $q->where('payment_method', $this->paymentMethod));
    }

    /**
     * @return LengthAwarePaginator<int, StockMutation>
     */
    public function getMutationsProperty(): LengthAwarePaginator
    {
        return $this->mutationsFilteredQuery()
            ->with(['medicine', 'createdBy'])
            ->orderByDesc('created_at')
            ->paginate(20);
    }

    /**
     * @return array{total_masuk: int|float, total_keluar: int|float}
     */
    public function getMutationsSummaryProperty(): array
    {
        $result = $this->mutationsFilteredQuery()
            ->selectRaw("
                SUM(CASE WHEN type = 'in' THEN quantity ELSE 0 END) as total_masuk,
                SUM(CASE WHEN type = 'out' THEN ABS(quantity) ELSE 0 END) as total_keluar
            ")
            ->first();

        return [
            'total_masuk' => $result?->total_masuk ?? 0,
            'total_keluar' => $result?->total_keluar ?? 0,
        ];
    }

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

    public function exportMutationsCsv(): StreamedResponse
    {
        $filename = 'laporan-stok-mutasi-'.now()->format('Y-m-d-His').'.csv';

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
                'Keterangan',
                'Dicatat Oleh',
            ]);

            $this->mutationsFilteredQuery()
                ->with(['medicine', 'createdBy'])
                ->orderByDesc('created_at')
                ->chunk(100, function ($mutations) use ($handle): void {
                    foreach ($mutations as $mutation) {
                        fputcsv($handle, [
                            $mutation->created_at?->format('Y-m-d H:i:s'),
                            $mutation->medicine?->name,
                            $this->typeLabel($mutation->type),
                            $mutation->quantity,
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
     * @return Builder<StockMutation>
     */
    protected function mutationsFilteredQuery(): Builder
    {
        return StockMutation::query()
            ->when($this->mutationType !== '', fn (Builder $q): Builder => $q->where('type', $this->mutationType))
            ->when($this->dateFrom !== '', fn (Builder $q): Builder => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo !== '', fn (Builder $q): Builder => $q->whereDate('created_at', '<=', $this->dateTo));
    }

    public function getPaymentSummaryProperty(): Collection
    {
        return Sale::query()
            ->when($this->dateFrom !== '', fn (Builder $q): Builder => $q->whereDate('sale_date', '>=', $this->dateFrom))
            ->when($this->dateTo !== '', fn (Builder $q): Builder => $q->whereDate('sale_date', '<=', $this->dateTo))
            ->selectRaw('payment_method, COUNT(*) as jumlah_transaksi, SUM(grand_total) as total_nominal')
            ->groupBy('payment_method')
            ->get()
            ->keyBy('payment_method');
    }

    public function getDailyBreakdownProperty(): Collection
    {
        return Sale::query()
            ->when($this->dateFrom !== '', fn (Builder $q): Builder => $q->whereDate('sale_date', '>=', $this->dateFrom))
            ->when($this->dateTo !== '', fn (Builder $q): Builder => $q->whereDate('sale_date', '<=', $this->dateTo))
            ->selectRaw('DATE(sale_date) as tanggal, payment_method, SUM(grand_total) as total')
            ->groupBy('tanggal', 'payment_method')
            ->orderBy('tanggal')
            ->get()
            ->groupBy('tanggal');
    }

    public function exportPaymentCsv(): StreamedResponse
    {
        $filename = 'laporan-pendapatan-metode-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            fputcsv($handle, ['Tanggal', 'Cash', 'Transfer', 'BPJS', 'Asuransi', 'Total']);

            foreach ($this->dailyBreakdown as $tanggal => $rows) {
                $byMethod = $rows->keyBy('payment_method');
                $cash = $byMethod->get('cash')?->total ?? 0;
                $transfer = $byMethod->get('transfer')?->total ?? 0;
                $bpjs = $byMethod->get('bpjs')?->total ?? 0;
                $insurance = $byMethod->get('insurance')?->total ?? 0;
                $dayTotal = $cash + $transfer + $bpjs + $insurance;

                fputcsv($handle, [$tanggal, $cash, $transfer, $bpjs, $insurance, $dayTotal]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function getExpiringMedicinesProperty(): Collection
    {
        return Medicine::expiringSoon(3)
            ->where('expiry_date', '>=', now())
            ->with('category')
            ->orderBy('expiry_date')
            ->get();
    }

    public function getLowStockMedicinesProperty(): Collection
    {
        return Medicine::lowStock()
            ->with('category')
            ->orderBy('stock')
            ->get();
    }

    public function expiryBadgeClass(string $expiryDate): string
    {
        $date = Carbon::parse($expiryDate);
        if ($date->lt(now()->addDays(30))) {
            return 'bg-red-100 text-red-700';
        }

        return 'bg-amber-100 text-amber-800';
    }

    public function exportExpiringCsv(): StreamedResponse
    {
        $filename = 'laporan-obat-kedaluwarsa-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            fputcsv($handle, ['Nama Obat', 'Nama Generik', 'Kategori', 'Stok', 'Tanggal Kedaluwarsa', 'Sisa Hari']);

            foreach ($this->expiringMedicines as $medicine) {
                fputcsv($handle, [
                    $medicine->name,
                    $medicine->generic_name,
                    $medicine->category?->name,
                    $medicine->stock,
                    $medicine->expiry_date?->format('Y-m-d'),
                    (int) now()->diffInDays($medicine->expiry_date, false),
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function exportLowStockCsv(): StreamedResponse
    {
        $filename = 'laporan-low-stock-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            fputcsv($handle, ['Nama Obat', 'Nama Generik', 'Kategori', 'Stok Saat Ini', 'Min. Stok']);

            foreach ($this->lowStockMedicines as $medicine) {
                fputcsv($handle, [
                    $medicine->name,
                    $medicine->generic_name,
                    $medicine->category?->name,
                    $medicine->stock,
                    $medicine->min_stock,
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function render(): View
    {
        $data = [];

        if ($this->activeTab === 'penjualan') {
            $data = [
                'sales' => $this->sales,
                'salesSummary' => $this->salesSummary,
            ];
        } elseif ($this->activeTab === 'stok') {
            $data = [
                'mutations' => $this->mutations,
                'mutationsSummary' => $this->mutationsSummary,
            ];
        } elseif ($this->activeTab === 'pendapatan') {
            $data = [
                'paymentSummary' => $this->paymentSummary,
                'dailyBreakdown' => $this->dailyBreakdown,
            ];
        } elseif ($this->activeTab === 'kedaluwarsa') {
            $data = [
                'expiringMedicines' => $this->expiringMedicines,
                'lowStockMedicines' => $this->lowStockMedicines,
            ];
        }

        return view('livewire.laporan.laporan-page', $data);
    }
}
