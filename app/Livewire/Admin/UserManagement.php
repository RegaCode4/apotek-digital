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
class UserManagement extends Component
{
    use WithPagination;

    // ── Filter ───────────────────────────────────────────────
    public string $search = '';

    // ── Modal state ───────────────────────────────────────────
    public bool $showModal = false;

    /** null = create mode, int = edit mode */
    public ?int $editingUserId = null;

    // ── Form fields ───────────────────────────────────────────
    public string $formName = '';

    public string $formEmail = '';

    public string $formPassword = '';

    public string $formRole = 'cashier';

    public bool $formIsActive = true;

    // ── Watchers ─────────────────────────────────────────────

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    // ── Actions — table ───────────────────────────────────────

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

    public function resetPassword(int $userId): void
    {
        $user = User::findOrFail($userId);
        $user->update(['password' => Hash::make('password123')]);

        $this->dispatch('notify', type: 'warning', message: "Password {$user->name} direset ke 'password123'. Minta user segera ganti password.");
    }

    // ── Actions — modal ───────────────────────────────────────

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->editingUserId = null;
        $this->showModal = true;
    }

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

    public function save(): void
    {
        $rules = $this->editingUserId
            ? $this->editRules()
            : $this->createRules();

        $validated = $this->validate($rules);

        if ($this->editingUserId) {
            $user = User::findOrFail($this->editingUserId);

            // Prevent admin from deactivating themselves via form
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

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
        $this->resetValidation();
    }

    // ── Computed ─────────────────────────────────────────────

    /**
     * @return LengthAwarePaginator<int, User>
     */
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

    // ── Render ────────────────────────────────────────────────

    public function render(): View
    {
        return view('livewire.admin.user-management', [
            'users' => $this->users,
        ]);
    }

    // ── Private helpers ───────────────────────────────────────

    private function resetForm(): void
    {
        $this->editingUserId = null;
        $this->formName = '';
        $this->formEmail = '';
        $this->formPassword = '';
        $this->formRole = 'cashier';
        $this->formIsActive = true;
    }

    /**
     * @return array<string, mixed>
     */
    private function createRules(): array
    {
        return [
            'formName' => ['required', 'string', 'max:100'],
            'formEmail' => ['required', 'email', 'max:150', 'unique:users,email'],
            'formPassword' => ['required', 'string', 'min:8'],
            'formRole' => ['required', 'in:admin,pharmacist,cashier'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
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
