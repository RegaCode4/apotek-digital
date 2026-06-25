<div>
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-[var(--color-ink)]">Laporan</h2>
            <p class="mt-1 text-sm font-semibold text-[var(--color-muted)]">Laporan operasional apotek</p>
        </div>
    </div>

    <div class="mb-6 overflow-x-auto">
        <div class="flex flex-wrap gap-2.5 pb-2">
            <button
                type="button"
                wire:click="setTab('penjualan')"
                @class([
                    'px-4 py-2.5 text-sm font-bold cursor-pointer transition-all duration-150',
                    'btn-brutal btn-primary shadow-[2px_2px_0_var(--color-brutal)]' => $activeTab === 'penjualan',
                    'btn-brutal btn-secondary shadow-[2px_2px_0_var(--color-brutal)] bg-[var(--color-surface)] text-[var(--color-ink)]' => $activeTab !== 'penjualan',
                ])
            >
                Penjualan
            </button>

            <button
                type="button"
                wire:click="setTab('stok')"
                @class([
                    'px-4 py-2.5 text-sm font-bold cursor-pointer transition-all duration-150',
                    'btn-brutal btn-primary shadow-[2px_2px_0_var(--color-brutal)]' => $activeTab === 'stok',
                    'btn-brutal btn-secondary shadow-[2px_2px_0_var(--color-brutal)] bg-[var(--color-surface)] text-[var(--color-ink)]' => $activeTab !== 'stok',
                ])
            >
                Stok &amp; Mutasi
            </button>

            <button
                type="button"
                wire:click="setTab('pendapatan')"
                @class([
                    'px-4 py-2.5 text-sm font-bold cursor-pointer transition-all duration-150',
                    'btn-brutal btn-primary shadow-[2px_2px_0_var(--color-brutal)]' => $activeTab === 'pendapatan',
                    'btn-brutal btn-secondary shadow-[2px_2px_0_var(--color-brutal)] bg-[var(--color-surface)] text-[var(--color-ink)]' => $activeTab !== 'pendapatan',
                ])
            >
                Pendapatan per Metode
            </button>

            <button
                type="button"
                wire:click="setTab('kedaluwarsa')"
                @class([
                    'px-4 py-2.5 text-sm font-bold cursor-pointer transition-all duration-150',
                    'btn-brutal btn-primary shadow-[2px_2px_0_var(--color-brutal)]' => $activeTab === 'kedaluwarsa',
                    'btn-brutal btn-secondary shadow-[2px_2px_0_var(--color-brutal)] bg-[var(--color-surface)] text-[var(--color-ink)]' => $activeTab !== 'kedaluwarsa',
                ])
            >
                Obat Kedaluwarsa &amp; Low Stock
            </button>
        </div>
    </div>

    @if ($activeTab === 'penjualan')
        {{-- ── Export button ── --}}
        <div class="mb-4 flex justify-end">
            <button
                type="button"
                wire:click="exportSalesCsv"
                wire:loading.attr="disabled"
                wire:target="exportSalesCsv"
                class="btn-brutal btn-secondary px-4 py-2.5 text-sm font-bold cursor-pointer shadow-[2px_2px_0_var(--color-brutal)]"
            >
                <span wire:loading.remove wire:target="exportSalesCsv">Export CSV</span>
                <span wire:loading wire:target="exportSalesCsv">Mengekspor...</span>
            </button>
        </div>

        {{-- ── Filter panel ── --}}
        <div class="mb-6 card-brutal p-4 bg-[var(--color-surface)]">
            <div class="grid gap-4 md:grid-cols-3">
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

                <div>
                    <label for="paymentMethod" class="mb-1.5 block text-sm font-bold text-[var(--color-ink)]">Metode Pembayaran</label>
                    <select
                        id="paymentMethod"
                        wire:model.live="paymentMethod"
                        class="block w-full input-brutal focus:ring-1 focus:ring-[var(--color-primary)]"
                    >
                        <option value="">Semua metode</option>
                        <option value="cash">Cash</option>
                        <option value="transfer">Transfer</option>
                        <option value="bpjs">BPJS</option>
                        <option value="insurance">Asuransi</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- ── Summary cards ── --}}
        <div class="mb-6 card-brutal p-4 bg-[var(--color-surface)]">
            <div class="flex items-center divide-x-2 divide-[var(--color-brutal)]">
                <div class="flex-1 px-4 first:pl-0">
                    <p class="text-sm font-semibold text-[var(--color-muted)]">Total Transaksi</p>
                    <p class="mt-1 text-2xl font-extrabold text-[var(--color-ink)]">{{ $salesSummary['total_transaksi'] }}</p>
                </div>
                <div class="flex-1 px-4">
                    <p class="text-sm font-semibold text-[var(--color-muted)]">Total Pendapatan</p>
                    <p class="mt-1 text-2xl font-extrabold text-[var(--color-ink)]">
                        Rp {{ number_format($salesSummary['total_pendapatan'], 0, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>

        {{-- ── Table ── --}}
        <div class="overflow-hidden card-brutal bg-[var(--color-surface)]">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y-2 divide-[var(--color-brutal)] text-sm">
                    <thead class="bg-[var(--color-surface-muted)] text-[var(--color-ink)]">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left font-bold">No. Invoice</th>
                            <th scope="col" class="px-4 py-3 text-left font-bold">Tanggal</th>
                            <th scope="col" class="px-4 py-3 text-left font-bold">Pembeli</th>
                            <th scope="col" class="px-4 py-3 text-left font-bold">Kasir</th>
                            <th scope="col" class="px-4 py-3 text-center font-bold">Total Item</th>
                            <th scope="col" class="px-4 py-3 text-right font-bold">Grand Total</th>
                            <th scope="col" class="px-4 py-3 text-left font-bold">Metode Bayar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-[var(--color-brutal)]">
                        @forelse ($sales as $sale)
                            @php
                                $paymentBadge = match ($sale->payment_method) {
                                    'cash'      => 'bg-[var(--color-surface-muted)] text-[var(--color-ink)]',
                                    'transfer'  => 'bg-[var(--color-info-soft)] text-[var(--color-ink)]',
                                    'bpjs'      => 'bg-[var(--color-success-soft)] text-[var(--color-success)]',
                                    'insurance' => 'bg-[var(--color-primary-soft)] text-[var(--color-primary-contrast)]',
                                    default     => 'bg-[var(--color-surface-muted)] text-[var(--color-muted)]',
                                };
                            @endphp
                            <tr wire:key="sale-{{ $sale->id }}" class="transition-colors hover:bg-[var(--color-primary-soft)]">
                                <td class="whitespace-nowrap px-4 py-3 font-mono text-xs font-bold text-[var(--color-ink)]">
                                    {{ $sale->invoice_no }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-[var(--color-ink)] font-semibold">
                                    {{ $sale->sale_date->format('d M Y') }}
                                    <span class="block text-xs text-[var(--color-muted)] font-medium">{{ $sale->sale_date->format('H:i') }}</span>
                                </td>
                                <td class="px-4 py-3 text-[var(--color-ink)] font-bold">{{ $sale->buyer_name }}</td>
                                <td class="px-4 py-3 text-[var(--color-muted)] font-semibold">{{ $sale->cashier?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-[var(--color-surface-muted)] border border-[var(--color-brutal)] text-xs font-extrabold text-[var(--color-ink)]">
                                        {{ $sale->sale_items_count }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right font-extrabold text-[var(--color-ink)]">
                                    Rp {{ number_format($sale->grand_total, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="badge-brutal px-2.5 py-0.5 text-xs font-bold shadow-[1px_1px_0_var(--color-brutal)] {{ $paymentBadge }}">
                                        {{ strtoupper($sale->payment_method) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-sm font-bold text-[var(--color-muted)]">
                                    Tidak ada data penjualan yang sesuai filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($sales->hasPages())
                <div class="border-t-2 border-[var(--color-brutal)] px-4 py-3 bg-[var(--color-surface-muted)]">
                    {{ $sales->links() }}
                </div>
            @endif
        </div>
    @elseif ($activeTab === 'stok')
        {{-- ── Export button ── --}}
        <div class="mb-4 flex justify-end">
            <button
                type="button"
                wire:click="exportMutationsCsv"
                wire:loading.attr="disabled"
                wire:target="exportMutationsCsv"
                class="btn-brutal btn-secondary px-4 py-2.5 text-sm font-bold cursor-pointer shadow-[2px_2px_0_var(--color-brutal)]"
            >
                <span wire:loading.remove wire:target="exportMutationsCsv">Export CSV</span>
                <span wire:loading wire:target="exportMutationsCsv">Mengekspor...</span>
            </button>
        </div>

        {{-- ── Filter panel ── --}}
        <div class="mb-6 card-brutal p-4 bg-[var(--color-surface)]">
            <div class="grid gap-4 md:grid-cols-3">
                <div>
                    <label for="stok-mutationType" class="mb-1.5 block text-sm font-bold text-[var(--color-ink)]">Tipe Mutasi</label>
                    <select
                        id="stok-mutationType"
                        wire:model.live="mutationType"
                        class="block w-full input-brutal focus:ring-1 focus:ring-[var(--color-primary)]"
                    >
                        <option value="">Semua tipe</option>
                        <option value="in">Masuk</option>
                        <option value="out">Keluar</option>
                        <option value="adjustment">Penyesuaian</option>
                        <option value="expired_return">Retur Kedaluwarsa</option>
                    </select>
                </div>

                <div>
                    <label for="stok-dateFrom" class="mb-1.5 block text-sm font-bold text-[var(--color-ink)]">Dari Tanggal</label>
                    <input
                        id="stok-dateFrom"
                        type="date"
                        wire:model.live="dateFrom"
                        class="block w-full input-brutal focus:ring-1 focus:ring-[var(--color-primary)]"
                    />
                </div>

                <div>
                    <label for="stok-dateTo" class="mb-1.5 block text-sm font-bold text-[var(--color-ink)]">Sampai Tanggal</label>
                    <input
                        id="stok-dateTo"
                        type="date"
                        wire:model.live="dateTo"
                        class="block w-full input-brutal focus:ring-1 focus:ring-[var(--color-primary)]"
                    />
                </div>
            </div>
        </div>

        {{-- ── Summary cards ── --}}
        <div class="mb-6 card-brutal p-4 bg-[var(--color-surface)]">
            <div class="flex items-center divide-x-2 divide-[var(--color-brutal)]">
                <div class="flex-1 px-4 first:pl-0">
                    <p class="text-sm font-semibold text-[var(--color-muted)]">Total Stok Masuk</p>
                    <p class="mt-1 text-2xl font-extrabold text-[var(--color-ink)]">{{ $mutationsSummary['total_masuk'] }}</p>
                </div>
                <div class="flex-1 px-4">
                    <p class="text-sm font-semibold text-[var(--color-muted)]">Total Stok Keluar</p>
                    <p class="mt-1 text-2xl font-extrabold text-[var(--color-ink)]">{{ $mutationsSummary['total_keluar'] }}</p>
                </div>
            </div>
        </div>

        {{-- ── Table ── --}}
        <div class="overflow-hidden card-brutal bg-[var(--color-surface)]">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y-2 divide-[var(--color-brutal)] text-sm">
                    <thead class="bg-[var(--color-surface-muted)] text-[var(--color-ink)]">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left font-bold">Tanggal</th>
                            <th scope="col" class="px-4 py-3 text-left font-bold">Nama Obat</th>
                            <th scope="col" class="px-4 py-3 text-left font-bold">Tipe</th>
                            <th scope="col" class="px-4 py-3 text-left font-bold">Jumlah</th>
                            <th scope="col" class="px-4 py-3 text-left font-bold">Keterangan</th>
                            <th scope="col" class="px-4 py-3 text-left font-bold">Dicatat Oleh</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-[var(--color-brutal)]">
                        @forelse ($mutations as $mutation)
                            @php
                                $mutationBadge = match ($mutation->type) {
                                    'in' => 'bg-[var(--color-success-soft)] text-[var(--color-success)]',
                                    'out' => 'bg-[var(--color-danger-soft)] text-[var(--color-danger)]',
                                    'adjustment' => 'bg-[var(--color-warning-soft)] text-[var(--color-warning)]',
                                    'expired_return' => 'bg-[var(--color-surface-muted)] text-[var(--color-muted)]',
                                    default => 'bg-[var(--color-surface-muted)] text-[var(--color-muted)]',
                                };
                            @endphp
                            <tr wire:key="mutation-{{ $mutation->id }}" class="transition-colors hover:bg-[var(--color-primary-soft)]">
                                <td class="whitespace-nowrap px-4 py-3 text-[var(--color-ink)] font-semibold">
                                    {{ $mutation->created_at?->format('d M Y') }}
                                    <span class="block text-xs text-[var(--color-muted)] font-medium">{{ $mutation->created_at?->format('H:i') }}</span>
                                </td>
                                <td class="px-4 py-3 font-bold text-[var(--color-ink)]">
                                    {{ $mutation->medicine?->name ?? '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="badge-brutal px-2.5 py-0.5 text-xs font-bold shadow-[1px_1px_0_var(--color-brutal)] {{ $mutationBadge }}">
                                        {{ $this->typeLabel($mutation->type) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-[var(--color-ink)] font-bold">
                                    @if ($mutation->quantity > 0)
                                        +{{ $mutation->quantity }}
                                    @else
                                        {{ $mutation->quantity }}
                                    @endif
                                </td>
                                <td class="max-w-xs truncate px-4 py-3 text-[var(--color-muted)] font-medium" title="{{ $mutation->notes }}">
                                    {{ $mutation->notes ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-[var(--color-ink)] font-semibold">
                                    {{ $mutation->createdBy?->name ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center text-sm font-bold text-[var(--color-muted)]">
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
    @elseif ($activeTab === 'pendapatan')
        {{-- ── Filter panel ── --}}
        <div class="mb-6 card-brutal p-4 bg-[var(--color-surface)]">
            <div class="flex items-end gap-4 flex-wrap sm:flex-nowrap">
                <div class="flex-1 min-w-[200px]">
                    <label for="pend-dateFrom" class="mb-1.5 block text-sm font-bold text-[var(--color-ink)]">Dari Tanggal</label>
                    <input
                        id="pend-dateFrom"
                        type="date"
                        wire:model.live="dateFrom"
                        class="block w-full input-brutal focus:ring-1 focus:ring-[var(--color-primary)]"
                    />
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label for="pend-dateTo" class="mb-1.5 block text-sm font-bold text-[var(--color-ink)]">Sampai Tanggal</label>
                    <input
                        id="pend-dateTo"
                        type="date"
                        wire:model.live="dateTo"
                        class="block w-full input-brutal focus:ring-1 focus:ring-[var(--color-primary)]"
                    />
                </div>
                <button
                    type="button"
                    wire:click="exportPaymentCsv"
                    wire:loading.attr="disabled"
                    wire:target="exportPaymentCsv"
                    class="btn-brutal btn-secondary px-4 py-2 text-sm font-bold cursor-pointer shadow-[2px_2px_0_var(--color-brutal)]"
                >
                    <span wire:loading.remove wire:target="exportPaymentCsv">Export CSV</span>
                    <span wire:loading wire:target="exportPaymentCsv">Mengekspor...</span>
                </button>
            </div>
        </div>

        {{-- ── Payment method summary cards ── --}}
        <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
            @php
                $methods = [
                    ['key' => 'cash',      'label' => 'Cash',     'border' => 'border-[var(--color-brutal)]', 'bg' => 'bg-[var(--color-surface)]',         'text' => 'text-[var(--color-ink)]'],
                    ['key' => 'transfer',  'label' => 'Transfer', 'border' => 'border-[var(--color-brutal)]', 'bg' => 'bg-[var(--color-info-soft)]',       'text' => 'text-[var(--color-ink)]'],
                    ['key' => 'bpjs',      'label' => 'BPJS',     'border' => 'border-[var(--color-brutal)]', 'bg' => 'bg-[var(--color-success-soft)]',    'text' => 'text-[var(--color-success)]'],
                    ['key' => 'insurance', 'label' => 'Asuransi', 'border' => 'border-[var(--color-brutal)]', 'bg' => 'bg-[var(--color-primary-soft)]',    'text' => 'text-[var(--color-primary-contrast)]'],
                ];
            @endphp
            @foreach ($methods as $method)
                @php $data = $paymentSummary->get($method['key']); @endphp
                <div class="card-brutal p-4 {{ $method['bg'] }} shadow-[3px_3px_0_var(--color-brutal)]">
                    <p class="text-xs font-extrabold {{ $method['text'] }} uppercase tracking-wide">{{ $method['label'] }}</p>
                    <p class="mt-2 text-2xl font-extrabold text-[var(--color-ink)]">
                        Rp {{ number_format($data?->total_nominal ?? 0, 0, ',', '.') }}
                    </p>
                    <p class="mt-1 text-sm font-semibold text-[var(--color-muted)]">{{ $data?->jumlah_transaksi ?? 0 }} transaksi</p>
                </div>
            @endforeach
        </div>

        {{-- ── Daily breakdown table ── --}}
        <div class="overflow-hidden card-brutal bg-[var(--color-surface)]">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y-2 divide-[var(--color-brutal)] text-sm">
                    <thead class="bg-[var(--color-surface-muted)] text-[var(--color-ink)]">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left font-bold">Tanggal</th>
                            <th scope="col" class="px-4 py-3 text-right font-bold">Cash</th>
                            <th scope="col" class="px-4 py-3 text-right font-bold">Transfer</th>
                            <th scope="col" class="px-4 py-3 text-right font-bold">BPJS</th>
                            <th scope="col" class="px-4 py-3 text-right font-bold">Asuransi</th>
                            <th scope="col" class="px-4 py-3 text-right font-bold">Total Hari</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-[var(--color-brutal)]">
                        @forelse ($dailyBreakdown as $tanggal => $rows)
                            @php
                                $byMethod = $rows->keyBy('payment_method');
                                $cash     = $byMethod->get('cash')?->total ?? 0;
                                $transfer = $byMethod->get('transfer')?->total ?? 0;
                                $bpjs     = $byMethod->get('bpjs')?->total ?? 0;
                                $insurance = $byMethod->get('insurance')?->total ?? 0;
                                $dayTotal = $cash + $transfer + $bpjs + $insurance;
                            @endphp
                            <tr wire:key="pend-{{ $tanggal }}" class="transition-colors hover:bg-[var(--color-primary-soft)]">
                                <td class="whitespace-nowrap px-4 py-3 font-bold text-[var(--color-ink)]">{{ $tanggal }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-[var(--color-ink)] font-semibold">
                                    {{ $cash > 0 ? 'Rp '.number_format($cash, 0, ',', '.') : '—' }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-[var(--color-ink)] font-semibold">
                                    {{ $transfer > 0 ? 'Rp '.number_format($transfer, 0, ',', '.') : '—' }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-[var(--color-ink)] font-semibold">
                                    {{ $bpjs > 0 ? 'Rp '.number_format($bpjs, 0, ',', '.') : '—' }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-[var(--color-ink)] font-semibold">
                                    {{ $insurance > 0 ? 'Rp '.number_format($insurance, 0, ',', '.') : '—' }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right font-extrabold text-[var(--color-ink)]">
                                    Rp {{ number_format($dayTotal, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center text-sm font-bold text-[var(--color-muted)]">
                                    Tidak ada transaksi dalam periode yang dipilih.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @elseif ($activeTab === 'kedaluwarsa')
        {{-- ── Section A: Obat Hampir Kedaluwarsa ── --}}
        <div class="mb-4 flex items-center justify-between flex-wrap gap-4">
            <h3 class="text-base font-bold text-[var(--color-ink)]">Obat Hampir Kedaluwarsa</h3>
            <button type="button" wire:click="exportExpiringCsv" wire:loading.attr="disabled" wire:target="exportExpiringCsv"
                class="btn-brutal btn-secondary px-4 py-2 text-sm font-bold cursor-pointer shadow-[2px_2px_0_var(--color-brutal)]">
                <span wire:loading.remove wire:target="exportExpiringCsv">Export CSV — Kedaluwarsa</span>
                <span wire:loading wire:target="exportExpiringCsv">Mengekspor...</span>
            </button>
        </div>

        <div class="overflow-hidden card-brutal bg-[var(--color-surface)]">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y-2 divide-[var(--color-brutal)] text-sm">
                    <thead class="bg-[var(--color-surface-muted)] text-[var(--color-ink)]">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left font-bold">Nama Obat</th>
                            <th scope="col" class="px-4 py-3 text-left font-bold">Nama Generik</th>
                            <th scope="col" class="px-4 py-3 text-left font-bold">Kategori</th>
                            <th scope="col" class="px-4 py-3 text-center font-bold">Stok</th>
                            <th scope="col" class="px-4 py-3 text-left font-bold">Tanggal Kedaluwarsa</th>
                            <th scope="col" class="px-4 py-3 text-center font-bold">Sisa Hari</th>
                            <th scope="col" class="px-4 py-3 text-left font-bold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-[var(--color-brutal)]">
                        @forelse ($expiringMedicines as $medicine)
                            @php
                                $daysLeft = (int) now()->diffInDays($medicine->expiry_date, false);
                                $badgeColorClass = $daysLeft < 30 ? 'bg-[var(--color-danger-soft)] text-[var(--color-danger)]' : 'bg-[var(--color-warning-soft)] text-[var(--color-warning)]';
                                $badgeLabel = $daysLeft < 30 ? 'Kritis' : 'Perhatian';
                            @endphp
                            <tr wire:key="expiring-{{ $medicine->id }}" class="transition-colors hover:bg-[var(--color-primary-soft)]">
                                <td class="px-4 py-3 font-bold text-[var(--color-ink)]">{{ $medicine->name }}</td>
                                <td class="px-4 py-3 text-[var(--color-muted)] font-semibold">{{ $medicine->generic_name ?? '—' }}</td>
                                <td class="px-4 py-3 text-[var(--color-muted)] font-semibold">{{ $medicine->category?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-center text-[var(--color-ink)] font-bold">{{ $medicine->stock }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-[var(--color-ink)] font-semibold">
                                    {{ $medicine->expiry_date?->format('d M Y') }}
                                </td>
                                <td class="px-4 py-3 text-center font-extrabold text-[var(--color-ink)]">{{ $daysLeft }}</td>
                                <td class="px-4 py-3">
                                    <span class="badge-brutal px-2.5 py-0.5 text-xs font-bold shadow-[1px_1px_0_var(--color-brutal)] {{ $badgeColorClass }}">
                                        {{ $badgeLabel }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-sm font-bold text-[var(--color-muted)]">
                                    Tidak ada obat yang hampir kedaluwarsa.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="my-6 border-t-2 border-[var(--color-brutal)]"></div>

        {{-- ── Section B: Obat Stok Menipis ── --}}
        <div class="mb-4 flex items-center justify-between flex-wrap gap-4">
            <h3 class="text-base font-bold text-[var(--color-ink)]">Obat Stok Menipis</h3>
            <button type="button" wire:click="exportLowStockCsv" wire:loading.attr="disabled" wire:target="exportLowStockCsv"
                class="btn-brutal btn-secondary px-4 py-2 text-sm font-bold cursor-pointer shadow-[2px_2px_0_var(--color-brutal)]">
                <span wire:loading.remove wire:target="exportLowStockCsv">Export CSV — Low Stock</span>
                <span wire:loading wire:target="exportLowStockCsv">Mengekspor...</span>
            </button>
        </div>

        <div class="overflow-hidden card-brutal bg-[var(--color-surface)]">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y-2 divide-[var(--color-brutal)] text-sm">
                    <thead class="bg-[var(--color-surface-muted)] text-[var(--color-ink)]">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left font-bold">Nama Obat</th>
                            <th scope="col" class="px-4 py-3 text-left font-bold">Nama Generik</th>
                            <th scope="col" class="px-4 py-3 text-left font-bold">Kategori</th>
                            <th scope="col" class="px-4 py-3 text-center font-bold">Stok Saat Ini</th>
                            <th scope="col" class="px-4 py-3 text-center font-bold">Min. Stok</th>
                            <th scope="col" class="px-4 py-3 text-center font-bold">Selisih</th>
                            <th scope="col" class="px-4 py-3 text-left font-bold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-[var(--color-brutal)]">
                        @forelse ($lowStockMedicines as $medicine)
                            <tr wire:key="lowstock-{{ $medicine->id }}" class="transition-colors hover:bg-[var(--color-primary-soft)]">
                                <td class="px-4 py-3 font-bold text-[var(--color-ink)]">{{ $medicine->name }}</td>
                                <td class="px-4 py-3 text-[var(--color-muted)] font-semibold">{{ $medicine->generic_name ?? '—' }}</td>
                                <td class="px-4 py-3 text-[var(--color-muted)] font-semibold">{{ $medicine->category?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-center text-[var(--color-ink)] font-bold">{{ $medicine->stock }}</td>
                                <td class="px-4 py-3 text-center text-[var(--color-muted)] font-bold">{{ $medicine->min_stock }}</td>
                                <td class="px-4 py-3 text-center font-extrabold text-[var(--color-ink)]">
                                    {{ $medicine->min_stock - $medicine->stock }}
                                </td>
                                <td class="px-4 py-3">
                                    @if ($medicine->stock === 0)
                                        <span class="badge-brutal bg-[var(--color-danger-soft)] text-[var(--color-danger)] px-2.5 py-0.5 text-xs font-bold shadow-[1px_1px_0_var(--color-brutal)]">
                                            Habis
                                        </span>
                                    @else
                                        <span class="badge-brutal bg-[var(--color-warning-soft)] text-[var(--color-warning)] px-2.5 py-0.5 text-xs font-bold shadow-[1px_1px_0_var(--color-brutal)]">
                                            Menipis
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-sm font-bold text-[var(--color-muted)]">
                                    Tidak ada obat dengan stok menipis.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
