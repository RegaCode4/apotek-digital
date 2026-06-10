<div>
    @if ($errorMessage)
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ $errorMessage }}
        </div>
    @endif

    @if ($successMessage || session('success'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ $successMessage ?? session('success') }}
        </div>
    @endif

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-zinc-900">Daftar Obat</h2>
            <p class="mt-1 text-sm text-zinc-500">Kelola master data obat apotek</p>
        </div>

        <button
            type="button"
            wire:click="$dispatch('open-medicine-form')"
            class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-zinc-800"
        >
            Tambah Obat
        </button>
    </div>

    <div class="mb-6 rounded-xl border border-zinc-200 bg-white p-4 shadow-sm">
        <div class="grid gap-4 md:grid-cols-3">
            <div>
                <label for="search" class="mb-1.5 block text-sm font-medium text-zinc-700">Cari Obat</label>
                <input
                    id="search"
                    type="search"
                    wire:model.live="search"
                    placeholder="Nama merek atau generik..."
                    class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500"
                />
            </div>

            <div>
                <label for="category" class="mb-1.5 block text-sm font-medium text-zinc-700">Kategori</label>
                <select
                    id="category"
                    wire:model.live="category"
                    class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500"
                >
                    <option value="">Semua kategori</option>
                    @foreach ($categories as $item)
                        <option value="{{ $item }}">{{ $item }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="requiresPrescription" class="mb-1.5 block text-sm font-medium text-zinc-700">Resep</label>
                <select
                    id="requiresPrescription"
                    wire:model.live="requiresPrescription"
                    class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500"
                >
                    <option value="">Semua</option>
                    <option value="1">Wajib resep</option>
                    <option value="0">Tanpa resep</option>
                </select>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 text-sm">
                <thead class="bg-zinc-50">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-zinc-600">Nama</th>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-zinc-600">Generik</th>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-zinc-600">Kategori</th>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-zinc-600">Stok</th>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-zinc-600">Min. Stok</th>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-zinc-600">Harga</th>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-zinc-600">Kedaluwarsa</th>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-zinc-600">Resep</th>
                        <th scope="col" class="px-4 py-3 text-right font-medium text-zinc-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse ($medicines as $medicine)
                        @php
                            $isLowStock = $medicine->stock <= $medicine->min_stock;
                            $isExpiringSoon = $medicine->expiry_date && $medicine->expiry_date->lte(now()->addMonths(3));
                        @endphp
                        <tr wire:key="medicine-{{ $medicine->id }}" class="hover:bg-zinc-50/80">
                            <td class="px-4 py-3 font-medium text-zinc-900">{{ $medicine->name }}</td>
                            <td class="px-4 py-3 text-zinc-600">{{ $medicine->generic_name ?? '—' }}</td>
                            <td class="px-4 py-3 text-zinc-600">{{ $medicine->category ?? '—' }}</td>
                            <td class="px-4 py-3">
                                @if ($isLowStock)
                                    <span class="inline-flex rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-700">
                                        {{ $medicine->stock }}
                                    </span>
                                @else
                                    <span class="text-zinc-700">{{ $medicine->stock }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-zinc-600">{{ $medicine->min_stock }}</td>
                            <td class="px-4 py-3 text-zinc-700">Rp {{ number_format($medicine->price, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                @if ($medicine->expiry_date)
                                    @if ($isExpiringSoon)
                                        <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800">
                                            {{ $medicine->expiry_date->format('d M Y') }}
                                        </span>
                                    @else
                                        <span class="text-zinc-700">{{ $medicine->expiry_date->format('d M Y') }}</span>
                                    @endif
                                @else
                                    <span class="text-zinc-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if ($medicine->requires_prescription)
                                    <span class="inline-flex rounded-full bg-sky-100 px-2.5 py-0.5 text-xs font-medium text-sky-700">
                                        Wajib resep
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-medium text-zinc-600">
                                        Tanpa resep
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    <button
                                        type="button"
                                        wire:click="$dispatch('open-medicine-form', { medicineId: {{ $medicine->id }} })"
                                        class="rounded-md border border-zinc-300 bg-white px-2.5 py-1.5 text-xs font-medium text-zinc-700 hover:bg-zinc-50"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="confirmDelete({{ $medicine->id }})"
                                        class="rounded-md border border-red-200 bg-white px-2.5 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50"
                                    >
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-10 text-center text-sm text-zinc-500">
                                Tidak ada data obat yang sesuai filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($medicines->hasPages())
            <div class="border-t border-zinc-200 px-4 py-3">
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
            x-transition
            class="fixed inset-0 bg-zinc-900/50"
            wire:click="$set('showDeleteModal', false)"
        ></div>

        <div class="fixed inset-0 z-50 overflow-y-auto p-4 sm:p-6">
            <div class="flex min-h-full items-center justify-center">
                <div
                    x-show="show"
                    x-transition
                    @click.stop
                    class="w-full max-w-md rounded-xl border border-zinc-200 bg-white p-6 shadow-xl"
                >
                    <h3 id="delete-medicine-title" class="text-lg font-semibold text-zinc-900">
                        Konfirmasi Hapus
                    </h3>
                    <p class="mt-3 text-sm text-zinc-600">
                        Apakah Anda yakin ingin menghapus obat
                        <span class="font-semibold text-zinc-900">{{ $deleteMedicineName }}</span>?
                        Tindakan ini tidak dapat dibatalkan.
                    </p>

                    <div class="mt-6 flex justify-end gap-3">
                        <button
                            type="button"
                            wire:click="$set('showDeleteModal', false)"
                            class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50"
                        >
                            Batal
                        </button>
                        <button
                            type="button"
                            wire:click="deleteConfirmed"
                            class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700"
                            wire:loading.attr="disabled"
                            wire:target="deleteConfirmed"
                        >
                            <span wire:loading.remove wire:target="deleteConfirmed">Hapus</span>
                            <span wire:loading wire:target="deleteConfirmed">Menghapus...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
