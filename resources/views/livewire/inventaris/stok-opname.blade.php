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
        <div class="mb-4 card-brutal bg-[var(--color-success-soft)] text-[var(--color-success)] p-4 font-bold text-sm shadow-[4px_4px_0_var(--color-neo-black)] border-2 border-[var(--color-neo-black)] flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <span>{{ session('success') }}</span>
            @if(session('last_so_timestamp'))
                <a href="{{ route('inventaris.stok-opname.cetak', session('last_so_timestamp')) }}" target="_blank" class="px-4 py-2 bg-white text-[var(--color-neo-black)] border-2 border-[var(--color-neo-black)] font-black text-xs hover:bg-[var(--color-neo-mint)] transition-colors shadow-[2px_2px_0_var(--color-neo-black)] whitespace-nowrap active:translate-y-1 active:shadow-none inline-flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 9V3a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v6"/><rect x="6" y="14" width="12" height="8" rx="1"/></svg>
                    Cetak Laporan Terakhir
                </a>
            @endif
        </div>
    @endif

    @if ($hasDraft)
        <div class="mb-4 card-brutal bg-[var(--color-neo-yellow)] text-[var(--color-neo-black)] p-4 font-bold text-sm shadow-[4px_4px_0_var(--color-neo-black)] border-2 border-[var(--color-neo-black)] flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <span class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6">
                    <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path>
                    <path d="M12 9v4"></path>
                    <path d="M12 17h.01"></path>
                </svg>
                <span>Anda sedang melanjutkan sesi Stok Opname yang tersimpan sebagai draf.</span>
            </span>
            <button wire:click="discardDraft" 
                    class="px-4 py-2 bg-white border-2 border-[var(--color-neo-black)] rounded-md hover:bg-[var(--color-neo-pink)] transition-colors text-xs font-black cursor-pointer shadow-[2px_2px_0_var(--color-neo-black)] active:translate-y-1 active:shadow-none"
                    wire:loading.attr="disabled"
                    wire:target="discardDraft">
                <span wire:loading.remove wire:target="discardDraft">Buang Draf & Mulai Ulang</span>
                <span wire:loading wire:target="discardDraft">Membuang...</span>
            </button>
        </div>
    @endif

    <!-- Modal Konfirmasi SO Belum Selesai -->
    <div x-show="showConfirmModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" style="display: none;" x-transition>
        <div class="bg-[var(--color-neo-yellow)] border-4 border-[var(--color-neo-black)] shadow-[8px_8px_0_var(--color-neo-black)] p-6 max-w-lg w-full" @click.away="showConfirmModal = false">
            <div class="flex items-center gap-3 mb-4">
                <span class="text-[var(--color-neo-black)]">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path>
                        <path d="M12 9v4"></path>
                        <path d="M12 17h.01"></path>
                    </svg>
                </span>
                <h3 class="text-2xl font-black text-[var(--color-neo-black)]">SO Belum 100% Selesai</h3>
            </div>
            <p class="font-bold text-[var(--color-neo-black)] mb-6 text-lg">
                Masih ada obat yang belum dihitung (<span x-text="{{ \App\Models\Medicine::count() }} - progress"></span> item). Apakah Anda yakin ingin menyelesaikan sesi ini? Obat yang tidak diisi akan diabaikan (dianggap tidak ada penyesuaian).
            </p>
            <div class="flex justify-end gap-4">
                <button type="button" @click="showConfirmModal = false" class="px-6 py-2 bg-white font-black border-2 border-[var(--color-neo-black)] shadow-[4px_4px_0_var(--color-neo-black)] hover:bg-[var(--color-surface-muted)] transition-colors cursor-pointer active:translate-y-1 active:shadow-none">Batal</button>
                <button type="button" @click="showConfirmModal = false; $wire.saveAllAdjustments()" class="px-6 py-2 bg-[var(--color-neo-pink)] text-white font-black border-2 border-[var(--color-neo-black)] shadow-[4px_4px_0_var(--color-neo-black)] hover:opacity-90 transition-opacity cursor-pointer active:translate-y-1 active:shadow-none">Ya, Tetap Selesaikan</button>
            </div>
        </div>
    </div>

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="text-3xl font-black text-[var(--color-ink)] drop-shadow-[2px_2px_0_var(--color-neo-mint)]">Stok Opname</h2>
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
        <div class="card-brutal bg-[var(--color-surface)] p-3 border-2 border-[var(--color-neo-black)] shadow-[4px_4px_0_var(--color-neo-black)] min-w-[250px]">
            <div class="flex justify-between text-sm font-black text-[var(--color-ink)] mb-2">
                <span>Progress SO</span>
                <span x-text="`${progress} / {{ \App\Models\Medicine::count() }}`"></span>
            </div>
            <div class="w-full h-4 bg-gray-200 border-2 border-[var(--color-neo-black)] rounded-full overflow-hidden">
                <div class="h-full transition-all duration-300 border-r-2 border-[var(--color-neo-black)]" 
                     :style="`background-color: #86efac; width: ${(progress / {{ max(1, \App\Models\Medicine::count()) }}) * 100}%`"></div>
            </div>
        </div>
    </div>

    <!-- Sesi Global / Jeda -->
    <div class="mb-6 card-brutal p-4 bg-[var(--color-neo-yellow)] border-2 border-[var(--color-neo-black)] shadow-[4px_4px_0_var(--color-neo-black)]">
        <label for="reason" class="mb-1.5 block text-sm font-black text-[var(--color-ink)]">
            Catatan Sesi SO (Global) <span class="text-[var(--color-danger)]">*</span>
        </label>
        <textarea
            id="reason"
            rows="2"
            wire:model="reason"
            placeholder="Contoh: Hasil penghitungan fisik stok opname bulan ini"
            class="block w-full input-brutal border-2 border-[var(--color-neo-black)] shadow-[2px_2px_0_var(--color-neo-black)] focus:ring-1 focus:ring-[var(--color-primary)] @error('reason') border-[var(--color-danger)] @enderror"
        ></textarea>
        @error('reason')
            <p class="mt-1 text-xs font-bold text-[var(--color-danger)]">{{ $message }}</p>
        @enderror
    </div>

    <!-- Smart Table Container -->
    <div class="card-brutal bg-[var(--color-surface)] border-2 border-[var(--color-neo-black)] shadow-[6px_6px_0_var(--color-neo-black)] overflow-hidden">
        
        <!-- Sticky Filter Header -->
        <div class="sticky top-0 z-10 bg-[var(--color-surface-muted)] border-b-2 border-[var(--color-neo-black)] p-3 flex flex-wrap gap-3 items-center shadow-sm">
            <span class="font-black text-sm text-[var(--color-ink)]">Filter:</span>
            
            <div class="flex flex-wrap gap-2">
                <button wire:click="$set('filterStatus', 'all')" 
                        class="px-3 py-1 font-bold text-sm rounded-full border-2 border-[var(--color-neo-black)] transition-transform active:translate-y-1 {{ $filterStatus === 'all' ? 'bg-[var(--color-neo-black)] text-white' : 'bg-white shadow-[2px_2px_0_var(--color-neo-black)]' }}">Semua</button>
                <button wire:click="$set('filterStatus', 'pending')" 
                        class="px-3 py-1 font-bold text-sm rounded-full border-2 border-[var(--color-neo-black)] transition-transform active:translate-y-1 {{ $filterStatus === 'pending' ? 'bg-[var(--color-neo-black)] text-white' : 'bg-white shadow-[2px_2px_0_var(--color-neo-black)]' }}">Belum Dihitung</button>
                <button wire:click="$set('filterStatus', 'diff')" 
                        class="px-3 py-1 font-bold text-sm rounded-full border-2 border-[var(--color-neo-black)] transition-transform active:translate-y-1 {{ $filterStatus === 'diff' ? 'bg-[var(--color-neo-pink)] shadow-[2px_2px_0_var(--color-neo-black)]' : 'bg-white shadow-[2px_2px_0_var(--color-neo-black)]' }}">Ada Selisih</button>
                <button wire:click="$set('filterStatus', 'match')" 
                        class="px-3 py-1 font-bold text-sm rounded-full border-2 border-[var(--color-neo-black)] transition-transform active:translate-y-1 {{ $filterStatus === 'match' ? 'bg-[var(--color-neo-black)] text-white' : 'bg-white shadow-[2px_2px_0_var(--color-neo-black)]' }}">Sesuai</button>
            </div>

            <div class="ml-auto flex flex-wrap items-center gap-3">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama obat..." class="input-brutal border-2 border-[var(--color-neo-black)] text-sm py-1 font-bold shadow-[2px_2px_0_var(--color-neo-black)] focus:ring-1 focus:ring-[var(--color-neo-black)] w-full sm:w-48">
                <div class="flex items-center gap-2">
                    <label class="text-sm font-bold text-[var(--color-ink)] whitespace-nowrap">Kategori:</label>
                    <select wire:model.live="filterCategoryId" class="input-brutal border-2 border-[var(--color-neo-black)] text-sm py-1 font-bold shadow-[2px_2px_0_var(--color-neo-black)] focus:ring-1 focus:ring-[var(--color-neo-black)]">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-[var(--color-surface-muted)] border-b-2 border-[var(--color-neo-black)]">
                    <tr>
                        <th class="px-4 py-3 text-left font-black text-[var(--color-ink)]">Nama Obat & Kategori</th>
                        <th class="px-4 py-3 text-center font-black text-[var(--color-ink)]">Stok Sistem</th>
                        <th class="px-4 py-3 text-center font-black text-[var(--color-ink)]">Stok Fisik (Input)</th>
                        <th class="px-4 py-3 text-center font-black text-[var(--color-ink)]">Selisih</th>
                        <th class="px-4 py-3 text-left font-black text-[var(--color-ink)] w-48">Alasan Penyesuaian</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-[var(--color-brutal)]">
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
                                'bg-[var(--color-neo-pink)] border-y-2 border-y-[var(--color-neo-black)]': state === 'diff',
                                'bg-[var(--color-neo-mint-light)] opacity-75': state === 'match',
                                'hover:bg-[var(--color-primary-soft)]': state === 'pending'
                            }"
                            class="transition-colors duration-150"
                            wire:key="row-{{ $medicine->id }}">
                            
                            <!-- Nama Obat & Kategori -->
                            <td class="px-4 py-3">
                                <div class="font-bold text-base text-[var(--color-ink)]">{{ $medicine->name }}</div>
                                <div class="text-xs font-bold text-[var(--color-muted)] uppercase tracking-wider mt-0.5">{{ $medicine->category?->name ?? 'Tanpa Kategori' }}</div>
                            </td>

                            <!-- Stok Sistem -->
                            <td class="px-4 py-3 text-center font-black text-[var(--color-muted)] text-lg" x-text="sys"></td>

                            <!-- Stok Fisik (Input Alpine) -->
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <input type="number" min="0" x-model="phys"
                                        @focus="$event.target.select()"
                                        @keydown.enter.prevent="$event.target.closest('tr').nextElementSibling?.querySelector('input[type=number]')?.focus()"
                                        class="w-24 input-brutal py-1 text-center font-bold text-lg border-2 border-[var(--color-neo-black)] focus:shadow-[2px_2px_0_var(--color-neo-black)]" />
                                    
                                    <!-- Tombol Match -->
                                    <button type="button" @click="matchFast" title="Sesuai Sistem"
                                            class="px-2 py-1.5 font-black text-xs border-2 border-[var(--color-neo-black)] rounded-md hover:bg-[var(--color-neo-black)] hover:text-white transition-colors cursor-pointer bg-white shadow-[2px_2px_0_var(--color-neo-black)] active:translate-y-1 active:shadow-none whitespace-nowrap"
                                            x-show="state === 'pending'">
                                        Sesuai
                                    </button>
                                </div>
                            </td>

                            <!-- Selisih -->
                            <td class="px-4 py-3 text-center font-black text-lg">
                                <span x-show="state === 'pending'" class="text-[var(--color-muted)]">-</span>
                                <span x-show="state === 'match'" class="text-[var(--color-success)] font-extrabold text-xl">✔</span>
                                <span x-show="state === 'diff'" 
                                      :class="diff > 0 ? 'text-[var(--color-success)]' : 'text-[var(--color-danger)]'" 
                                      x-text="diff > 0 ? `+${diff}` : diff"></span>
                            </td>

                            <!-- Alasan (Hanya muncul jika selisih != 0) -->
                            <td class="px-4 py-3">
                                <div x-show="state === 'diff'" style="display: none;" :style="{ display: state === 'diff' ? 'block' : 'none' }">
                                    <select wire:model="itemReasons.{{ $medicine->id }}" 
                                            class="input-brutal w-full text-xs font-bold border-2 border-[var(--color-neo-black)] shadow-[2px_2px_0_var(--color-neo-black)] py-1 focus:ring-1 focus:ring-[var(--color-neo-black)]">
                                        <option value="">Pilih Alasan...</option>
                                        <option value="Rusak">Rusak</option>
                                        <option value="Hilang">Hilang</option>
                                        <option value="Salah Hitung">Salah Hitung Sebelumnya</option>
                                        <option value="Kadaluarsa">Kadaluarsa</option>
                                    </select>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center font-bold text-[var(--color-muted)]">
                                Tidak ada data obat yang sesuai filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="p-4 border-t-2 border-[var(--color-neo-black)] bg-white">
            {{ $medicines->links() }}
        </div>
        
        <div class="bg-[var(--color-surface-muted)] border-t-2 border-[var(--color-neo-black)] px-4 py-4 flex flex-col sm:flex-row justify-end gap-3">
            <button wire:click="saveDraft" 
                    class="btn-brutal px-6 py-2.5 bg-white text-[var(--color-ink)] border-2 border-[var(--color-neo-black)] font-black shadow-[4px_4px_0_var(--color-neo-black)] hover:translate-y-1 hover:shadow-[2px_2px_0_var(--color-neo-black)] transition-all cursor-pointer"
                    wire:loading.attr="disabled"
                    wire:target="saveDraft, saveAllAdjustments">
                <span wire:loading.remove wire:target="saveDraft">Simpan Sementara</span>
                <span wire:loading wire:target="saveDraft">Menyimpan...</span>
            </button>
            <button type="button" @click="confirmSubmit()" 
                    class="btn-brutal px-6 py-2.5 bg-[var(--color-neo-mint)] text-[var(--color-ink)] border-2 border-[var(--color-neo-black)] font-black shadow-[4px_4px_0_var(--color-neo-black)] hover:translate-y-1 hover:shadow-[2px_2px_0_var(--color-neo-black)] transition-all cursor-pointer"
                    wire:loading.attr="disabled"
                    wire:target="saveDraft, saveAllAdjustments">
                <span wire:loading.remove wire:target="saveAllAdjustments">Simpan & Selesaikan SO</span>
                <span wire:loading wire:target="saveAllAdjustments">Memproses...</span>
            </button>
        </div>
    </div>
</div>
