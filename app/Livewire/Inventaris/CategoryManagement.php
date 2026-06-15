<?php

namespace App\Livewire\Inventaris;

use App\Models\Category;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.sistem')]
#[Title('Manajemen Kategori Obat')]
class CategoryManagement extends Component
{
    use WithPagination;

    // ── Filter ─────────────────────────────────────────────────
    public string $search = '';

    // ── Modal state ────────────────────────────────────────────
    public bool $showModal = false;

    public bool $showDeleteConfirm = false;

    /** null = create mode, int = edit mode */
    public ?int $editingId = null;

    /** ID category yang akan dihapus */
    public ?int $deletingId = null;

    // ── Form fields ────────────────────────────────────────────
    public string $formName = '';

    public string $formDescription = '';

    // ── Watchers ──────────────────────────────────────────────

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    // ── Actions — modal ───────────────────────────────────────

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->editingId = null;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function openEditModal(int $categoryId): void
    {
        $category = Category::findOrFail($categoryId);

        $this->editingId = $category->id;
        $this->formName = $category->name;
        $this->formDescription = $category->description ?? '';
        $this->resetValidation();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
        $this->resetValidation();
    }

    public function save(): void
    {
        $rules = [
            'formName' => [
                'required',
                'string',
                'max:100',
                $this->editingId
                    ? "unique:categories,name,{$this->editingId}"
                    : 'unique:categories,name',
            ],
            'formDescription' => ['nullable', 'string'],
        ];

        $this->validate($rules, [
            'formName.required' => 'Nama kategori wajib diisi.',
            'formName.max' => 'Nama kategori maksimal 100 karakter.',
            'formName.unique' => 'Nama kategori sudah digunakan.',
        ]);

        if ($this->editingId) {
            $category = Category::findOrFail($this->editingId);
            $category->update([
                'name' => $this->formName,
                'description' => $this->formDescription ?: null,
            ]);

            $this->dispatch('notify', type: 'success', message: "Kategori \"{$category->name}\" berhasil diperbarui.");
        } else {
            $category = Category::create([
                'name' => $this->formName,
                'description' => $this->formDescription ?: null,
            ]);

            $this->dispatch('notify', type: 'success', message: "Kategori \"{$category->name}\" berhasil ditambahkan.");
        }

        $this->showModal = false;
        $this->resetForm();
        $this->resetPage();
    }

    // ── Actions — delete ──────────────────────────────────────

    public function confirmDelete(int $categoryId): void
    {
        $this->deletingId = $categoryId;
        $this->showDeleteConfirm = true;
    }

    public function cancelDelete(): void
    {
        $this->deletingId = null;
        $this->showDeleteConfirm = false;
    }

    public function delete(): void
    {
        if (! $this->deletingId) {
            return;
        }

        $category = Category::findOrFail($this->deletingId);

        if ($category->medicines()->exists()) {
            $this->dispatch('notify', type: 'error', message: "Kategori \"{$category->name}\" tidak dapat dihapus karena masih digunakan oleh {$category->medicines()->count()} obat.");
            $this->showDeleteConfirm = false;
            $this->deletingId = null;

            return;
        }

        $name = $category->name;
        $category->delete();

        $this->dispatch('notify', type: 'success', message: "Kategori \"{$name}\" berhasil dihapus.");
        $this->showDeleteConfirm = false;
        $this->deletingId = null;
        $this->resetPage();
    }

    // ── Render ────────────────────────────────────────────────

    public function render(): View
    {
        $categories = Category::query()
            ->when($this->search !== '', function ($q): void {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%');
            })
            ->withCount('medicines')
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.inventaris.category-management', [
            'categories' => $categories,
        ]);
    }

    // ── Private helpers ───────────────────────────────────────

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->formName = '';
        $this->formDescription = '';
    }
}
