<div>
    {{-- ── Header ── --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-zinc-900">Kategori Obat</h2>
            <p class="mt-1 text-sm text-zinc-500">Kelola kategori obat berdasarkan sistem ATC WHO</p>
        </div>
        <button
            type="button"
            wire:click="openCreateModal"
            class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-zinc-800"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
            </svg>
            Tambah Kategori
        </button>
    </div>

    {{-- ── Search ── --}}
    <div class="mb-4 rounded-xl border border-zinc-200 bg-white p-4 shadow-sm">
        <input
            type="search"
            wire:model.live.debounce.300ms="search"
            placeholder="Cari nama atau deskripsi..."
            class="block w-full max-w-xs rounded-lg border border-zinc-300 px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500"
        />
    </div>

    {{-- ── Table ── --}}
    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 text-sm">
                <thead class="bg-zinc-50">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-zinc-600">Nama Kategori</th>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-zinc-600">Deskripsi</th>
                        <th scope="col" class="px-4 py-3 text-center font-medium text-zinc-600">Jumlah Obat</th>
                        <th scope="col" class="px-4 py-3 text-right font-medium text-zinc-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse ($categories as $category)
                        <tr wire:key="cat-{{ $category->id }}" class="hover:bg-zinc-50/80">
                            <td class="px-4 py-3 font-medium text-zinc-900">
                                {{ $category->name }}
                            </td>
                            <td class="px-4 py-3 text-zinc-500">
                                {{ $category->description ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium
                                    {{ $category->medicines_count > 0 ? 'bg-sky-100 text-sky-700' : 'bg-zinc-100 text-zinc-500' }}">
                                    {{ $category->medicines_count }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button
                                        type="button"
                                        wire:click="openEditModal({{ $category->id }})"
                                        class="rounded-md border border-zinc-300 bg-white px-2.5 py-1.5 text-xs font-medium text-zinc-700 hover:bg-zinc-50"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="confirmDelete({{ $category->id }})"
                                        class="rounded-md border border-red-200 bg-red-50 px-2.5 py-1.5 text-xs font-medium text-red-600 hover:bg-red-100"
                                    >
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center text-sm text-zinc-500">
                                Tidak ada kategori yang sesuai pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($categories->hasPages())
            <div class="border-t border-zinc-200 px-4 py-3">
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
                <h3 id="category-modal-title" class="text-lg font-semibold text-zinc-900">
                    {{ $editingId ? 'Edit Kategori' : 'Tambah Kategori Baru' }}
                </h3>

                <div class="mt-4 space-y-4">
                    {{-- Nama --}}
                    <div>
                        <label for="formName" class="mb-1 block text-sm font-medium text-zinc-700">
                            Nama Kategori <span class="text-red-500" aria-hidden="true">*</span>
                        </label>
                        <input
                            id="formName"
                            type="text"
                            wire:model="formName"
                            placeholder="Contoh: Sistem Kardiovaskular"
                            class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500 @error('formName') border-red-500 @enderror"
                        />
                        @error('formName')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Deskripsi --}}
                    <div>
                        <label for="formDescription" class="mb-1 block text-sm font-medium text-zinc-700">
                            Deskripsi
                        </label>
                        <textarea
                            id="formDescription"
                            rows="3"
                            wire:model="formDescription"
                            placeholder="Deskripsi singkat kategori..."
                            class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500 @error('formDescription') border-red-500 @enderror"
                        ></textarea>
                        @error('formDescription')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

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
                            {{ $editingId ? 'Simpan Perubahan' : 'Tambah Kategori' }}
                        </span>
                        <span wire:loading wire:target="save">Menyimpan...</span>
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
            class="fixed inset-0 bg-zinc-900/60"
        ></div>

        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div
                x-show="show"
                x-transition
                @click.stop
                class="w-full max-w-sm rounded-2xl border border-zinc-200 bg-white p-6 shadow-xl"
            >
                <div class="flex items-start gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <h3 id="delete-modal-title" class="text-base font-semibold text-zinc-900">Hapus Kategori?</h3>
                        <p class="mt-1 text-sm text-zinc-500">
                            Tindakan ini tidak dapat dibatalkan. Kategori yang masih digunakan oleh obat tidak dapat dihapus.
                        </p>
                    </div>
                </div>

                <div class="mt-5 flex justify-end gap-2">
                    <button
                        type="button"
                        wire:click="cancelDelete"
                        class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        wire:click="delete"
                        wire:loading.attr="disabled"
                        wire:target="delete"
                        class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 disabled:opacity-60"
                    >
                        <span wire:loading.remove wire:target="delete">Ya, Hapus</span>
                        <span wire:loading wire:target="delete">Menghapus...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
