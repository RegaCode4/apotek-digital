<?php

namespace App\Livewire\Inventaris;

use App\Models\Medicine;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.sistem')]
#[Title('Daftar Obat')]
class MedicineIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $category = '';

    public string $requiresPrescription = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategory(): void
    {
        $this->resetPage();
    }

    public function updatingRequiresPrescription(): void
    {
        $this->resetPage();
    }

    /**
     * @return LengthAwarePaginator<int, Medicine>
     */
    public function getMedicinesProperty(): LengthAwarePaginator
    {
        return Medicine::query()
            ->when($this->search !== '', function (Builder $query): void {
                $query->where(function (Builder $query): void {
                    $query->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('generic_name', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->category !== '', fn (Builder $query): Builder => $query->where('category', $this->category))
            ->when($this->requiresPrescription !== '', function (Builder $query): void {
                $query->where('requires_prescription', $this->requiresPrescription === '1');
            })
            ->orderBy('name')
            ->paginate(15);
    }

    /**
     * @return Collection<int, string>
     */
    public function getCategoriesProperty(): Collection
    {
        return Medicine::query()
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');
    }

    public function render(): View
    {
        return view('livewire.inventaris.medicine-index', [
            'medicines' => $this->medicines,
            'categories' => $this->categories,
        ]);
    }
}
