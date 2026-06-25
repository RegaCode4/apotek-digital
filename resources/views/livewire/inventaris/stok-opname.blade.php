<div>
    @if ($successMessage || session('success'))
        <div class="mb-4 card-brutal bg-[var(--color-success-soft)] text-[var(--color-success)] p-4 font-bold text-sm">
            {{ $successMessage ?? session('success') }}
        </div>
    @endif

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-[var(--color-ink)]">Stok Opname</h2>
            <p class="mt-1 text-sm font-semibold text-[var(--color-muted)]">Sesuaikan stok fisik dengan stok di sistem</p>
            <p class="mt-2 text-sm text-[var(--color-muted)] font-medium">
                Stok opname terakhir:
                @if ($lastOpnameAt)
                    <span class="font-extrabold text-[var(--color-ink)]">{{ $lastOpnameAt->format('d M Y, H:i') }}</span>
                @else
                    <span class="font-bold text-[var(--color-muted)]">Belum pernah dilakukan</span>
                @endif
            </p>
        </div>
    </div>

    <div class="mb-6 card-brutal p-4 bg-[var(--color-surface)]">
        <label for="reason" class="mb-1.5 block text-sm font-bold text-[var(--color-ink)]">
            Alasan Penyesuaian <span class="text-[var(--color-danger)]">*</span>
        </label>
        <textarea
            id="reason"
            rows="2"
            wire:model="reason"
            placeholder="Contoh: Hasil penghitungan fisik stok opname bulan ini"
            class="block w-full input-brutal focus:ring-1 focus:ring-[var(--color-primary)] @error('reason') border-[var(--color-danger)] @enderror"
        ></textarea>
        @error('reason')
            <p class="mt-1 text-xs font-bold text-[var(--color-danger)]">{{ $message }}</p>
        @enderror
    </div>

    <div class="overflow-hidden card-brutal bg-[var(--color-surface)]">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y-2 divide-[var(--color-brutal)] text-sm">
                <thead class="bg-[var(--color-surface-muted)] text-[var(--color-ink)]">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left font-bold">Nama Obat</th>
                        <th scope="col" class="px-4 py-3 text-center font-bold">Stok Sistem</th>
                        <th scope="col" class="px-4 py-3 text-center font-bold">Stok Fisik</th>
                        <th scope="col" class="px-4 py-3 text-center font-bold">Selisih</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-[var(--color-brutal)]">
                    @forelse ($medicines as $medicine)
                        @php
                            $physicalStock = (int) ($physicalStocks[$medicine->id] ?? $medicine->stock);
                            $difference = $physicalStock - $medicine->stock;
                        @endphp
                        <tr wire:key="opname-{{ $medicine->id }}" class="hover:bg-[var(--color-primary-soft)] transition-colors duration-150">
                            <td class="px-4 py-3 font-bold text-[var(--color-ink)]">{{ $medicine->name }}</td>
                            <td class="px-4 py-3 text-center font-semibold text-[var(--color-muted)]">{{ $medicine->stock }}</td>
                            <td class="px-4 py-3 text-center">
                                <input
                                    type="number"
                                    min="0"
                                    wire:model.live="physicalStocks.{{ $medicine->id }}"
                                    class="w-28 input-brutal py-1.5 focus:ring-1 focus:ring-[var(--color-primary)] @error('physicalStocks.'.$medicine->id) border-[var(--color-danger)] @enderror"
                                />
                                @error('physicalStocks.'.$medicine->id)
                                    <p class="mt-1 text-xs font-bold text-[var(--color-danger)]">{{ $message }}</p>
                                @enderror
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($difference < 0)
                                    <span class="badge-brutal bg-[var(--color-danger-soft)] text-[var(--color-danger)] text-xs font-bold shadow-[1px_1px_0_var(--color-brutal)]">
                                        {{ $difference }}
                                    </span>
                                @elseif ($difference > 0)
                                    <span class="badge-brutal bg-[var(--color-success-soft)] text-[var(--color-success)] text-xs font-bold shadow-[1px_1px_0_var(--color-brutal)]">
                                        +{{ $difference }}
                                    </span>
                                @else
                                    <span class="text-[var(--color-muted)] font-bold">0</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center text-sm font-bold text-[var(--color-muted)]">
                                Belum ada data obat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($medicines->isNotEmpty())
            <div class="flex justify-end border-t-2 border-[var(--color-brutal)] px-4 py-4 bg-[var(--color-surface-muted)]">
                <button
                    type="button"
                    wire:click="saveAllAdjustments"
                    class="btn-brutal btn-primary px-4 py-2.5 text-sm font-bold cursor-pointer shadow-[2px_2px_0_var(--color-brutal)]"
                    wire:loading.attr="disabled"
                    wire:target="saveAllAdjustments"
                >
                    <span wire:loading.remove wire:target="saveAllAdjustments">Simpan Semua Adjustment</span>
                    <span wire:loading wire:target="saveAllAdjustments">Menyimpan...</span>
                </button>
            </div>
        @endif
    </div>
</div>
