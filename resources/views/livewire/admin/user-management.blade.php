<div>
    {{-- ── Header ── --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-zinc-900">Manajemen User</h2>
            <p class="mt-1 text-sm text-zinc-500">Kelola akun pengguna sistem</p>
        </div>
        <button
            type="button"
            wire:click="openCreateModal"
            class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-zinc-800"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
            </svg>
            Tambah User
        </button>
    </div>

    {{-- ── Search ── --}}
    <div class="mb-4 rounded-xl border border-zinc-200 bg-white p-4 shadow-sm">
        <input
            type="search"
            wire:model.live="search"
            placeholder="Cari nama atau email..."
            class="block w-full max-w-xs rounded-lg border border-zinc-300 px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500"
        />
    </div>

    {{-- ── Table ── --}}
    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 text-sm">
                <thead class="bg-zinc-50">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-zinc-600">Nama</th>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-zinc-600">Email</th>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-zinc-600">Role</th>
                        <th scope="col" class="px-4 py-3 text-center font-medium text-zinc-600">Status</th>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-zinc-600">Dibuat</th>
                        <th scope="col" class="px-4 py-3 text-right font-medium text-zinc-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse ($users as $user)
                        @php $isSelf = $user->id === auth()->id(); @endphp
                        <tr wire:key="user-{{ $user->id }}" class="hover:bg-zinc-50/80">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-zinc-200 text-xs font-bold text-zinc-700">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <span class="font-medium text-zinc-900">
                                        {{ $user->name }}
                                        @if ($isSelf)
                                            <span class="ml-1 text-xs text-zinc-400">(Anda)</span>
                                        @endif
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-zinc-600">{{ $user->email }}</td>
                            <td class="px-4 py-3">
                                @php
                                    $roleBadge = match ($user->role) {
                                        'admin'      => 'bg-violet-100 text-violet-700',
                                        'pharmacist' => 'bg-sky-100 text-sky-700',
                                        default      => 'bg-zinc-100 text-zinc-600',
                                    };
                                @endphp
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium capitalize {{ $roleBadge }}">
                                    {{ $user->role }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($user->is_active)
                                    <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-700">Aktif</span>
                                @else
                                    <span class="inline-flex rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-600">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-zinc-500">
                                {{ $user->created_at->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1.5">
                                    {{-- Edit --}}
                                    <button
                                        type="button"
                                        wire:click="openEditModal({{ $user->id }})"
                                        class="rounded-md border border-zinc-300 bg-white px-2.5 py-1.5 text-xs font-medium text-zinc-700 hover:bg-zinc-50"
                                    >
                                        Edit
                                    </button>

                                    {{-- Toggle active --}}
                                    @if (! $isSelf)
                                        <button
                                            type="button"
                                            wire:click="toggleActive({{ $user->id }})"
                                            wire:confirm="{{ $user->is_active ? 'Nonaktifkan user ini?' : 'Aktifkan user ini?' }}"
                                            class="rounded-md border px-2.5 py-1.5 text-xs font-medium
                                                {{ $user->is_active
                                                    ? 'border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100'
                                                    : 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}"
                                        >
                                            {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    @endif

                                    {{-- Reset password --}}
                                    <button
                                        type="button"
                                        wire:click="resetPassword({{ $user->id }})"
                                        wire:confirm="Reset password {{ $user->name }} ke 'password123'?"
                                        class="rounded-md border border-zinc-300 bg-white px-2.5 py-1.5 text-xs font-medium text-zinc-600 hover:bg-zinc-50"
                                        title="Reset password ke password123"
                                    >
                                        Reset PW
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-sm text-zinc-500">
                                Tidak ada user yang sesuai pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="border-t border-zinc-200 px-4 py-3">
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
            class="fixed inset-0 bg-zinc-900/60"
            wire:click="closeModal"
        ></div>

        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div
                x-show="show"
                x-transition
                @click.stop
                class="w-full max-w-md rounded-2xl border border-zinc-200 bg-white p-6 shadow-xl"
            >
                <h3 id="user-modal-title" class="text-lg font-semibold text-zinc-900">
                    {{ $editingUserId ? 'Edit User' : 'Tambah User Baru' }}
                </h3>

                <div class="mt-4 space-y-4">
                    {{-- Nama --}}
                    <div>
                        <label for="formName" class="mb-1 block text-sm font-medium text-zinc-700">
                            Nama <span class="text-red-500" aria-hidden="true">*</span>
                        </label>
                        <input
                            id="formName"
                            type="text"
                            wire:model.live="formName"
                            placeholder="Nama lengkap..."
                            class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500"
                        />
                        @error('formName')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label for="formEmail" class="mb-1 block text-sm font-medium text-zinc-700">
                            Email <span class="text-red-500" aria-hidden="true">*</span>
                        </label>
                        <input
                            id="formEmail"
                            type="email"
                            wire:model.live="formEmail"
                            placeholder="email@apotek.com"
                            class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500"
                        />
                        @error('formEmail')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password (create only) --}}
                    @if (! $editingUserId)
                        <div>
                            <label for="formPassword" class="mb-1 block text-sm font-medium text-zinc-700">
                                Password <span class="text-red-500" aria-hidden="true">*</span>
                            </label>
                            <input
                                id="formPassword"
                                type="password"
                                wire:model.live="formPassword"
                                placeholder="Min. 8 karakter"
                                class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500"
                            />
                            @error('formPassword')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    {{-- Role --}}
                    <div>
                        <label for="formRole" class="mb-1 block text-sm font-medium text-zinc-700">
                            Role <span class="text-red-500" aria-hidden="true">*</span>
                        </label>
                        <select
                            id="formRole"
                            wire:model.live="formRole"
                            class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500"
                        >
                            <option value="cashier">Cashier</option>
                            <option value="pharmacist">Pharmacist</option>
                            <option value="admin">Admin</option>
                        </select>
                        @error('formRole')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Status aktif (edit only) --}}
                    @if ($editingUserId)
                        <div class="flex items-center justify-between rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-2.5">
                            <label for="formIsActive" class="text-sm font-medium text-zinc-700">
                                Status Akun
                            </label>
                            <button
                                type="button"
                                wire:click="$toggle('formIsActive')"
                                id="formIsActive"
                                role="switch"
                                aria-checked="{{ $formIsActive ? 'true' : 'false' }}"
                                class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-zinc-500 focus:ring-offset-2
                                    {{ $formIsActive ? 'bg-zinc-900' : 'bg-zinc-300' }}"
                            >
                                <span
                                    class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out
                                        {{ $formIsActive ? 'translate-x-4' : 'translate-x-0' }}"
                                ></span>
                            </button>
                        </div>
                        @error('formIsActive')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    @endif
                </div>

                {{-- Actions --}}
                <div class="mt-6 flex justify-end gap-2">
                    <button
                        type="button"
                        wire:click="closeModal"
                        class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        wire:click="save"
                        wire:loading.attr="disabled"
                        wire:target="save"
                        class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 disabled:opacity-60"
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
</div>
