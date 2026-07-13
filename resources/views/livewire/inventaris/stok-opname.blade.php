<div x-data="{
    calculatedCount: @entangle('physicalStocks').live,
    showConfirmModal: false,
    get progress() {
        return Object.values(this.calculatedCount).filter(val => val !== '' && val !== null).length;
    },
    confirmSubmit() {
        if (this.progress < {{ \App\Models\Medicine::count() }}) {
            this.showConfirmModal = true;
        } else {
            $wire.saveAllAdjustments();
        }
    }
}">
    @if (session('success'))
        <div class="mb-4 card-brutal bg-[var(--color-success-soft)] text-[var(--color-success)] p-4 font-bold text-sm flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <span>{{ session('success') }}</span>
            @if(session('last_so_timestamp'))
                <a href="{{ route('inventaris.stok-opname.cetak', session('last_so_timestamp')) }}" target="_blank" class="btn-brutal btn-secondary px-4 py-2 text-xs flex items-center gap-2 shadow-[2px_2px_0_var(--color-brutal)]">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 9V3a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v6"/><rect x="6" y="14" width="12" height="8" rx="1"/></svg>
                    Cetak Laporan Terakhir
                </a>
            @endif
        </div>
    @endif

    @if ($hasDraft)
        <div class="mb-4 card-brutal bg-[var(--color-warning-soft)] text-[var(--color-warning)] p-4 font-bold text-sm flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <span class="flex items-center gap-2 text-[var(--color-ink)]">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 text-[var(--color-warning)]">
                    <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path>
                    <path d="M12 9v4"></path>
                    <path d="M12 17h.01"></path>
                </svg>
                <span>Anda sedang melanjutkan sesi Stok Opname yang tersimpan sebagai draf.</span>
            </span>
            <button wire:click="discardDraft" 
                    class="btn-brutal btn-danger bg-[var(--color-danger-soft)] text-[var(--color-danger)] hover:text-white px-4 py-2 text-xs font-bold cursor-pointer shadow-[2px_2px_0_var(--color-brutal)]"
                    wire:loading.attr="disabled"
                    wire:target="discardDraft">
                <span wire:loading.remove wire:target="discardDraft">Buang Draf & Mulai Ulang</span>
                <span wire:loading wire:target="discardDraft">Membuang...</span>
            </button>
        </div>
    @endif

    <!-- Modal Konfirmasi SO Belum Selesai -->
    <div
        x-show="showConfirmModal"
        x-cloak
        class="relative z-50"
        role="dialog"
        aria-modal="true"
        aria-labelledby="confirm-so-title"
    >
        <div
            x-show="showConfirmModal"
            x-transition.opacity
            class="fixed inset-0 bg-[var(--color-brutal)]/40 backdrop-blur-xs"
        ></div>

        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div
                x-show="showConfirmModal"
                x-transition
                @click.away="showConfirmModal = false"
                class="w-full max-w-md card-brutal p-6 card-brutal-lg bg-[var(--color-surface)]"
            >
                {{-- Icon Peringatan --}}
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-[var(--color-warning-soft)] border-2 border-[var(--color-brutal)] shadow-[2px_2px_0_var(--color-brutal)]">
                    <svg class="h-6 w-6 text-[var(--color-warning)]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path>
                        <path d="M12 9v4"></path>
                        <path d="M12 17h.01"></path>
                    </svg>
                </div>

                <h3 id="confirm-so-title" class="text-lg font-bold text-[var(--color-ink)]">
                    SO Belum 100% Selesai
                </h3>
                
                <p class="mt-2 text-sm font-semibold text-[var(--color-muted)] leading-relaxed">
                    Masih ada obat yang belum dihitung (<span x-text="{{ \App\Models\Medicine::count() }} - progress" class="font-extrabold text-[var(--color-ink)]"></span> item). Apakah Anda yakin ingin menyelesaikan sesi ini? Obat yang tidak diisi akan diabaikan (dianggap tidak ada penyesuaian).
                </p>

                <div class="mt-6 flex flex-col sm:flex-row justify-end gap-2.5">
                    <button
                        type="button"
                        @click="showConfirmModal = false"
                        class="btn-brutal btn-secondary px-4 py-2.5 text-sm font-bold flex-1 text-center cursor-pointer shadow-[2px_2px_0_var(--color-brutal)]"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        @click="showConfirmModal = false; $wire.saveAllAdjustments()"
                        class="btn-brutal btn-danger px-4 py-2.5 text-sm font-bold flex-1 text-center cursor-pointer bg-[var(--color-warning)] text-white hover:opacity-90 shadow-[2px_2px_0_var(--color-brutal)]"
                    >
                        Ya, Selesaikan
                    </button>
                </div>
            </div>
        </div>
    </div>

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

        <!-- Progress Bar Blocky Neubrutal -->
        <div class="card-brutal bg-[var(--color-surface)] p-3 min-w-[250px]">
            <div class="flex justify-between text-sm font-bold text-[var(--color-ink)] mb-2">
                <span>Progress SO</span>
                <span x-text="`${progress} / {{ \App\Models\Medicine::count() }}`"></span>
            </div>
            <div class="w-full h-4 bg-[var(--color-surface-muted)] border-2 border-[var(--color-brutal)] overflow-hidden">
                <div class="h-full transition-all duration-300 border-r-2 border-[var(--color-brutal)] bg-[var(--color-primary)]" 
                     :style="`width: ${(progress / {{ max(1, \App\Models\Medicine::count()) }}) * 100}%`"></div>
            </div>
        </div>
    </div>

    <!-- Sesi Global / Jeda -->
    <div class="mb-6 card-brutal p-4 bg-[var(--color-surface)]">
        <label for="reason" class="mb-1.5 block text-sm font-bold text-[var(--color-ink)]">
            Catatan Sesi SO (Global) <span class="text-[var(--color-danger)]">*</span>
        </label>
        <textarea
            id="reason"
            rows="2"
            wire:model="reason"
            placeholder="Contoh: Hasil penghitungan fisik stok opname bulan ini"
            class="block w-full input-brutal text-sm text-[var(--color-ink)] placeholder-[var(--color-muted)] focus:outline-none focus:ring-1 focus:ring-[var(--color-primary)] @error('reason') border-[var(--color-danger)] @enderror"
        ></textarea>
        @error('reason')
            <p class="mt-1 text-xs font-bold text-[var(--color-danger)]">{{ $message }}</p>
        @enderror
    </div>

    <!-- Smart Table Container -->
    <div class="card-brutal overflow-hidden bg-[var(--color-surface)]">
        
        <!-- Sticky Filter Header -->
        <div class="sticky top-0 z-10 bg-[var(--color-surface-muted)] border-b-2 border-[var(--color-brutal)] p-3 flex flex-wrap gap-3 items-center shadow-sm">
            <span class="font-bold text-sm text-[var(--color-ink)]">Filter:</span>
            
            <div class="flex flex-wrap gap-2">
                <button wire:click="$set('filterStatus', 'all')" 
                        class="px-3 py-1 font-bold text-xs rounded-full border-2 border-[var(--color-brutal)] transition-colors {{ $filterStatus === 'all' ? 'bg-[var(--color-ink)] text-white' : 'bg-white hover:bg-[var(--color-surface)] shadow-[2px_2px_0_var(--color-brutal)]' }}">Semua</button>
                <button wire:click="$set('filterStatus', 'pending')" 
                        class="px-3 py-1 font-bold text-xs rounded-full border-2 border-[var(--color-brutal)] transition-colors {{ $filterStatus === 'pending' ? 'bg-[var(--color-ink)] text-white' : 'bg-white hover:bg-[var(--color-surface)] shadow-[2px_2px_0_var(--color-brutal)]' }}">Belum Dihitung</button>
                <button wire:click="$set('filterStatus', 'diff')" 
                        class="px-3 py-1 font-bold text-xs rounded-full border-2 border-[var(--color-brutal)] transition-colors {{ $filterStatus === 'diff' ? 'bg-[var(--color-danger)] text-white shadow-[2px_2px_0_var(--color-brutal)]' : 'bg-white hover:bg-[var(--color-surface)] shadow-[2px_2px_0_var(--color-brutal)]' }}">Ada Selisih</button>
                <button wire:click="$set('filterStatus', 'match')" 
                        class="px-3 py-1 font-bold text-xs rounded-full border-2 border-[var(--color-brutal)] transition-colors {{ $filterStatus === 'match' ? 'bg-[var(--color-ink)] text-white' : 'bg-white hover:bg-[var(--color-surface)] shadow-[2px_2px_0_var(--color-brutal)]' }}">Sesuai</button>
            </div>

            <div class="ml-auto flex flex-wrap items-center gap-3">
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari nama obat..." class="input-brutal text-sm py-1 font-medium w-full sm:w-48 placeholder-[var(--color-muted)] focus:outline-none">
                <div class="flex items-center gap-2">
                    <label class="text-sm font-bold text-[var(--color-ink)] whitespace-nowrap">Kategori:</label>
                    <div class="w-48">
                        <x-brutal-select 
                            wire:model.live="filterCategoryId"
                            placeholder="Semua Kategori"
                            :options="$categories->pluck('name', 'id')->toArray()"
                        />
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-[var(--color-border-soft)] text-sm">
                <thead class="bg-[var(--color-surface-muted)] border-b-2 border-[var(--color-brutal)]">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-bold text-[var(--color-ink)] whitespace-nowrap">Nama Obat & Kategori</th>
                        <th class="px-4 py-3 text-center text-xs font-bold text-[var(--color-ink)] whitespace-nowrap">Stok Sistem</th>
                        <th class="px-4 py-3 text-center text-xs font-bold text-[var(--color-ink)] whitespace-nowrap">Stok Fisik (Input)</th>
                        <th class="px-4 py-3 text-center text-xs font-bold text-[var(--color-ink)] whitespace-nowrap">Selisih</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-[var(--color-ink)] min-w-[200px] whitespace-nowrap">Alasan Penyesuaian</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--color-border-soft)]">
                    @forelse ($medicines as $medicine)
                        <tr x-data="{
                                sys: {{ $medicine->stock }},
                                phys: @entangle('physicalStocks.'.$medicine->id),
                                get diff() { return (this.phys === null || this.phys === '' || typeof this.phys === 'undefined') ? null : parseInt(this.phys) - this.sys; },
                                get state() {
                                    if (this.phys === null || this.phys === '' || typeof this.phys === 'undefined') return 'pending';
                                    return this.diff === 0 ? 'match' : 'diff';
                                },
                                matchFast() { this.phys = this.sys; }
                            }"
                            :class="{
                                'bg-[var(--color-danger-soft)]': state === 'diff',
                                'bg-[var(--color-success-soft)]': state === 'match',
                                'hover:bg-[var(--color-primary-soft)]/50': state === 'pending'
                            }"
                            class="transition-colors duration-150"
                            wire:key="row-{{ $medicine->id }}">
                            
                            <!-- Nama Obat & Kategori -->
                            <td class="px-4 py-3">
                                <div class="font-bold text-[var(--color-ink)]">{{ $medicine->name }}</div>
                                <div class="text-xs font-semibold text-[var(--color-muted)] mt-0.5">{{ $medicine->category?->name ?? 'Tanpa Kategori' }}</div>
                            </td>

                            <!-- Stok Sistem -->
                            <td class="px-4 py-3 text-center font-bold text-[var(--color-ink)] text-base" x-text="sys"></td>

                            <!-- Stok Fisik (Input Alpine) -->
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <input type="number" min="0" x-model="phys"
                                        @focus="$event.target.select()"
                                        @keydown.enter.prevent="$event.target.closest('tr').nextElementSibling?.querySelector('input[type=number]')?.focus()"
                                        class="w-20 input-brutal py-1 text-center font-semibold text-sm border-2 border-[var(--color-brutal)] focus:ring-1 focus:ring-[var(--color-primary)]" />
                                    
                                    <!-- Tombol Match -->
                                    <button type="button" @click="matchFast" title="Sesuai Sistem"
                                            class="px-2 py-1.5 font-bold text-xs border-2 border-[var(--color-brutal)] rounded-md hover:bg-[var(--color-surface)] transition-colors cursor-pointer bg-white shadow-[2px_2px_0_var(--color-brutal)] active:translate-y-1 active:shadow-none whitespace-nowrap"
                                            x-show="state === 'pending'">
                                        Sesuai
                                    </button>
                                </div>
                            </td>

                            <!-- Selisih -->
                            <td class="px-4 py-3 text-center font-bold text-base">
                                <span x-show="state === 'pending'" class="text-[var(--color-muted)]">-</span>
                                <span x-show="state === 'match'" class="text-[var(--color-success)] font-extrabold">✔</span>
                                <span x-show="state === 'diff'" 
                                      :class="diff > 0 ? 'text-[var(--color-success)]' : 'text-[var(--color-danger)]'" 
                                      x-text="diff > 0 ? `+${diff}` : diff"></span>
                            </td>

                            <!-- Alasan (Hanya muncul jika selisih != 0) -->
                            <td class="px-4 py-3">
                                <div x-show="state === 'diff'" style="display: none;" :style="{ display: state === 'diff' ? 'block' : 'none' }">
                                    <x-brutal-select 
                                        wire:model="itemReasons.{{ $medicine->id }}"
                                        placeholder="Pilih Alasan..."
                                        :options="[
                                            'Rusak' => 'Rusak',
                                            'Hilang' => 'Hilang',
                                            'Salah Hitung' => 'Salah Hitung Sebelumnya',
                                            'Kadaluarsa' => 'Kadaluarsa'
                                        ]"
                                    />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-sm font-bold text-[var(--color-muted)]">
                                Tidak ada data obat yang sesuai filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="border-t-2 border-[var(--color-brutal)] px-4 py-3 bg-[var(--color-surface-muted)]">
            {{ $medicines->links() }}
        </div>
        
        <div class="bg-[var(--color-surface)] border-t-2 border-[var(--color-brutal)] px-4 py-4 flex flex-col sm:flex-row justify-end gap-3">
            <button wire:click="saveDraft" 
                    class="btn-brutal btn-secondary px-6 py-2 text-sm font-bold cursor-pointer shadow-[2px_2px_0_var(--color-brutal)]"
                    wire:loading.attr="disabled"
                    wire:target="saveDraft, saveAllAdjustments">
                <span wire:loading.remove wire:target="saveDraft">Simpan Sementara</span>
                <span wire:loading wire:target="saveDraft">Menyimpan...</span>
            </button>
            <button type="button" @click="confirmSubmit()" 
                    class="btn-brutal btn-primary px-6 py-2 text-sm font-bold cursor-pointer"
                    wire:loading.attr="disabled"
                    wire:target="saveDraft, saveAllAdjustments">
                <span wire:loading.remove wire:target="saveAllAdjustments">Simpan & Selesaikan SO</span>
                <span wire:loading wire:target="saveAllAdjustments">Memproses...</span>
            </button>
        </div>
    </div>
</div>
