<div>
    {{-- ── Header ── --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-[var(--color-ink)]">Kategori Obat</h2>
            <p class="mt-1 text-sm font-semibold text-[var(--color-muted)]">Kelola kategori obat berdasarkan sistem ATC WHO</p>
        </div>
        <button
            type="button"
            wire:click="openCreateModal"
            class="btn-brutal btn-primary px-4 py-2 text-sm cursor-pointer"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
            </svg>
            Tambah Kategori
        </button>
    </div>

    {{-- ── Search ── --}}
    <div class="mb-4 card-brutal p-4 bg-[var(--color-surface)]">
        <input
            type="search"
            wire:model.live.debounce.300ms="search"
            placeholder="Cari nama atau deskripsi..."
            class="block w-full max-w-xs input-brutal text-sm text-[var(--color-ink)] placeholder-[var(--color-muted)] focus:outline-none"
        />
    </div>

    {{-- ── Table ── --}}
    <div class="card-brutal overflow-hidden bg-[var(--color-surface)]">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-[var(--color-border-soft)] text-sm">
                <thead class="bg-[var(--color-surface-muted)] border-b-2 border-[var(--color-brutal)]">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-[var(--color-ink)]">Nama Kategori</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-[var(--color-ink)]">Deskripsi</th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-bold text-[var(--color-ink)]">Jumlah Obat</th>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-bold text-[var(--color-ink)]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--color-border-soft)]">
                    @forelse ($categories as $category)
                        <tr wire:key="cat-{{ $category->id }}" class="hover:bg-[var(--color-primary-soft)]/50 transition-colors duration-150">
                            <td class="px-4 py-3 font-bold text-[var(--color-ink)]">
                                {{ $category->name }}
                            </td>
                            <td class="px-4 py-3 text-[var(--color-muted)] font-semibold">
                                {{ $category->description ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="badge-brutal text-xs font-bold shadow-[1px_1px_0_var(--color-brutal)]
                                    {{ $category->medicines_count > 0 ? 'bg-[var(--color-info-soft)] text-[var(--color-brutal)]' : 'bg-[var(--color-surface-muted)] text-[var(--color-muted)]' }}">
                                    {{ $category->medicines_count }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2.5">
                                    <button
                                        type="button"
                                        wire:click="openEditModal({{ $category->id }})"
                                        class="btn-brutal btn-secondary px-2.5 py-1.5 text-xs font-bold cursor-pointer shadow-[2px_2px_0_var(--color-brutal)]"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="confirmDelete({{ $category->id }})"
                                        class="btn-brutal btn-danger px-2.5 py-1.5 text-xs font-bold cursor-pointer bg-[var(--color-danger-soft)] text-[var(--color-danger)] hover:text-white shadow-[2px_2px_0_var(--color-brutal)]"
                                    >
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center text-sm font-bold text-[var(--color-muted)]">
                                Tidak ada kategori yang sesuai pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($categories->hasPages())
            <div class="border-t-2 border-[var(--color-brutal)] px-4 py-3 bg-[var(--color-surface-muted)]">
                {{ $categories->links() }}
            </div>
        @endif
    </div>

    {{-- ══════════════════════════════════════════════
         MODAL — Tambah / Edit Kategori
    ══════════════════════════════════════════════ --}}
    <div
        x-data="{ show: @entangle('showModal') }"
        x-show="show"
        x-cloak
        class="relative z-50"
        role="dialog"
        aria-modal="true"
        aria-labelledby="category-modal-title"
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
                <h3 id="category-modal-title" class="text-lg font-bold text-[var(--color-ink)]">
                    {{ $editingId ? 'Edit Kategori' : 'Tambah Kategori Baru' }}
                </h3>

                <div class="mt-4 space-y-4">
                    {{-- Nama --}}
                    <div>
                        <label for="formName" class="mb-1 block text-sm font-bold text-[var(--color-ink)]">
                            Nama Kategori <span class="text-[var(--color-danger)]" aria-hidden="true">*</span>
                        </label>
                        <input
                            id="formName"
                            type="text"
                            wire:model="formName"
                            placeholder="Contoh: Sistem Kardiovaskular"
                            class="block w-full input-brutal text-sm text-[var(--color-ink)] placeholder-[var(--color-muted)] focus:outline-none @error('formName') border-[var(--color-danger)] @enderror"
                        />
                        @error('formName')
                            <p class="mt-1 text-xs font-bold text-[var(--color-danger)]">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Deskripsi --}}
                    <div>
                        <label for="formDescription" class="mb-1 block text-sm font-bold text-[var(--color-ink)]">
                            Deskripsi
                        </label>
                        <textarea
                            id="formDescription"
                            rows="3"
                            wire:model="formDescription"
                            placeholder="Deskripsi singkat kategori..."
                            class="block w-full input-brutal text-sm text-[var(--color-ink)] placeholder-[var(--color-muted)] focus:outline-none @error('formDescription') border-[var(--color-danger)] @enderror"
                        ></textarea>
                        @error('formDescription')
                            <p class="mt-1 text-xs font-bold text-[var(--color-danger)]">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

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
                            {{ $editingId ? 'Simpan' : 'Tambah' }}
                        </span>
                        <span wire:loading wire:target="save">...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         MODAL — Konfirmasi Hapus
    ══════════════════════════════════════════════ --}}
    <div
        x-data="{ show: @entangle('showDeleteConfirm') }"
        x-show="show"
        x-cloak
        class="relative z-50"
        role="dialog"
        aria-modal="true"
        aria-labelledby="delete-modal-title"
    >
        <div
            x-show="show"
            x-transition.opacity
            class="fixed inset-0 bg-[var(--color-brutal)]/40 backdrop-blur-xs"
        ></div>

        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div
                x-show="show"
                x-transition
                @click.stop
                class="w-full max-w-sm card-brutal card-brutal-lg p-6 bg-[var(--color-surface)]"
            >
                <div class="flex items-start gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[var(--color-danger-soft)] border-2 border-[var(--color-brutal)] shadow-[2px_2px_0_var(--color-brutal)]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[var(--color-brutal)]" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <h3 id="delete-modal-title" class="text-base font-bold text-[var(--color-ink)]">Hapus Kategori?</h3>
                        <p class="mt-1 text-sm font-semibold text-[var(--color-muted)]">
                            Tindakan ini tidak dapat dibatalkan. Kategori yang masih digunakan oleh obat tidak dapat dihapus.
                        </p>
                    </div>
                </div>

                <div class="mt-5 flex justify-end gap-2.5">
                    <button
                        type="button"
                        wire:click="cancelDelete"
                        class="btn-brutal btn-secondary px-4 py-2 text-sm font-bold cursor-pointer shadow-[2px_2px_0_var(--color-brutal)]"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        wire:click="delete"
                        wire:loading.attr="disabled"
                        wire:target="delete"
                        class="btn-brutal btn-danger px-4 py-2 text-sm font-bold cursor-pointer shadow-[2px_2px_0_var(--color-brutal)] disabled:opacity-60"
                    >
                        <span wire:loading.remove wire:target="delete">Ya, Hapus</span>
                        <span wire:loading wire:target="delete">...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
