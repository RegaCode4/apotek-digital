<?php

namespace App\Livewire\Inventaris;

use App\Models\Category;
use App\Models\Medicine;
use App\Models\StockMutation;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.sistem')]
#[Title('Stok Opname')]
/** Stok Opname — penyesuaian stok fisik seluruh obat. */
class StokOpname extends Component
{
    use WithPagination;

    /** @var array<int, int|string> */
    public array $physicalStocks = [];

    /** @var array<int, string> */
    public array $itemReasons = [];

    public string $reason = '';

    public ?string $successMessage = null;

    /** @var string Filter status: all, pending, match, diff */
    public string $filterStatus = 'all';

    /** @var string ID Kategori untuk filter */
    public string $filterCategoryId = '';

    /** @var string Kata kunci pencarian nama obat */
    public string $search = '';

    public bool $hasDraft = false;

    /**
     * Mereset paginasi saat kata kunci pencarian diperbarui.
     */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Mereset paginasi saat filter status diperbarui.
     */
    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    /**
     * Mereset paginasi saat filter kategori diperbarui.
     */
    public function updatingFilterCategoryId(): void
    {
        $this->resetPage();
    }

    /**
     * Dipanggil saat komponen pertama kali diinisialisasi.
     * Memuat draf stok opname (jika ada) dari cache.
     */
    public function mount(): void
    {
        $draft = Cache::get('stock_opname_draft_'.Auth::id());
        if ($draft) {
            $this->physicalStocks = $draft['physicalStocks'] ?? [];
            $this->itemReasons = $draft['itemReasons'] ?? [];
            $this->reason = $draft['reason'] ?? '';
            $this->hasDraft = true;
        }
    }

    /**
     * Menyimpan inputan stok saat ini sebagai draf sementara di cache.
     * Draf berlaku selama 7 hari.
     */
    public function saveDraft(): void
    {
        Cache::put('stock_opname_draft_'.Auth::id(), [
            'physicalStocks' => $this->physicalStocks,
            'itemReasons' => $this->itemReasons,
            'reason' => $this->reason,
        ], now()->addDays(7));

        $this->hasDraft = true;

        $this->successMessage = 'Draf Stok Opname berhasil disimpan sementara (berlaku 7 hari).';
        session()->flash('success', $this->successMessage);
    }

    /**
     * Membuang (menghapus) draf stok opname dari cache
     * dan mereset sesi SO ke awal.
     */
    public function discardDraft(): void
    {
        Cache::forget('stock_opname_draft_'.Auth::id());
        $this->physicalStocks = [];
        $this->itemReasons = [];
        $this->reason = '';
        $this->hasDraft = false;

        $this->successMessage = 'Draf berhasil dibuang. Sesi SO di-reset.';
        session()->flash('success', $this->successMessage);
    }

    /** Simpan semua penyesuaian stok sekaligus (Tulis ke Ledger Mutasi). */
    public function saveAllAdjustments(): void
    {
        $this->validate([
            'reason' => ['required', 'string', 'min:3'],
            'physicalStocks.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        $filledMedicineIds = collect($this->physicalStocks)
            ->filter(fn ($val) => $val !== '' && $val !== null)
            ->keys();

        if ($filledMedicineIds->isEmpty()) {
            $this->addError('reason', 'Belum ada obat yang dihitung. Isi setidaknya satu obat.');

            return;
        }

        $medicines = Medicine::whereIn('id', $filledMedicineIds)->get();
        $adjustedCount = 0;
        $timestamp = now(); // Exact timestamp for grouping

        DB::transaction(function () use ($medicines, &$adjustedCount, $timestamp): void {
            foreach ($medicines as $medicine) {
                $physicalStock = (int) $this->physicalStocks[$medicine->id];
                $difference = $physicalStock - $medicine->stock;

                if ($difference === 0) {
                    continue; // Stok sesuai, tidak perlu record penyesuaian
                }

                $itemReason = ! empty($this->itemReasons[$medicine->id]) ? $this->itemReasons[$medicine->id] : 'Penyesuaian SO';

                // Tulis ke Ledger Mutasi Stok dengan timestamp absolut
                $mutation = new StockMutation([
                    'medicine_id' => $medicine->id,
                    'type' => 'adjustment',
                    'quantity' => $difference,
                    'notes' => "SO: {$itemReason} | Ref: {$this->reason}",
                    'created_by' => Auth::id(),
                ]);
                $mutation->timestamps = false; // Disable auto timestamp
                $mutation->created_at = $timestamp;
                $mutation->updated_at = $timestamp;
                $mutation->save();

                // Update kolom stok cache di tabel medicines
                $medicine->update(['stock' => $physicalStock]);

                $adjustedCount++;
            }
        });

        $this->successMessage = "SO Selesai! {$adjustedCount} penyesuaian stok dicatat di ledger.";
        session()->flash('success', $this->successMessage);
        session()->flash('last_so_timestamp', $timestamp->timestamp); // For PDF print

        // Reset state sesi
        $this->reason = '';
        $this->physicalStocks = [];
        $this->itemReasons = [];
        $this->filterStatus = 'all';
        $this->hasDraft = false;
        Cache::forget('stock_opname_draft_'.Auth::id());
    }

    /** Mendapatkan list obat tersaring dengan paginasi manual di collection. */
    public function getMedicinesProperty(): LengthAwarePaginator
    {
        $query = Medicine::query()
            ->with('category')
            ->when($this->filterCategoryId, fn ($q) => $q->where('category_id', $this->filterCategoryId))
            ->when($this->search, fn ($q) => $q->where('name', 'like', '%'.$this->search.'%'));

        $medicines = $query->get();

        // Sort Default: Alfabetis Natural -> Kategori (stable sort: hasil akhir terurut Kategori, lalu Nama)
        $medicines = $medicines
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->sortBy(fn ($medicine) => $medicine->category?->name ?? 'Z');

        if ($this->filterStatus !== 'all') {
            $medicines = $medicines->filter(function ($medicine) {
                $phys = $this->physicalStocks[$medicine->id] ?? null;
                $hasInput = ($phys !== null && $phys !== '');

                if ($this->filterStatus === 'pending') {
                    return ! $hasInput;
                }
                if ($this->filterStatus === 'match') {
                    return $hasInput && ((int) $phys === $medicine->stock);
                }
                if ($this->filterStatus === 'diff') {
                    return $hasInput && ((int) $phys !== $medicine->stock);
                }

                return true;
            });
        }

        // Re-index array setelah difilter
        $medicines = $medicines->values();

        // Pagination Manual Collection
        $currentPage = Paginator::resolveCurrentPage() ?: 1;
        $perPage = 50; // Jumlah baris per halaman
        $paginatedItems = $medicines->forPage($currentPage, $perPage);

        // Inisialisasi key array untuk mencegah error Livewire @entangle di frontend
        foreach ($paginatedItems as $medicine) {
            if (! array_key_exists($medicine->id, $this->physicalStocks)) {
                $this->physicalStocks[$medicine->id] = null;
            }

            if (! array_key_exists($medicine->id, $this->itemReasons)) {
                $this->itemReasons[$medicine->id] = '';
            }
        }

        return new LengthAwarePaginator(
            $paginatedItems,
            $medicines->count(),
            $perPage,
            $currentPage,
            ['path' => Paginator::resolveCurrentPath(), 'pageName' => 'page']
        );
    }

    /** Mendapatkan kategori untuk filter. */
    public function getCategoriesProperty(): Collection
    {
        return Category::query()->orderBy('name')->get();
    }

    /** Mendapatkan waktu SO terakhir. */
    public function getLastOpnameAtProperty(): ?Carbon
    {
        $lastOpname = StockMutation::query()
            ->where('type', 'adjustment')
            ->latest('created_at')
            ->value('created_at');

        return $lastOpname ? Carbon::parse($lastOpname) : null;
    }

    /**
     * Merender tampilan komponen stok opname.
     */
    public function render(): View
    {
        return view('livewire.inventaris.stok-opname', [
            'medicines' => $this->medicines,
            'categories' => $this->categories,
            'lastOpnameAt' => $this->lastOpnameAt,
        ]);
    }
}
