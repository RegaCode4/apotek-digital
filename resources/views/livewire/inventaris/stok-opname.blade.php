<div>
    @if ($successMessage || session('success'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ $successMessage ?? session('success') }}
        </div>
    @endif

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-zinc-900">Stok Opname</h2>
            <p class="mt-1 text-sm text-zinc-500">Sesuaikan stok fisik dengan stok di sistem</p>
            <p class="mt-2 text-sm text-zinc-600">
                Stok opname terakhir:
                @if ($lastOpnameAt)
                    <span class="font-medium text-zinc-900">{{ $lastOpnameAt->format('d M Y, H:i') }}</span>
                @else
                    <span class="text-zinc-400">Belum pernah dilakukan</span>
                @endif
            </p>
        </div>
    </div>

    <div class="mb-6 rounded-xl border border-zinc-200 bg-white p-4 shadow-sm">
        <label for="reason" class="mb-1.5 block text-sm font-medium text-zinc-700">
            Alasan Penyesuaian <span class="text-red-500">*</span>
        </label>
        <textarea
            id="reason"
            rows="2"
            wire:model="reason"
            placeholder="Contoh: Hasil penghitungan fisik stok opname bulan ini"
            class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500 @error('reason') border-red-500 @enderror"
        ></textarea>
        @error('reason')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 text-sm">
                <thead class="bg-zinc-50">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-zinc-600">Nama Obat</th>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-zinc-600">Stok Sistem</th>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-zinc-600">Stok Fisik</th>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-zinc-600">Selisih</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse ($medicines as $medicine)
                        @php
                            $physicalStock = (int) ($physicalStocks[$medicine->id] ?? $medicine->stock);
                            $difference = $physicalStock - $medicine->stock;
                        @endphp
                        <tr wire:key="opname-{{ $medicine->id }}" class="hover:bg-zinc-50/80">
                            <td class="px-4 py-3 font-medium text-zinc-900">{{ $medicine->name }}</td>
                            <td class="px-4 py-3 text-zinc-700">{{ $medicine->stock }}</td>
                            <td class="px-4 py-3">
                                <input
                                    type="number"
                                    min="0"
                                    wire:model.live="physicalStocks.{{ $medicine->id }}"
                                    class="w-28 rounded-lg border border-zinc-300 px-3 py-1.5 text-sm focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500 @error('physicalStocks.'.$medicine->id) border-red-500 @enderror"
                                />
                                @error('physicalStocks.'.$medicine->id)
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </td>
                            <td class="px-4 py-3">
                                @if ($difference < 0)
                                    <span class="inline-flex rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-700">
                                        {{ $difference }}
                                    </span>
                                @elseif ($difference > 0)
                                    <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                                        +{{ $difference }}
                                    </span>
                                @else
                                    <span class="text-zinc-500">0</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center text-sm text-zinc-500">
                                Belum ada data obat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($medicines->isNotEmpty())
            <div class="flex justify-end border-t border-zinc-200 px-4 py-4">
                <button
                    type="button"
                    wire:click="saveAllAdjustments"
                    class="rounded-lg bg-zinc-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-zinc-800"
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
