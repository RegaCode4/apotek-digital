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
        class="fixed inset-0 bg-[var(--color-brutal)]/40 backdrop-blur-xs"
        wire:click="close"
    ></div>

    <div class="fixed inset-0 z-50 overflow-y-auto p-4 sm:p-6">
        <div class="flex min-h-full items-center justify-center">
            <div
                x-show="show"
                x-transition
                @click.stop
                class="w-full max-w-2xl card-brutal card-brutal-lg bg-[var(--color-surface)]"
            >
                <div class="flex items-center justify-between border-b-2 border-[var(--color-brutal)] bg-[var(--color-surface-muted)] rounded-t-[var(--radius-brutal)] px-6 py-4">
                    <h3 id="medicine-form-title" class="text-lg font-bold text-[var(--color-ink)]">
                        {{ $medicineId ? 'Edit Obat' : 'Tambah Obat' }}
                    </h3>
                    <button
                        type="button"
                        wire:click="close"
                        class="rounded-md p-1.5 text-[var(--color-muted)] hover:bg-white/10 hover:text-[var(--color-ink)] border border-transparent hover:border-[var(--color-brutal)] transition-all cursor-pointer"
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
                            <label for="name" class="mb-1.5 block text-sm font-bold text-[var(--color-ink)]">Nama Obat <span class="text-[var(--color-danger)]">*</span></label>
                            <input
                                id="name"
                                type="text"
                                wire:model="name"
                                class="block w-full input-brutal text-sm text-[var(--color-ink)] placeholder-[var(--color-muted)] focus:outline-none @error('name') border-[var(--color-danger)] @enderror"
                            />
                            @error('name') <p class="mt-1 text-xs font-bold text-[var(--color-danger)]">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="generic_name" class="mb-1.5 block text-sm font-bold text-[var(--color-ink)]">Nama Generik</label>
                            <input id="generic_name" type="text" wire:model="generic_name" class="block w-full input-brutal text-sm text-[var(--color-ink)] placeholder-[var(--color-muted)] focus:outline-none" />
                            @error('generic_name') <p class="mt-1 text-xs font-bold text-[var(--color-danger)]">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="category_id" class="mb-1.5 block text-sm font-bold text-[var(--color-ink)]">Kategori</label>
                            <x-brutal-select 
                                id="category_id"
                                wire:model="category_id"
                                placeholder="— Pilih Kategori —"
                                :options="$categoryOptions->pluck('name', 'id')->toArray()"
                                class="{{ $errors->has('category_id') ? 'border-[var(--color-danger)]' : '' }}"
                            />
                            @error('category_id') <p class="mt-1 text-xs font-bold text-[var(--color-danger)]">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="manufacturer" class="mb-1.5 block text-sm font-bold text-[var(--color-ink)]">Produsen</label>
                            <input id="manufacturer" type="text" wire:model="manufacturer" class="block w-full input-brutal text-sm text-[var(--color-ink)] placeholder-[var(--color-muted)] focus:outline-none" />
                            @error('manufacturer') <p class="mt-1 text-xs font-bold text-[var(--color-danger)]">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="unit" class="mb-1.5 block text-sm font-bold text-[var(--color-ink)]">Satuan <span class="text-[var(--color-danger)]">*</span></label>
                            <input id="unit" type="text" wire:model="unit" class="block w-full input-brutal text-sm text-[var(--color-ink)] placeholder-[var(--color-muted)] focus:outline-none" />
                            @error('unit') <p class="mt-1 text-xs font-bold text-[var(--color-danger)]">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="price" class="mb-1.5 block text-sm font-bold text-[var(--color-ink)]">Harga <span class="text-[var(--color-danger)]">*</span></label>
                            <input id="price" type="number" step="0.01" min="0" wire:model="price" class="block w-full input-brutal text-sm text-[var(--color-ink)] placeholder-[var(--color-muted)] focus:outline-none" />
                            @error('price') <p class="mt-1 text-xs font-bold text-[var(--color-danger)]">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="stock" class="mb-1.5 block text-sm font-bold text-[var(--color-ink)]">Stok <span class="text-[var(--color-danger)]">*</span></label>
                            <input id="stock" type="number" min="0" wire:model="stock" class="block w-full input-brutal text-sm text-[var(--color-ink)] placeholder-[var(--color-muted)] focus:outline-none" />
                            @error('stock') <p class="mt-1 text-xs font-bold text-[var(--color-danger)]">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="min_stock" class="mb-1.5 block text-sm font-bold text-[var(--color-ink)]">Min. Stok <span class="text-[var(--color-danger)]">*</span></label>
                            <input id="min_stock" type="number" min="0" wire:model="min_stock" class="block w-full input-brutal text-sm text-[var(--color-ink)] placeholder-[var(--color-muted)] focus:outline-none" />
                            @error('min_stock') <p class="mt-1 text-xs font-bold text-[var(--color-danger)]">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="expiry_date" class="mb-1.5 block text-sm font-bold text-[var(--color-ink)]">Tanggal Kedaluwarsa</label>
                            <input id="expiry_date" type="text" x-datepicker wire:model="expiry_date" class="block w-full input-brutal text-sm text-[var(--color-ink)] focus:outline-none" />
                            @error('expiry_date') <p class="mt-1 text-xs font-bold text-[var(--color-danger)]">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex items-center sm:col-span-2">
                            <input
                                id="requires_prescription"
                                type="checkbox"
                                wire:model="requires_prescription"
                                class="h-5 w-5 rounded border-2 border-[var(--color-brutal)] text-[var(--color-primary)] focus:ring-[var(--color-primary)] focus:ring-offset-2 cursor-pointer"
                            />
                            <label for="requires_prescription" class="ml-2 text-sm font-bold text-[var(--color-ink)] cursor-pointer">Wajib resep dokter</label>
                        </div>

                        <div class="sm:col-span-2">
                            <label for="description" class="mb-1.5 block text-sm font-bold text-[var(--color-ink)]">Deskripsi</label>
                            <textarea
                                id="description"
                                rows="3"
                                wire:model="description"
                                class="block w-full input-brutal text-sm text-[var(--color-ink)] placeholder-[var(--color-muted)] focus:outline-none"
                            ></textarea>
                            @error('description') <p class="mt-1 text-xs font-bold text-[var(--color-danger)]">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex justify-end gap-2.5 border-t-2 border-[var(--color-brutal)] pt-4">
                        <button
                            type="button"
                            wire:click="close"
                            class="btn-brutal btn-secondary px-4 py-2 text-sm font-bold cursor-pointer shadow-[2px_2px_0_var(--color-brutal)]"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="btn-brutal btn-primary px-4 py-2 text-sm font-bold cursor-pointer shadow-[2px_2px_0_var(--color-brutal)]"
                            wire:loading.attr="disabled"
                            wire:target="save"
                        >
                            <span wire:loading.remove wire:target="save">Simpan</span>
                            <span wire:loading wire:target="save">...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
