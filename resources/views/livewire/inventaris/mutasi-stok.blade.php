<div>
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-zinc-900">Riwayat Mutasi Stok</h2>
            <p class="mt-1 text-sm text-zinc-500">Audit trail keluar-masuk stok obat</p>
        </div>

        <button
            type="button"
            wire:click="exportCsv"
            class="inline-flex items-center justify-center rounded-lg border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 hover:bg-zinc-50"
            wire:loading.attr="disabled"
            wire:target="exportCsv"
        >
            <span wire:loading.remove wire:target="exportCsv">Export CSV</span>
            <span wire:loading wire:target="exportCsv">Mengekspor...</span>
        </button>
    </div>

    <div class="mb-6 rounded-xl border border-zinc-200 bg-white p-4 shadow-sm">
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div>
                <label for="type" class="mb-1.5 block text-sm font-medium text-zinc-700">Tipe Mutasi</label>
                <select
                    id="type"
                    wire:model.live="type"
                    class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500"
                >
                    <option value="">Semua tipe</option>
                    <option value="in">Masuk</option>
                    <option value="out">Keluar</option>
                    <option value="adjustment">Penyesuaian</option>
                    <option value="expired_return">Retur Kedaluwarsa</option>
                </select>
            </div>

            <div>
                <label for="search" class="mb-1.5 block text-sm font-medium text-zinc-700">Nama Obat</label>
                <input
                    id="search"
                    type="search"
                    wire:model.live="search"
                    placeholder="Cari nama obat..."
                    class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500"
                />
            </div>

            <div>
                <label for="dateFrom" class="mb-1.5 block text-sm font-medium text-zinc-700">Dari Tanggal</label>
                <input
                    id="dateFrom"
                    type="date"
                    wire:model.live="dateFrom"
                    class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500"
                />
            </div>

            <div>
                <label for="dateTo" class="mb-1.5 block text-sm font-medium text-zinc-700">Sampai Tanggal</label>
                <input
                    id="dateTo"
                    type="date"
                    wire:model.live="dateTo"
                    class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500"
                />
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 text-sm">
                <thead class="bg-zinc-50">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-zinc-600">Tanggal</th>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-zinc-600">Nama Obat</th>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-zinc-600">Tipe</th>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-zinc-600">Jumlah</th>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-zinc-600">Referensi</th>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-zinc-600">Catatan</th>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-zinc-600">Dicatat Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse ($mutations as $mutation)
                        <tr wire:key="mutation-{{ $mutation->id }}" class="hover:bg-zinc-50/80">
                            <td class="px-4 py-3 text-zinc-700 whitespace-nowrap">
                                {{ $mutation->created_at?->format('d M Y, H:i') }}
                            </td>
                            <td class="px-4 py-3 font-medium text-zinc-900">
                                {{ $mutation->medicine?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $badgeClasses = match ($mutation->type) {
                                        'in' => 'bg-emerald-100 text-emerald-800',
                                        'out' => 'bg-red-100 text-red-700',
                                        'adjustment' => 'bg-amber-100 text-amber-800',
                                        'expired_return' => 'bg-zinc-200 text-zinc-700',
                                        default => 'bg-zinc-100 text-zinc-600',
                                    };
                                @endphp
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badgeClasses }}">
                                    {{ $this->typeLabel($mutation->type) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-zinc-700">
                                @if ($mutation->quantity > 0)
                                    +{{ $mutation->quantity }}
                                @else
                                    {{ $mutation->quantity }}
                                @endif
                            </td>
                            <td class="px-4 py-3 text-zinc-600">
                                {{ $mutation->reference_id ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-zinc-600 max-w-xs truncate" title="{{ $mutation->notes }}">
                                {{ $mutation->notes ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-zinc-700">
                                {{ $mutation->createdBy?->name ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-sm text-zinc-500">
                                Tidak ada data mutasi stok yang sesuai filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($mutations->hasPages())
            <div class="border-t border-zinc-200 px-4 py-3">
                {{ $mutations->links() }}
            </div>
        @endif
    </div>
</div>
