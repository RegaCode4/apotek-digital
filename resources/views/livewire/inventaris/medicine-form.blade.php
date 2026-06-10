<div
    x-data="{ show: @entangle('show') }"
    x-show="show"
    x-cloak
    class="relative z-50"
    aria-labelledby="medicine-form-title"
    role="dialog"
    aria-modal="true"
>
    <div
        x-show="show"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-zinc-900/50"
        wire:click="close"
    ></div>

    <div class="fixed inset-0 z-50 overflow-y-auto p-4 sm:p-6">
        <div class="flex min-h-full items-center justify-center">
            <div
                x-show="show"
                x-transition
                @click.stop
                class="w-full max-w-2xl rounded-xl border border-zinc-200 bg-white shadow-xl"
            >
                <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-4">
                    <h3 id="medicine-form-title" class="text-lg font-semibold text-zinc-900">
                        {{ $medicineId ? 'Edit Obat' : 'Tambah Obat' }}
                    </h3>
                    <button
                        type="button"
                        wire:click="close"
                        class="rounded-md p-1 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600"
                    >
                        <span class="sr-only">Tutup</span>
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="save" class="space-y-5 px-6 py-5">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="name" class="mb-1.5 block text-sm font-medium text-zinc-700">Nama Obat <span class="text-red-500">*</span></label>
                            <input
                                id="name"
                                type="text"
                                wire:model="name"
                                class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500 @error('name') border-red-500 @enderror"
                            />
                            @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="generic_name" class="mb-1.5 block text-sm font-medium text-zinc-700">Nama Generik</label>
                            <input id="generic_name" type="text" wire:model="generic_name" class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500" />
                            @error('generic_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="category" class="mb-1.5 block text-sm font-medium text-zinc-700">Kategori</label>
                            <input id="category" type="text" wire:model="category" class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500" />
                            @error('category') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="manufacturer" class="mb-1.5 block text-sm font-medium text-zinc-700">Produsen</label>
                            <input id="manufacturer" type="text" wire:model="manufacturer" class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500" />
                            @error('manufacturer') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="unit" class="mb-1.5 block text-sm font-medium text-zinc-700">Satuan <span class="text-red-500">*</span></label>
                            <input id="unit" type="text" wire:model="unit" class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500" />
                            @error('unit') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="price" class="mb-1.5 block text-sm font-medium text-zinc-700">Harga <span class="text-red-500">*</span></label>
                            <input id="price" type="number" step="0.01" min="0" wire:model="price" class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500" />
                            @error('price') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="stock" class="mb-1.5 block text-sm font-medium text-zinc-700">Stok <span class="text-red-500">*</span></label>
                            <input id="stock" type="number" min="0" wire:model="stock" class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500" />
                            @error('stock') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="min_stock" class="mb-1.5 block text-sm font-medium text-zinc-700">Min. Stok <span class="text-red-500">*</span></label>
                            <input id="min_stock" type="number" min="0" wire:model="min_stock" class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500" />
                            @error('min_stock') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="expiry_date" class="mb-1.5 block text-sm font-medium text-zinc-700">Tanggal Kedaluwarsa</label>
                            <input id="expiry_date" type="date" wire:model="expiry_date" class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500" />
                            @error('expiry_date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex items-center sm:col-span-2">
                            <input
                                id="requires_prescription"
                                type="checkbox"
                                wire:model="requires_prescription"
                                class="h-4 w-4 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500"
                            />
                            <label for="requires_prescription" class="ml-2 text-sm text-zinc-700">Wajib resep dokter</label>
                        </div>

                        <div class="sm:col-span-2">
                            <label for="description" class="mb-1.5 block text-sm font-medium text-zinc-700">Deskripsi</label>
                            <textarea
                                id="description"
                                rows="3"
                                wire:model="description"
                                class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500"
                            ></textarea>
                            @error('description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 border-t border-zinc-200 pt-4">
                        <button
                            type="button"
                            wire:click="close"
                            class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800"
                            wire:loading.attr="disabled"
                            wire:target="save"
                        >
                            <span wire:loading.remove wire:target="save">Simpan</span>
                            <span wire:loading wire:target="save">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
