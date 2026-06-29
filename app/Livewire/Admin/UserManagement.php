<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.sistem')]
#[Title('Manajemen User')]
/** Halaman manajemen user dengan CRUD dan pencarian */
class UserManagement extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;

    /** null = create mode, int = edit mode */
    public ?int $editingUserId = null;

    public string $formName = '';

    public string $formEmail = '';

    public string $formPassword = '';

    public string $formRole = 'cashier';

    public bool $formIsActive = true;

    /** Mereset paginasi saat pencarian berubah */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /** Mengganti status aktif user, mencegah nonaktif diri sendiri */
    public function toggleActive(int $userId): void
    {
        if ($userId === Auth::id()) {
            $this->dispatch('notify', type: 'error', message: 'Anda tidak bisa menonaktifkan akun Anda sendiri.');

            return;
        }

        $user = User::findOrFail($userId);
        $user->update(['is_active' => ! $user->is_active]);

        $status = $user->fresh()->is_active ? 'diaktifkan' : 'dinonaktifkan';
        $this->dispatch('notify', type: 'success', message: "User {$user->name} berhasil {$status}.");
    }

    /** Mereset password user ke default */
    public function resetPassword(int $userId): void
    {
        $user = User::findOrFail($userId);
        $user->update(['password' => Hash::make('password123')]);

        $this->dispatch('notify', type: 'warning', message: "Password {$user->name} direset ke 'password123'. Minta user segera ganti password.");
    }

    /** Membuka modal untuk membuat user baru */
    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->editingUserId = null;
        $this->showModal = true;
    }

    /** Membuka modal untuk mengedit user yang sudah ada */
    public function openEditModal(int $userId): void
    {
        $user = User::findOrFail($userId);

        $this->editingUserId = $user->id;
        $this->formName = $user->name;
        $this->formEmail = $user->email;
        $this->formPassword = '';
        $this->formRole = $user->role;
        $this->formIsActive = (bool) $user->is_active;
        $this->resetValidation();
        $this->showModal = true;
    }

    /** Membuat atau memperbarui user dari form modal */
    public function save(): void
    {
        $rules = $this->editingUserId
            ? $this->editRules()
            : $this->createRules();

        $validated = $this->validate($rules);

        if ($this->editingUserId) {
            $user = User::findOrFail($this->editingUserId);

            if ($this->editingUserId === Auth::id() && ! $validated['formIsActive']) {
                $this->addError('formIsActive', 'Anda tidak bisa menonaktifkan akun Anda sendiri.');

                return;
            }

            $user->update([
                'name' => $validated['formName'],
                'email' => $validated['formEmail'],
                'role' => $validated['formRole'],
                'is_active' => $validated['formIsActive'],
            ]);

            $this->dispatch('notify', type: 'success', message: "User {$user->name} berhasil diperbarui.");
        } else {
            $user = User::create([
                'name' => $validated['formName'],
                'email' => $validated['formEmail'],
                'password' => Hash::make($validated['formPassword']),
                'role' => $validated['formRole'],
                'is_active' => true,
            ]);

            $this->dispatch('notify', type: 'success', message: "User {$user->name} berhasil ditambahkan.");
        }

        $this->showModal = false;
        $this->resetForm();
        $this->resetPage();
    }

    /** Menutup modal tanpa menyimpan */
    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
        $this->resetValidation();
    }

    /** Daftar user dengan paginasi dan filter pencarian */
    public function getUsersProperty(): LengthAwarePaginator
    {
        return User::query()
            ->when($this->search !== '', function (Builder $q): void {
                $q->where(function (Builder $q): void {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            })
            ->orderByDesc('created_at')
            ->paginate(15);
    }

    /** Menampilkan tampilan manajemen user */
    public function render(): View
    {
        return view('livewire.admin.user-management', [
            'users' => $this->users,
        ]);
    }

    /** Mereset semua field form ke nilai default */
    private function resetForm(): void
    {
        $this->editingUserId = null;
        $this->formName = '';
        $this->formEmail = '';
        $this->formPassword = '';
        $this->formRole = 'cashier';
        $this->formIsActive = true;
    }

    /** Aturan validasi untuk membuat user baru */
    private function createRules(): array
    {
        return [
            'formName' => ['required', 'string', 'max:100'],
            'formEmail' => ['required', 'email', 'max:150', 'unique:users,email'],
            'formPassword' => ['required', 'string', 'min:8'],
            'formRole' => ['required', 'in:admin,pharmacist,cashier'],
        ];
    }

    /** Aturan validasi untuk mengedit user yang sudah ada */
    private function editRules(): array
    {
        return [
            'formName' => ['required', 'string', 'max:100'],
            'formEmail' => ['required', 'email', 'max:150', "unique:users,email,{$this->editingUserId}"],
            'formRole' => ['required', 'in:admin,pharmacist,cashier'],
            'formIsActive' => ['boolean'],
        ];
    }
}
