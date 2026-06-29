<?php

namespace App\Livewire\Inventaris;

use App\Models\Medicine;
use App\Models\StockMutation;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.sistem')]
#[Title('Stok Opname')]
/** Stok Opname — penyesuaian stok fisik seluruh obat. */
class StokOpname extends Component
{
    /** @var array<int, int> */
    public array $physicalStocks = [];

    public string $reason = '';

    public ?string $successMessage = null;

    /** Inisialisasi stok fisik dari nilai stok database. */
    public function mount(): void
    {
        $this->initializePhysicalStocks();
    }

    /** Simpan semua penyesuaian stok sekaligus. */
    public function saveAllAdjustments(): void
    {
        $this->validate([
            'reason' => ['required', 'string', 'min:3'],
            'physicalStocks.*' => ['required', 'integer', 'min:0'],
        ]);

        $medicines = $this->medicines();
        $adjustedCount = 0;

        DB::transaction(function () use ($medicines, &$adjustedCount): void {
            foreach ($medicines as $medicine) {
                $physicalStock = (int) ($this->physicalStocks[$medicine->id] ?? $medicine->stock);
                $difference = $physicalStock - $medicine->stock;

                if ($difference === 0) {
                    continue;
                }

                $medicine->update(['stock' => $physicalStock]);

                StockMutation::query()->create([
                    'medicine_id' => $medicine->id,
                    'type' => 'adjustment',
                    'quantity' => $difference,
                    'notes' => $this->reason,
                    'created_by' => Auth::id(),
                ]);

                $adjustedCount++;
            }
        });

        if ($adjustedCount === 0) {
            $this->addError('reason', 'Tidak ada perubahan stok untuk disimpan.');

            return;
        }

        $this->successMessage = "Berhasil menyimpan {$adjustedCount} penyesuaian stok.";
        $this->dispatch('notify', type: 'success', message: $this->successMessage);
        $this->reason = '';
        $this->initializePhysicalStocks();
    }

    /**
     * @return Collection<int, Medicine>
     */
    public function getMedicinesProperty(): Collection
    {
        return $this->medicines();
    }

    /** Waktu opname terakhir dari mutasi tipe adjustment. */
    public function getLastOpnameAtProperty(): ?Carbon
    {
        $lastOpname = StockMutation::query()
            ->where('type', 'adjustment')
            ->latest('created_at')
            ->value('created_at');

        return $lastOpname ? Carbon::parse($lastOpname) : null;
    }

    /** Menampilkan halaman stok opname. */
    public function render(): View
    {
        return view('livewire.inventaris.stok-opname', [
            'medicines' => $this->medicines,
            'lastOpnameAt' => $this->lastOpnameAt,
        ]);
    }

    /**
     * @return Collection<int, Medicine>
     */
    protected function medicines(): Collection
    {
        return Medicine::query()->orderBy('name')->get();
    }

    /** Mengatur stok fisik awal sama dengan stok database. */
    protected function initializePhysicalStocks(): void
    {
        $this->physicalStocks = $this->medicines()
            ->mapWithKeys(fn (Medicine $medicine): array => [$medicine->id => $medicine->stock])
            ->all();
    }
}
