<?php

namespace App\Livewire\Inventaris;

use App\Models\Category;
use App\Models\Medicine;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.sistem')]
#[Title('Daftar Obat')]
/** Daftar Obat — manajemen master obat dengan filter, pagination, dan hapus. */
class MedicineIndex extends Component
{
    use WithPagination;

    public string $search = '';

    /** Filter berdasarkan category_id (FK ke tabel categories). */
    public string $categoryId = '';

    public string $requiresPrescription = '';

    public ?string $successMessage = null;

    public ?string $errorMessage = null;

    public bool $showDeleteModal = false;

    public ?int $deleteMedicineId = null;

    public string $deleteMedicineName = '';

    /** Mereset halaman saat filter pencarian berubah. */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /** Reset halaman saat filter kategori berubah. */
    public function updatingCategoryId(): void
    {
        $this->resetPage();
    }

    /** Reset halaman saat filter resep berubah. */
    public function updatingRequiresPrescription(): void
    {
        $this->resetPage();
    }

    /** Reset filter ke kondisi awal. */
    public function resetFilter(): void
    {
        $this->search = '';
        $this->categoryId = '';
        $this->requiresPrescription = '';
        $this->resetPage();
    }

    /** Memuat ulang data setelah form obat ditutup. */
    #[On('medicine-saved')]
    public function refreshMedicines(?string $message = null): void
    {
        if ($message !== null) {
            $this->successMessage = $message;
            session()->flash('success', $message);
        }

        $this->resetPage();
    }

    /** Tampilkan konfirmasi hapus obat. */
    public function confirmDelete(int $id): void
    {
        $medicine = Medicine::query()->findOrFail($id);

        $this->errorMessage = null;
        $this->deleteMedicineId = $medicine->id;
        $this->deleteMedicineName = $medicine->name;
        $this->showDeleteModal = true;
    }

    /** Hapus obat dari database. */
    public function deleteConfirmed(): void
    {
        if ($this->deleteMedicineId === null) {
            return;
        }

        $medicine = Medicine::query()->findOrFail($this->deleteMedicineId);

        if ($this->hasSaleItems($medicine)) {
            $this->errorMessage = 'Obat tidak bisa dihapus karena sudah pernah dijual. Nonaktifkan saja.';
            $this->resetDeleteState();

            return;
        }

        $medicine->stockMutations()->delete();
        $medicine->delete();

        $this->successMessage = 'Obat berhasil dihapus.';
        session()->flash('success', $this->successMessage);
        $this->resetDeleteState();
        $this->resetPage();
    }

    /**
     * @return LengthAwarePaginator<int, Medicine>
     */
    public function getMedicinesProperty(): LengthAwarePaginator
    {
        return Medicine::query()
            ->with('category')
            ->when($this->search !== '', function (Builder $query): void {
                $query->where(function (Builder $query): void {
                    $query->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('generic_name', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->categoryId !== '', fn (Builder $query): Builder => $query->where('category_id', $this->categoryId))
            ->when($this->requiresPrescription !== '', function (Builder $query): void {
                $query->where('requires_prescription', $this->requiresPrescription === '1');
            })
            ->orderBy('name')
            ->paginate(15);
    }

    /**
     * Memuat semua kategori dari tabel categories untuk filter dropdown.
     *
     * @return Collection<int, Category>
     */
    public function getCategoriesProperty(): Collection
    {
        return Category::query()
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /** Menampilkan halaman daftar obat. */
    public function render(): View
    {
        return view('livewire.inventaris.medicine-index', [
            'medicines' => $this->medicines,
            'categories' => $this->categories,
        ]);
    }

    /** Cek apakah obat sudah pernah dijual. */
    protected function hasSaleItems(Medicine $medicine): bool
    {
        if (! Schema::hasTable('sale_items')) {
            return false;
        }

        return DB::table('sale_items')->where('medicine_id', $medicine->id)->exists();
    }

    /** Mereset state modal hapus. */
    protected function resetDeleteState(): void
    {
        $this->showDeleteModal = false;
        $this->deleteMedicineId = null;
        $this->deleteMedicineName = '';
    }
}
