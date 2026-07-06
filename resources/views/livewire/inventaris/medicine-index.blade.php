<div>
    @if ($errorMessage)
        <div class="mb-4 card-brutal bg-[var(--color-danger-soft)] text-[var(--color-danger)] px-4 py-3 text-sm font-bold">
            {{ $errorMessage }}
        </div>
    @endif

    @if ($successMessage || session('success'))
        <div class="mb-4 card-brutal bg-[var(--color-success-soft)] text-[var(--color-success)] px-4 py-3 text-sm font-bold">
            {{ $successMessage ?? session('success') }}
        </div>
    @endif

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-[var(--color-ink)]">Daftar Obat</h2>
            <p class="mt-1 text-sm font-semibold text-[var(--color-muted)]">Kelola master data obat apotek</p>
        </div>

        <button
            type="button"
            wire:click="$dispatch('open-medicine-form')"
            class="btn-brutal btn-primary px-4 py-2 text-sm cursor-pointer"
        >
            Tambah Obat
        </button>
    </div>

    <div class="mb-6 card-brutal p-4 bg-[var(--color-surface)]">
        <div class="grid gap-4 md:grid-cols-3">
            <div>
                <label for="search" class="mb-1.5 block text-sm font-bold text-[var(--color-ink)]">Cari Obat</label>
                <input
                    id="search"
                    type="search"
                    wire:model.live="search"
                    placeholder="Nama merek atau generik..."
                    class="block w-full input-brutal text-sm text-[var(--color-ink)] placeholder-[var(--color-muted)] focus:outline-none"
                />
            </div>

            <div>
                <label for="categoryId" class="mb-1.5 block text-sm font-bold text-[var(--color-ink)]">Kategori</label>
                <x-brutal-select 
                    id="categoryId"
                    wire:model.live="categoryId"
                    placeholder="Semua kategori"
                    :options="$categories->pluck('name', 'id')->toArray()"
                />
            </div>

            <div>
                <label for="requiresPrescription" class="mb-1.5 block text-sm font-bold text-[var(--color-ink)]">Resep</label>
                <x-brutal-select 
                    id="requiresPrescription"
                    wire:model.live="requiresPrescription"
                    placeholder="Semua"
                    :options="[
                        '1' => 'Wajib resep',
                        '0' => 'Tanpa resep'
                    ]"
                />
            </div>
        </div>
    </div>

    <div class="card-brutal overflow-hidden bg-[var(--color-surface)]">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-[var(--color-border-soft)] text-sm">
                <thead class="bg-[var(--color-surface-muted)] border-b-2 border-[var(--color-brutal)]">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-[var(--color-ink)]">Nama</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-[var(--color-ink)]">Generik</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-[var(--color-ink)]">Kategori</th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-bold text-[var(--color-ink)]">Stok</th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-bold text-[var(--color-ink)]">Min. Stok</th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-bold text-[var(--color-ink)]">Harga</th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-bold text-[var(--color-ink)]">Kedaluwarsa</th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-bold text-[var(--color-ink)]">Resep</th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-bold text-[var(--color-ink)]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--color-border-soft)]">
                    @forelse ($medicines as $medicine)
                        @php
                            $isLowStock = $medicine->stock <= $medicine->min_stock;
                            $isExpiringSoon = $medicine->expiry_date && $medicine->expiry_date->lte(now()->addMonths(3));
                        @endphp
                        <tr wire:key="medicine-{{ $medicine->id }}" class="hover:bg-[var(--color-primary-soft)]/50 transition-colors duration-150">
                            <td class="px-4 py-3 font-bold text-[var(--color-ink)]">{{ $medicine->name }}</td>
                            <td class="px-4 py-3 text-[var(--color-ink)] font-medium">{{ $medicine->generic_name ?? '—' }}</td>
                            <td class="px-4 py-3 text-[var(--color-ink)] font-medium">{{ $medicine->category?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">
                                @if ($isLowStock)
                                    <span class="badge-brutal bg-[var(--color-danger-soft)] text-[var(--color-danger)] text-xs font-bold shadow-[1px_1px_0_var(--color-brutal)]">
                                        {{ $medicine->stock }}
                                    </span>
                                @else
                                    <span class="text-[var(--color-ink)] font-semibold">{{ $medicine->stock }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center text-[var(--color-muted)] font-semibold">{{ $medicine->min_stock }}</td>
                            <td class="px-4 py-3 text-center whitespace-nowrap text-[var(--color-ink)] font-bold">Rp {{ number_format($medicine->price, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center">
                                @if ($medicine->expiry_date)
                                    @if ($isExpiringSoon)
                                        <span class="badge-brutal whitespace-nowrap bg-[var(--color-warning-soft)] text-[var(--color-warning)] text-xs font-bold shadow-[1px_1px_0_var(--color-brutal)]">
                                            {{ $medicine->expiry_date->format('d M Y') }}
                                        </span>
                                    @else
                                        <span class="whitespace-nowrap text-[var(--color-ink)] font-medium">{{ $medicine->expiry_date->format('d M Y') }}</span>
                                    @endif
                                @else
                                    <span class="text-[var(--color-muted)] font-semibold">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($medicine->requires_prescription)
                                    <span class="badge-brutal whitespace-nowrap bg-[var(--color-info-soft)] text-[var(--color-ink)] text-xs font-bold shadow-[1px_1px_0_var(--color-brutal)]">
                                        Wajib resep
                                    </span>
                                @else
                                    <span class="badge-brutal whitespace-nowrap bg-[var(--color-surface-muted)] text-[var(--color-muted)] text-xs font-bold shadow-[1px_1px_0_var(--color-brutal)]">
                                        Tanpa resep
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-center gap-2.5">
                                    <button
                                        type="button"
                                        wire:click="$dispatch('open-medicine-form', { medicineId: {{ $medicine->id }} })"
                                        class="btn-brutal btn-secondary px-2.5 py-1.5 text-xs font-bold cursor-pointer shadow-[2px_2px_0_var(--color-brutal)]"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="confirmDelete({{ $medicine->id }})"
                                        class="btn-brutal btn-danger px-2.5 py-1.5 text-xs font-bold cursor-pointer bg-[var(--color-danger-soft)] text-[var(--color-danger)] hover:text-white shadow-[2px_2px_0_var(--color-brutal)]"
                                    >
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-10 text-center text-sm font-bold text-[var(--color-muted)]">
                                Tidak ada data obat yang sesuai filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($medicines->hasPages())
            <div class="border-t-2 border-[var(--color-brutal)] px-4 py-3 bg-[var(--color-surface-muted)]">
                {{ $medicines->links() }}
            </div>
        @endif
    </div>

    <livewire:inventaris.medicine-form />

    <div
        x-data="{ show: @entangle('showDeleteModal') }"
        x-show="show"
        x-cloak
        class="relative z-50"
        aria-labelledby="delete-medicine-title"
        role="dialog"
        aria-modal="true"
    >
        <div
            x-show="show"
            x-transition.opacity
            class="fixed inset-0 bg-[var(--color-brutal)]/40 backdrop-blur-xs"
            wire:click="$set('showDeleteModal', false)"
        ></div>

        <div class="fixed inset-0 z-50 overflow-y-auto p-4 sm:p-6">
            <div class="flex min-h-full items-center justify-center">
                <div
                    x-show="show"
                    x-transition
                    @click.stop
                    class="w-full max-w-md card-brutal card-brutal-lg p-6 bg-[var(--color-surface)]"
                >
                    <h3 id="delete-medicine-title" class="text-lg font-bold text-[var(--color-ink)]">
                        Konfirmasi Hapus
                    </h3>
                    <p class="mt-3 text-sm font-semibold text-[var(--color-muted)]">
                        Apakah Anda yakin ingin menghapus obat
                        <span class="font-extrabold text-[var(--color-ink)]">{{ $deleteMedicineName }}</span>?
                        Tindakan ini tidak dapat dibatalkan.
                    </p>

                    <div class="mt-6 flex justify-end gap-2.5">
                        <button
                            type="button"
                            wire:click="$set('showDeleteModal', false)"
                            class="btn-brutal btn-secondary px-4 py-2 text-sm font-bold cursor-pointer shadow-[2px_2px_0_var(--color-brutal)]"
                        >
                            Batal
                        </button>
                        <button
                            type="button"
                            wire:click="deleteConfirmed"
                            class="btn-brutal btn-danger px-4 py-2 text-sm font-bold cursor-pointer shadow-[2px_2px_0_var(--color-brutal)]"
                            wire:loading.attr="disabled"
                            wire:target="deleteConfirmed"
                        >
                            <span wire:loading.remove wire:target="deleteConfirmed">Hapus</span>
                            <span wire:loading wire:target="deleteConfirmed">...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
