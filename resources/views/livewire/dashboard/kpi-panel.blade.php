<div wire:poll.60000ms="refreshData">

    {{-- ═══════════════════════════════════════════════════════════
         KPI CARDS
    ════════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">

        {{-- Pendapatan Hari Ini --}}
        <div class="card-brutal p-4">
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold uppercase tracking-wide text-[var(--color-muted)]">Pendapatan Hari Ini</p>
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-[var(--color-success-soft)] border-2 border-[var(--color-brutal)] shadow-[1px_1px_0_var(--color-brutal)]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[var(--color-brutal)]" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>
            <p class="mt-3 text-2xl font-bold text-[var(--color-ink)]">
                Rp {{ number_format($todayRevenue, 0, ',', '.') }}
            </p>
            <p class="mt-1 text-xs font-semibold text-[var(--color-muted)]">Total penjualan hari ini</p>
        </div>

        {{-- Jumlah Transaksi --}}
        <div class="card-brutal p-4">
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold uppercase tracking-wide text-[var(--color-muted)]">Transaksi Hari Ini</p>
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-[var(--color-info-soft)] border-2 border-[var(--color-brutal)] shadow-[1px_1px_0_var(--color-brutal)]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[var(--color-brutal)]" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>
            <p class="mt-3 text-2xl font-bold text-[var(--color-ink)]">{{ $todayTransactionCount }}</p>
            <p class="mt-1 text-xs font-semibold text-[var(--color-muted)]">Nota berhasil diproses</p>
        </div>

        {{-- Stok Menipis --}}
        @php $lowStockDanger = $lowStockCount > 0; @endphp
        <button
            type="button"
            wire:click="toggleAlertPanel"
            class="btn-brutal text-left p-4 block w-full transition-all cursor-pointer
                {{ $lowStockDanger
                    ? 'bg-[var(--color-warning-soft)]'
                    : 'bg-[var(--color-surface)] hover:bg-[var(--color-surface-muted)]' }}"
            aria-expanded="{{ $alertPanelOpen && $activeTab === 'low_stock' ? 'true' : 'false' }}"
        >
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold uppercase tracking-wide {{ $lowStockDanger ? 'text-[var(--color-warning)]' : 'text-[var(--color-muted)]' }}">
                    Stok Menipis
                </p>
                <div class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-[var(--color-brutal)] shadow-[1px_1px_0_var(--color-brutal)] {{ $lowStockDanger ? 'bg-[var(--color-warning)] text-white' : 'bg-[var(--color-surface-muted)] text-[var(--color-brutal)]' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>
            <p class="mt-3 text-2xl font-bold text-[var(--color-ink)]">
                {{ $lowStockCount }}
            </p>
            <p class="mt-1 text-xs font-semibold {{ $lowStockDanger ? 'text-[var(--color-warning)]' : 'text-[var(--color-muted)]' }}">
                {{ $lowStockDanger ? 'Klik untuk lihat detail' : 'Semua stok aman' }}
            </p>
        </button>

        {{-- Hampir Kedaluwarsa --}}
        @php $expiryDanger = $expiringSoonCount > 0; @endphp
        <button
            type="button"
            wire:click="toggleAlertPanel"
            class="btn-brutal text-left p-4 block w-full transition-all cursor-pointer
                {{ $expiryDanger
                    ? 'bg-[var(--color-danger-soft)]'
                    : 'bg-[var(--color-surface)] hover:bg-[var(--color-surface-muted)]' }}"
            aria-expanded="{{ $alertPanelOpen && $activeTab === 'expiring_soon' ? 'true' : 'false' }}"
        >
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold uppercase tracking-wide {{ $expiryDanger ? 'text-[var(--color-danger)]' : 'text-[var(--color-muted)]' }}">
                    Hampir Kedaluwarsa
                </p>
                <div class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-[var(--color-brutal)] shadow-[1px_1px_0_var(--color-brutal)] {{ $expiryDanger ? 'bg-[var(--color-danger)] text-white' : 'bg-[var(--color-surface-muted)] text-[var(--color-brutal)]' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>
            <p class="mt-3 text-2xl font-bold text-[var(--color-ink)]">
                {{ $expiringSoonCount }}
            </p>
            <p class="mt-1 text-xs font-semibold {{ $expiryDanger ? 'text-[var(--color-danger)]' : 'text-[var(--color-muted)]' }}">
                {{ $expiryDanger ? 'Klik untuk lihat detail' : 'Tidak ada dalam 3 bulan' }}
            </p>
        </button>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         ALERT PANEL (collapsible)
    ════════════════════════════════════════════════════════════ --}}
    @if ($alertPanelOpen)
        <div class="mt-4 card-brutal">

            {{-- Header tab --}}
            <div class="flex items-center justify-between border-b-2 border-[var(--color-brutal)] bg-[var(--color-surface-muted)] rounded-t-[var(--radius-brutal)] px-4">
                <div class="flex gap-1" role="tablist">
                    <button
                        type="button"
                        role="tab"
                        wire:click="setActiveTab('low_stock')"
                        aria-selected="{{ $activeTab === 'low_stock' ? 'true' : 'false' }}"
                        class="relative px-4 py-3 text-sm font-bold transition-all cursor-pointer
                            {{ $activeTab === 'low_stock'
                                ? 'text-[var(--color-ink)] after:absolute after:bottom-0 after:left-0 after:right-0 after:h-1 after:bg-[var(--color-primary)]'
                                : 'text-[var(--color-muted)] hover:text-[var(--color-ink)]' }}"
                    >
                        Stok Menipis
                        @if ($lowStockCount > 0)
                            <span class="ml-1.5 inline-flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-[var(--color-warning-soft)] px-1 text-[10px] font-bold text-[var(--color-warning)] border border-[var(--color-brutal)]">
                                {{ $lowStockCount }}
                            </span>
                        @endif
                    </button>

                    <button
                        type="button"
                        role="tab"
                        wire:click="setActiveTab('expiring_soon')"
                        aria-selected="{{ $activeTab === 'expiring_soon' ? 'true' : 'false' }}"
                        class="relative px-4 py-3 text-sm font-bold transition-all cursor-pointer
                            {{ $activeTab === 'expiring_soon'
                                ? 'text-[var(--color-ink)] after:absolute after:bottom-0 after:left-0 after:right-0 after:h-1 after:bg-[var(--color-primary)]'
                                : 'text-[var(--color-muted)] hover:text-[var(--color-ink)]' }}"
                    >
                        Hampir Kedaluwarsa
                        @if ($expiringSoonCount > 0)
                            <span class="ml-1.5 inline-flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-[var(--color-danger-soft)] px-1 text-[10px] font-bold text-[var(--color-danger)] border border-[var(--color-brutal)]">
                                {{ $expiringSoonCount }}
                            </span>
                        @endif
                    </button>
                </div>

                <button
                    type="button"
                    wire:click="toggleAlertPanel"
                    class="rounded-md p-1 text-[var(--color-muted)] hover:bg-white/10 hover:text-[var(--color-ink)] border border-transparent hover:border-[var(--color-brutal)] transition-all cursor-pointer"
                    aria-label="Tutup panel alert"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>

            {{-- Konten tab --}}
            <div role="tabpanel" class="overflow-x-auto">

                {{-- TAB: Stok Menipis --}}
                @if ($activeTab === 'low_stock')
                    <table class="min-w-full divide-y divide-[var(--color-border-soft)] text-sm">
                        <thead class="bg-[var(--color-surface-muted)] border-b-2 border-[var(--color-brutal)]">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-[var(--color-ink)]">Nama Obat</th>
                                <th scope="col" class="px-4 py-3 text-center text-xs font-bold text-[var(--color-ink)]">Stok Saat Ini</th>
                                <th scope="col" class="px-4 py-3 text-center text-xs font-bold text-[var(--color-ink)]">Min. Stok</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-[var(--color-ink)]">Kategori</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[var(--color-border-soft)]">
                            @forelse ($lowStockMedicines ?? [] as $medicine)
                                @php $isCritical = $medicine->stock === 0; @endphp
                                <tr wire:key="low-{{ $medicine->id }}" class="{{ $isCritical ? 'bg-[var(--color-danger-soft)]/50' : 'hover:bg-[var(--color-primary-soft)]/40' }} transition-colors">
                                    <td class="px-4 py-2.5 font-bold text-[var(--color-ink)]">{{ $medicine->name }}</td>
                                    <td class="px-4 py-2.5 text-center">
                                        <span class="badge-brutal text-xs font-bold
                                            {{ $isCritical ? 'bg-[var(--color-danger-soft)] text-[var(--color-danger)]' : 'bg-[var(--color-warning-soft)] text-[var(--color-warning)]' }}">
                                            {{ $medicine->stock }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2.5 text-center font-medium text-[var(--color-muted)]">{{ $medicine->min_stock }}</td>
                                    <td class="px-4 py-2.5 text-[var(--color-muted)] font-medium">{{ $medicine->category->name ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-sm text-[var(--color-muted)] font-medium">
                                        Semua stok dalam kondisi aman.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                @endif

                {{-- TAB: Hampir Kedaluwarsa --}}
                @if ($activeTab === 'expiring_soon')
                    <table class="min-w-full divide-y divide-[var(--color-border-soft)] text-sm">
                        <thead class="bg-[var(--color-surface-muted)] border-b-2 border-[var(--color-brutal)]">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-[var(--color-ink)]">Nama Obat</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-[var(--color-ink)]">Tanggal Kedaluwarsa</th>
                                <th scope="col" class="px-4 py-3 text-center text-xs font-bold text-[var(--color-ink)]">Sisa Hari</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-[var(--color-ink)]">Kategori</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[var(--color-border-soft)]">
                            @forelse ($expiringSoonMedicines ?? [] as $medicine)
                                @php
                                    $daysLeft = (int) now()->startOfDay()->diffInDays($medicine->expiry_date, false);
                                    $isUrgent = $daysLeft <= 30;
                                @endphp
                                <tr wire:key="exp-{{ $medicine->id }}" class="{{ $isUrgent ? 'bg-[var(--color-danger-soft)]/50' : 'hover:bg-[var(--color-primary-soft)]/40' }} transition-colors">
                                    <td class="px-4 py-2.5 font-bold text-[var(--color-ink)]">{{ $medicine->name }}</td>
                                    <td class="px-4 py-2.5 font-semibold text-[var(--color-ink)]">
                                        {{ $medicine->expiry_date->format('d M Y') }}
                                    </td>
                                    <td class="px-4 py-2.5 text-center">
                                        <span class="badge-brutal text-xs font-bold
                                            {{ $isUrgent ? 'bg-[var(--color-danger-soft)] text-[var(--color-danger)]' : 'bg-[var(--color-warning-soft)] text-[var(--color-warning)]' }}">
                                            {{ $daysLeft }}h
                                        </span>
                                    </td>
                                    <td class="px-4 py-2.5 text-[var(--color-muted)] font-medium">{{ $medicine->category->name ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-sm text-[var(--color-muted)] font-medium">
                                        Tidak ada obat yang kedaluwarsa dalam 3 bulan ke depan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                @endif

            </div>
        </div>
    @endif

</div>
