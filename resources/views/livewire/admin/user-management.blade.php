<div>
    {{-- ── Header ── --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-[var(--color-ink)]">Manajemen User</h2>
            <p class="mt-1 text-sm font-semibold text-[var(--color-muted)]">Kelola akun pengguna sistem</p>
        </div>
        <button
            type="button"
            wire:click="openCreateModal"
            class="btn-brutal btn-primary px-4 py-2 text-sm cursor-pointer"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
            </svg>
            Tambah User
        </button>
    </div>

    {{-- ── Cari ── --}}
    <div class="mb-6 card-brutal p-4 bg-[var(--color-surface)]">
        <div>
            <label for="search" class="mb-1.5 block text-sm font-bold text-[var(--color-ink)]">Cari User</label>
            <input
                id="search"
                type="search"
                wire:model.live="search"
                placeholder="Cari nama atau email..."
                class="block w-full max-w-xs input-brutal text-sm text-[var(--color-ink)] placeholder-[var(--color-muted)] focus:outline-none"
            />
        </div>
    </div>

    {{-- ── Table ── --}}
    <div class="overflow-hidden card-brutal bg-[var(--color-surface)]">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-[var(--color-border-soft)] text-sm">
                <thead class="bg-[var(--color-surface-muted)] border-b-2 border-[var(--color-brutal)]">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-[var(--color-ink)]">Nama</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-[var(--color-ink)]">Email</th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-bold text-[var(--color-ink)]">Role</th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-bold text-[var(--color-ink)]">Status</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-[var(--color-ink)]">Dibuat</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-[var(--color-ink)]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--color-border-soft)]">
                    @forelse ($users as $user)
                        @php $isSelf = $user->id === auth()->id(); @endphp
                        <tr wire:key="user-{{ $user->id }}" class="hover:bg-[var(--color-primary-soft)]/50 transition-colors duration-150">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-[var(--color-primary-soft)] border-2 border-[var(--color-brutal)] text-xs font-extrabold text-[var(--color-primary-contrast)]">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <span class="font-bold text-[var(--color-ink)]">
                                        {{ $user->name }}
                                        @if ($isSelf)
                                            <span class="ml-1 text-xs font-semibold text-[var(--color-muted)]">(Anda)</span>
                                        @endif
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3 font-semibold text-[var(--color-muted)]">{{ $user->email }}</td>
                            <td class="px-4 py-3 text-center">
                                @php
                                    $roleBg = match ($user->role) {
                                        'admin'      => 'bg-[var(--color-info-soft)] text-[var(--color-ink)]',
                                        'pharmacist' => 'bg-[var(--color-primary-soft)] text-[var(--color-primary-contrast)]',
                                        default      => 'bg-[var(--color-surface-muted)] text-[var(--color-muted)]',
                                    };
                                @endphp
                                <span class="badge-brutal px-2.5 py-0.5 text-xs font-bold shadow-[1px_1px_0_var(--color-brutal)] {{ $roleBg }} capitalize">
                                    {{ $user->role }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($user->is_active)
                                    <span class="badge-brutal bg-[var(--color-success-soft)] text-[var(--color-success)] px-2.5 py-0.5 text-xs font-bold shadow-[1px_1px_0_var(--color-brutal)]">Aktif</span>
                                @else
                                    <span class="badge-brutal bg-[var(--color-danger-soft)] text-[var(--color-danger)] px-2.5 py-0.5 text-xs font-bold shadow-[1px_1px_0_var(--color-brutal)]">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-semibold text-[var(--color-muted)]">
                                {{ $user->created_at->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-start gap-1.5">
                                    {{-- Edit --}}
                                    <button
                                        type="button"
                                        wire:click="openEditModal({{ $user->id }})"
                                        class="btn-brutal btn-secondary px-2.5 py-1.5 text-xs font-bold cursor-pointer shadow-[2px_2px_0_var(--color-brutal)]"
                                    >
                                        Edit
                                    </button>

                                    {{-- Alihkan aktif --}}
                                    @if (! $isSelf)
                                        <button
                                            type="button"
                                            wire:click="openConfirmModal('toggleActive', {{ $user->id }})"
                                            class="btn-brutal px-2.5 py-1.5 text-xs font-bold cursor-pointer shadow-[2px_2px_0_var(--color-brutal)] {{ $user->is_active ? 'bg-[var(--color-warning-soft)] text-[var(--color-warning)]' : 'bg-[var(--color-success-soft)] text-[var(--color-success)]' }}"
                                        >
                                            {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    @endif

                                    {{-- Atur ulang kata sandi --}}
                                    <button
                                        type="button"
                                        wire:click="openConfirmModal('resetPassword', {{ $user->id }})"
                                        class="btn-brutal btn-secondary px-2.5 py-1.5 text-xs font-bold cursor-pointer shadow-[2px_2px_0_var(--color-brutal)]"
                                        title="Reset password ke password123"
                                    >
                                        Reset PW
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-sm font-bold text-[var(--color-muted)]">
                                Tidak ada user yang sesuai pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="border-t-2 border-[var(--color-brutal)] px-4 py-3 bg-[var(--color-surface-muted)]">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    {{-- ══════════════════════════════════════════════
         MODAL — Tambah / Edit User
    ══════════════════════════════════════════════ --}}
    <div
        x-data="{ show: @entangle('showModal') }"
        x-show="show"
        x-cloak
        class="relative z-50"
        role="dialog"
        aria-modal="true"
        aria-labelledby="user-modal-title"
    >
        <div
            x-show="show"
            x-transition.opacity
            class="fixed inset-0 bg-[var(--color-brutal)]/40 backdrop-blur-xs"
            wire:click="closeModal"
        ></div>

        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div
                x-show="show"
                x-transition
                @click.stop
                class="w-full max-w-md card-brutal card-brutal-lg p-6 bg-[var(--color-surface)]"
            >
                <h3 id="user-modal-title" class="text-lg font-bold text-[var(--color-ink)]">
                    {{ $editingUserId ? 'Edit User' : 'Tambah User Baru' }}
                </h3>

                <div class="mt-4 space-y-4">
                    {{-- Nama --}}
                    <div>
                        <label for="formName" class="mb-1.5 block text-sm font-bold text-[var(--color-ink)]">
                            Nama <span class="text-[var(--color-danger)]" aria-hidden="true">*</span>
                        </label>
                        <input
                            id="formName"
                            type="text"
                            wire:model.live="formName"
                            placeholder="Nama lengkap..."
                            class="block w-full input-brutal text-sm text-[var(--color-ink)] placeholder-[var(--color-muted)] focus:outline-none @error('formName') border-[var(--color-danger)] @enderror"
                        />
                        @error('formName')
                            <p class="mt-1 text-xs font-bold text-[var(--color-danger)]">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label for="formEmail" class="mb-1.5 block text-sm font-bold text-[var(--color-ink)]">
                            Email <span class="text-[var(--color-danger)]" aria-hidden="true">*</span>
                        </label>
                        <input
                            id="formEmail"
                            type="email"
                            wire:model.live="formEmail"
                            placeholder="email@apotek.com"
                            class="block w-full input-brutal text-sm text-[var(--color-ink)] placeholder-[var(--color-muted)] focus:outline-none @error('formEmail') border-[var(--color-danger)] @enderror"
                        />
                        @error('formEmail')
                            <p class="mt-1 text-xs font-bold text-[var(--color-danger)]">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Kata sandi (hanya buat) --}}
                    @if (! $editingUserId)
                        <div>
                            <label for="formPassword" class="mb-1.5 block text-sm font-bold text-[var(--color-ink)]">
                                Password <span class="text-[var(--color-danger)]" aria-hidden="true">*</span>
                            </label>
                            <input
                                id="formPassword"
                                type="password"
                                wire:model.live="formPassword"
                                placeholder="Min. 8 karakter"
                                class="block w-full input-brutal text-sm text-[var(--color-ink)] placeholder-[var(--color-muted)] focus:outline-none @error('formPassword') border-[var(--color-danger)] @enderror"
                            />
                            @error('formPassword')
                                <p class="mt-1 text-xs font-bold text-[var(--color-danger)]">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    {{-- Role --}}
                    <div>
                        <label for="formRole" class="mb-1.5 block text-sm font-bold text-[var(--color-ink)]">
                            Role <span class="text-[var(--color-danger)]" aria-hidden="true">*</span>
                        </label>
                        <select
                            id="formRole"
                            wire:model.live="formRole"
                            class="block w-full input-brutal text-sm text-[var(--color-ink)] focus:outline-none @error('formRole') border-[var(--color-danger)] @enderror"
                        >
                            <option value="cashier">Cashier</option>
                            <option value="pharmacist">Pharmacist</option>
                            <option value="admin">Admin</option>
                        </select>
                        @error('formRole')
                            <p class="mt-1 text-xs font-bold text-[var(--color-danger)]">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Status aktif (hanya edit) --}}
                    @if ($editingUserId)
                        <div class="flex items-center justify-between card-brutal px-3 py-2.5 bg-[var(--color-surface-muted)]">
                            <label for="formIsActive" class="text-sm font-bold text-[var(--color-ink)]">
                                Status Akun
                            </label>
                            <button
                                type="button"
                                wire:click="$toggle('formIsActive')"
                                id="formIsActive"
                                role="switch"
                                aria-checked="{{ $formIsActive ? 'true' : 'false' }}"
                                class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-[var(--color-brutal)] transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)] focus:ring-offset-2 {{ $formIsActive ? 'bg-[var(--color-primary)]' : 'bg-[var(--color-surface-muted)]' }}"
                            >
                                <span
                                    class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $formIsActive ? 'translate-x-4' : 'translate-x-0' }}"
                                ></span>
                            </button>
                        </div>
                        @error('formIsActive')
                            <p class="text-xs font-bold text-[var(--color-danger)]">{{ $message }}</p>
                        @enderror
                    @endif
                </div>

                {{-- Aksi --}}
                <div class="mt-6 flex justify-end gap-2.5">
                    <button
                        type="button"
                        wire:click="closeModal"
                        class="btn-brutal btn-secondary px-4 py-2 text-sm font-bold cursor-pointer shadow-[2px_2px_0_var(--color-brutal)]"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        wire:click="save"
                        wire:loading.attr="disabled"
                        wire:target="save"
                        class="btn-brutal btn-primary px-4 py-2 text-sm font-bold cursor-pointer shadow-[2px_2px_0_var(--color-brutal)] disabled:opacity-60"
                    >
                        <span wire:loading.remove wire:target="save">
                            {{ $editingUserId ? 'Simpan Perubahan' : 'Tambah User' }}
                        </span>
                        <span wire:loading wire:target="save">Menyimpan...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         MODAL — Konfirmasi Aksi
    ══════════════════════════════════════════════ --}}
    <div
        x-data="{ show: @entangle('showConfirmModal') }"
        x-show="show"
        x-cloak
        class="relative z-50"
        role="dialog"
        aria-modal="true"
        aria-labelledby="confirm-modal-title"
    >
        <div
            x-show="show"
            x-transition.opacity
            class="fixed inset-0 bg-[var(--color-brutal)]/40 backdrop-blur-xs"
            wire:click="closeConfirmModal"
        ></div>

        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div
                x-show="show"
                x-transition
                @click.stop
                class="w-full max-w-sm card-brutal card-brutal-lg p-6 bg-[var(--color-surface)]"
            >
                {{-- Icon --}}
                <div class="mb-4 flex items-center justify-center">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full border-2 border-[var(--color-brutal)] {{ $confirmAction === 'resetPassword' ? 'bg-[var(--color-warning-soft)]' : ($confirmAction === 'toggleActive' ? 'bg-[var(--color-danger-soft)]' : 'bg-[var(--color-info-soft)]') }}">
                        @if ($confirmAction === 'resetPassword')
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[var(--color-warning)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[var(--color-danger)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                        @endif
                    </div>
                </div>

                {{-- Title --}}
                <h3 id="confirm-modal-title" class="text-center text-lg font-bold text-[var(--color-ink)]">
                    {{ $confirmTitle }}
                </h3>

                {{-- Message --}}
                <p class="mt-2 text-center text-sm font-semibold text-[var(--color-muted)]">
                    {{ $confirmMessage }}
                </p>

                {{-- Actions --}}
                <div class="mt-6 flex justify-center gap-2.5">
                    <button
                        type="button"
                        wire:click="closeConfirmModal"
                        class="btn-brutal btn-secondary px-4 py-2 text-sm font-bold cursor-pointer shadow-[2px_2px_0_var(--color-brutal)]"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        wire:click="executeConfirm"
                        wire:loading.attr="disabled"
                        wire:target="executeConfirm"
                        class="btn-brutal px-4 py-2 text-sm font-bold cursor-pointer shadow-[2px_2px_0_var(--color-brutal)] disabled:opacity-60 {{ $confirmAction === 'resetPassword' ? 'bg-[var(--color-warning-soft)] text-[var(--color-warning)] hover:bg-[var(--color-warning)] hover:text-white' : 'bg-[var(--color-danger-soft)] text-[var(--color-danger)] hover:bg-[var(--color-danger)] hover:text-white' }} transition-colors duration-150"
                    >
                        <span wire:loading.remove wire:target="executeConfirm">Ya, Lanjutkan</span>
                        <span wire:loading wire:target="executeConfirm">Memproses...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
