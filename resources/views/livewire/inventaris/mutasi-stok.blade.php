<div>
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-[var(--color-ink)]">Riwayat Mutasi Stok</h2>
            <p class="mt-1 text-sm font-semibold text-[var(--color-muted)]">Audit trail keluar-masuk stok obat</p>
        </div>

        <x-export-dropdown actionCsv="exportCsv" actionPdf="exportPdf" />
    </div>

    <div class="mb-6 card-brutal p-4 bg-[var(--color-surface)]">
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div>
                <label for="type" class="mb-1.5 block text-sm font-bold text-[var(--color-ink)]">Tipe Mutasi</label>
                <x-brutal-select 
                    id="type"
                    wire:model.live="type"
                    placeholder="Semua tipe"
                    :options="[
                        'in' => 'Masuk',
                        'out' => 'Keluar',
                        'adjustment' => 'Penyesuaian',
                        'expired_return' => 'Retur Kedaluwarsa'
                    ]"
                />
            </div>

            <div>
                <label for="search" class="mb-1.5 block text-sm font-bold text-[var(--color-ink)]">Nama Obat</label>
                <input
                    id="search"
                    type="search"
                    wire:model.live="search"
                    placeholder="Cari nama obat..."
                    class="block w-full input-brutal focus:ring-1 focus:ring-[var(--color-primary)]"
                />
            </div>

            <div>
                <label for="dateFrom" class="mb-1.5 block text-sm font-bold text-[var(--color-ink)]">Dari Tanggal</label>
                <input
                    id="dateFrom"
                    type="date"
                    wire:model.live="dateFrom"
                    class="block w-full input-brutal focus:ring-1 focus:ring-[var(--color-primary)]"
                />
            </div>

            <div>
                <label for="dateTo" class="mb-1.5 block text-sm font-bold text-[var(--color-ink)]">Sampai Tanggal</label>
                <input
                    id="dateTo"
                    type="date"
                    wire:model.live="dateTo"
                    class="block w-full input-brutal focus:ring-1 focus:ring-[var(--color-primary)]"
                />
            </div>
        </div>
    </div>

    <div class="overflow-hidden card-brutal bg-[var(--color-surface)]">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y-2 divide-[var(--color-brutal)] text-sm">
                <thead class="bg-[var(--color-surface-muted)] text-[var(--color-ink)]">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left font-bold">Tanggal</th>
                        <th scope="col" class="px-4 py-3 text-left font-bold">Nama Obat</th>
                        <th scope="col" class="px-4 py-3 text-center font-bold">Tipe</th>
                        <th scope="col" class="px-4 py-3 text-center font-bold">Jumlah</th>
                        <th scope="col" class="px-4 py-3 text-center font-bold">Referensi</th>
                        <th scope="col" class="px-4 py-3 text-left font-bold">Catatan</th>
                        <th scope="col" class="px-4 py-3 text-left font-bold">Dicatat Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-[var(--color-brutal)]">
                    @forelse ($mutations as $mutation)
                        <tr wire:key="mutation-{{ $mutation->id }}" class="hover:bg-[var(--color-primary-soft)] transition-colors duration-150">
                            <td class="px-4 py-3 text-[var(--color-ink)] font-semibold whitespace-nowrap">
                                {{ $mutation->created_at?->format('d M Y, H:i') }}
                            </td>
                            <td class="px-4 py-3 font-bold text-[var(--color-ink)]">
                                {{ $mutation->medicine?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @php
                                    $badgeClasses = match ($mutation->type) {
                                        'in' => 'bg-[var(--color-success-soft)] text-[var(--color-success)]',
                                        'out' => 'bg-[var(--color-danger-soft)] text-[var(--color-danger)]',
                                        'adjustment' => 'bg-[var(--color-warning-soft)] text-[var(--color-warning)]',
                                        'expired_return' => 'bg-[var(--color-surface-muted)] text-[var(--color-muted)]',
                                        default => 'bg-[var(--color-surface-muted)] text-[var(--color-muted)]',
                                    };
                                @endphp
                                <span class="badge-brutal px-2.5 py-0.5 text-xs font-bold shadow-[1px_1px_0_var(--color-brutal)] {{ $badgeClasses }}">
                                    {{ $this->typeLabel($mutation->type) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center text-[var(--color-ink)] font-bold">
                                @if ($mutation->quantity > 0)
                                    +{{ $mutation->quantity }}
                                @else
                                    {{ $mutation->quantity }}
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center text-[var(--color-muted)] font-medium">
                                {{ $mutation->reference_id ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-[var(--color-muted)] font-medium max-w-xs truncate" title="{{ $mutation->notes }}">
                                {{ $mutation->notes ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-[var(--color-ink)] font-semibold">
                                {{ $mutation->createdBy?->name ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-sm font-bold text-[var(--color-muted)]">
                                Tidak ada data mutasi stok yang sesuai filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($mutations->hasPages())
            <div class="border-t-2 border-[var(--color-brutal)] px-4 py-3 bg-[var(--color-surface-muted)]">
                {{ $mutations->links() }}
            </div>
        @endif
    </div>
</div>
