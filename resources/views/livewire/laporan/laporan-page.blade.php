<div>
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-zinc-900">Laporan</h2>
            <p class="mt-1 text-sm text-zinc-500">Laporan operasional apotek</p>
        </div>
    </div>

    <div class="mb-6 overflow-x-auto">
        <div class="flex border-b border-zinc-200">
            <button
                type="button"
                wire:click="setTab('penjualan')"
                @class([
                    'px-4 py-2.5 text-sm whitespace-nowrap',
                    'border-b-2 border-zinc-900 text-zinc-900 font-medium' => $activeTab === 'penjualan',
                    'text-zinc-500 hover:text-zinc-900 transition-colors' => $activeTab !== 'penjualan',
                ])
            >
                Penjualan
            </button>

            <button
                type="button"
                wire:click="setTab('stok')"
                @class([
                    'px-4 py-2.5 text-sm whitespace-nowrap',
                    'border-b-2 border-zinc-900 text-zinc-900 font-medium' => $activeTab === 'stok',
                    'text-zinc-500 hover:text-zinc-900 transition-colors' => $activeTab !== 'stok',
                ])
            >
                Stok &amp; Mutasi
            </button>

            <button
                type="button"
                wire:click="setTab('pendapatan')"
                @class([
                    'px-4 py-2.5 text-sm whitespace-nowrap',
                    'border-b-2 border-zinc-900 text-zinc-900 font-medium' => $activeTab === 'pendapatan',
                    'text-zinc-500 hover:text-zinc-900 transition-colors' => $activeTab !== 'pendapatan',
                ])
            >
                Pendapatan per Metode
            </button>

            <button
                type="button"
                wire:click="setTab('kedaluwarsa')"
                @class([
                    'px-4 py-2.5 text-sm whitespace-nowrap',
                    'border-b-2 border-zinc-900 text-zinc-900 font-medium' => $activeTab === 'kedaluwarsa',
                    'text-zinc-500 hover:text-zinc-900 transition-colors' => $activeTab !== 'kedaluwarsa',
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
                class="inline-flex items-center justify-center rounded-lg border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 hover:bg-zinc-50"
            >
                <span wire:loading.remove wire:target="exportSalesCsv">Export CSV</span>
                <span wire:loading wire:target="exportSalesCsv">Mengekspor...</span>
            </button>
        </div>

        {{-- ── Filter panel ── --}}
        <div class="mb-6 rounded-xl border border-zinc-200 bg-white p-4 shadow-sm">
            <div class="grid gap-4 md:grid-cols-3">
                <div>
                    <label for="dateFrom" class="mb-1.5 block text-sm font-medium text-zinc-700">Dari Tanggal</label>
                    <input
                        id="dateFrom"
                        type="date"
                        wire:model.live="dateFrom"
                        class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500"
                    />
                </div>

                <div>
                    <label for="dateTo" class="mb-1.5 block text-sm font-medium text-zinc-700">Sampai Tanggal</label>
                    <input
                        id="dateTo"
                        type="date"
                        wire:model.live="dateTo"
                        class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500"
                    />
                </div>

                <div>
                    <label for="paymentMethod" class="mb-1.5 block text-sm font-medium text-zinc-700">Metode Pembayaran</label>
                    <select
                        id="paymentMethod"
                        wire:model.live="paymentMethod"
                        class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500"
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
        <div class="mb-6 rounded-xl border border-zinc-200 bg-white p-4 shadow-sm">
            <div class="flex items-center divide-x divide-zinc-200">
                <div class="flex-1 px-4 first:pl-0">
                    <p class="text-sm text-zinc-500">Total Transaksi</p>
                    <p class="mt-1 text-2xl font-semibold text-zinc-900">{{ $salesSummary['total_transaksi'] }}</p>
                </div>
                <div class="flex-1 px-4">
                    <p class="text-sm text-zinc-500">Total Pendapatan</p>
                    <p class="mt-1 text-2xl font-semibold text-zinc-900">
                        Rp {{ number_format($salesSummary['total_pendapatan'], 0, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>

        {{-- ── Table ── --}}
        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 text-sm">
                    <thead class="bg-zinc-50">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left font-medium text-zinc-600">No. Invoice</th>
                            <th scope="col" class="px-4 py-3 text-left font-medium text-zinc-600">Tanggal</th>
                            <th scope="col" class="px-4 py-3 text-left font-medium text-zinc-600">Pembeli</th>
                            <th scope="col" class="px-4 py-3 text-left font-medium text-zinc-600">Kasir</th>
                            <th scope="col" class="px-4 py-3 text-center font-medium text-zinc-600">Total Item</th>
                            <th scope="col" class="px-4 py-3 text-right font-medium text-zinc-600">Grand Total</th>
                            <th scope="col" class="px-4 py-3 text-left font-medium text-zinc-600">Metode Bayar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @forelse ($sales as $sale)
                            @php
                                $paymentBadge = match ($sale->payment_method) {
                                    'cash'      => 'bg-zinc-100 text-zinc-700',
                                    'transfer'  => 'bg-sky-100 text-sky-700',
                                    'bpjs'      => 'bg-emerald-100 text-emerald-700',
                                    'insurance' => 'bg-violet-100 text-violet-700',
                                    default     => 'bg-zinc-100 text-zinc-600',
                                };
                            @endphp
                            <tr wire:key="sale-{{ $sale->id }}" class="transition-colors hover:bg-zinc-50/80">
                                <td class="whitespace-nowrap px-4 py-3 font-mono text-xs font-medium text-zinc-900">
                                    {{ $sale->invoice_no }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-zinc-600">
                                    {{ $sale->sale_date->format('d M Y') }}
                                    <span class="block text-xs text-zinc-400">{{ $sale->sale_date->format('H:i') }}</span>
                                </td>
                                <td class="px-4 py-3 text-zinc-900">{{ $sale->buyer_name }}</td>
                                <td class="px-4 py-3 text-zinc-600">{{ $sale->cashier?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-center text-zinc-600">
                                    <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-zinc-200 text-xs font-medium">
                                        {{ $sale->sale_items_count }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right font-semibold text-zinc-900">
                                    Rp {{ number_format($sale->grand_total, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $paymentBadge }}">
                                        {{ strtoupper($sale->payment_method) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-sm text-zinc-500">
                                    Tidak ada data penjualan yang sesuai filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($sales->hasPages())
                <div class="border-t border-zinc-200 px-4 py-3">
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
                class="inline-flex items-center justify-center rounded-lg border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 hover:bg-zinc-50"
            >
                <span wire:loading.remove wire:target="exportMutationsCsv">Export CSV</span>
                <span wire:loading wire:target="exportMutationsCsv">Mengekspor...</span>
            </button>
        </div>

        {{-- ── Filter panel ── --}}
        <div class="mb-6 rounded-xl border border-zinc-200 bg-white p-4 shadow-sm">
            <div class="grid gap-4 md:grid-cols-3">
                <div>
                    <label for="stok-mutationType" class="mb-1.5 block text-sm font-medium text-zinc-700">Tipe Mutasi</label>
                    <select
                        id="stok-mutationType"
                        wire:model.live="mutationType"
                        class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500"
                    >
                        <option value="">Semua tipe</option>
                        <option value="in">Masuk</option>
                        <option value="out">Keluar</option>
                        <option value="adjustment">Penyesuaian</option>
                        <option value="expired_return">Retur Kedaluwarsa</option>
                    </select>
                </div>

                <div>
                    <label for="stok-dateFrom" class="mb-1.5 block text-sm font-medium text-zinc-700">Dari Tanggal</label>
                    <input
                        id="stok-dateFrom"
                        type="date"
                        wire:model.live="dateFrom"
                        class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500"
                    />
                </div>

                <div>
                    <label for="stok-dateTo" class="mb-1.5 block text-sm font-medium text-zinc-700">Sampai Tanggal</label>
                    <input
                        id="stok-dateTo"
                        type="date"
                        wire:model.live="dateTo"
                        class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500"
                    />
                </div>
            </div>
        </div>

        {{-- ── Summary cards ── --}}
        <div class="mb-6 rounded-xl border border-zinc-200 bg-white p-4 shadow-sm">
            <div class="flex items-center divide-x divide-zinc-200">
                <div class="flex-1 px-4 first:pl-0">
                    <p class="text-sm text-zinc-500">Total Stok Masuk</p>
                    <p class="mt-1 text-2xl font-semibold text-zinc-900">{{ $mutationsSummary['total_masuk'] }}</p>
                </div>
                <div class="flex-1 px-4">
                    <p class="text-sm text-zinc-500">Total Stok Keluar</p>
                    <p class="mt-1 text-2xl font-semibold text-zinc-900">{{ $mutationsSummary['total_keluar'] }}</p>
                </div>
            </div>
        </div>

        {{-- ── Table ── --}}
        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 text-sm">
                    <thead class="bg-zinc-50">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left font-medium text-zinc-600">Tanggal</th>
                            <th scope="col" class="px-4 py-3 text-left font-medium text-zinc-600">Nama Obat</th>
                            <th scope="col" class="px-4 py-3 text-left font-medium text-zinc-600">Tipe</th>
                            <th scope="col" class="px-4 py-3 text-left font-medium text-zinc-600">Jumlah</th>
                            <th scope="col" class="px-4 py-3 text-left font-medium text-zinc-600">Keterangan</th>
                            <th scope="col" class="px-4 py-3 text-left font-medium text-zinc-600">Dicatat Oleh</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @forelse ($mutations as $mutation)
                            @php
                                $mutationBadge = match ($mutation->type) {
                                    'in' => 'bg-emerald-100 text-emerald-800',
                                    'out' => 'bg-red-100 text-red-700',
                                    'adjustment' => 'bg-amber-100 text-amber-800',
                                    'expired_return' => 'bg-zinc-200 text-zinc-700',
                                    default => 'bg-zinc-100 text-zinc-600',
                                };
                            @endphp
                            <tr wire:key="mutation-{{ $mutation->id }}" class="transition-colors hover:bg-zinc-50/80">
                                <td class="whitespace-nowrap px-4 py-3 text-zinc-600">
                                    {{ $mutation->created_at?->format('d M Y') }}
                                    <span class="block text-xs text-zinc-400">{{ $mutation->created_at?->format('H:i') }}</span>
                                </td>
                                <td class="px-4 py-3 font-medium text-zinc-900">
                                    {{ $mutation->medicine?->name ?? '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $mutationBadge }}">
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
                                <td class="max-w-xs truncate px-4 py-3 text-zinc-600" title="{{ $mutation->notes }}">
                                    {{ $mutation->notes ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-zinc-700">
                                    {{ $mutation->createdBy?->name ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center text-sm text-zinc-500">
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
    @elseif ($activeTab === 'pendapatan')
        {{-- ── Filter panel ── --}}
        <div class="mb-6 rounded-xl border border-zinc-200 bg-white p-4 shadow-sm">
            <div class="flex items-end gap-4">
                <div class="flex-1">
                    <label for="pend-dateFrom" class="mb-1.5 block text-sm font-medium text-zinc-700">Dari Tanggal</label>
                    <input
                        id="pend-dateFrom"
                        type="date"
                        wire:model.live="dateFrom"
                        class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500"
                    />
                </div>
                <div class="flex-1">
                    <label for="pend-dateTo" class="mb-1.5 block text-sm font-medium text-zinc-700">Sampai Tanggal</label>
                    <input
                        id="pend-dateTo"
                        type="date"
                        wire:model.live="dateTo"
                        class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500"
                    />
                </div>
                <button
                    type="button"
                    wire:click="exportPaymentCsv"
                    wire:loading.attr="disabled"
                    wire:target="exportPaymentCsv"
                    class="inline-flex items-center rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50"
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
                    ['key' => 'cash',      'label' => 'Cash',     'border' => 'border-zinc-300',   'bg' => 'bg-zinc-50',    'text' => 'text-zinc-700'],
                    ['key' => 'transfer',  'label' => 'Transfer', 'border' => 'border-sky-300',     'bg' => 'bg-sky-50',     'text' => 'text-sky-700'],
                    ['key' => 'bpjs',      'label' => 'BPJS',     'border' => 'border-emerald-300', 'bg' => 'bg-emerald-50', 'text' => 'text-emerald-700'],
                    ['key' => 'insurance', 'label' => 'Asuransi', 'border' => 'border-violet-300',  'bg' => 'bg-violet-50',  'text' => 'text-violet-700'],
                ];
            @endphp
            @foreach ($methods as $method)
                @php $data = $paymentSummary->get($method['key']); @endphp
                <div class="rounded-xl border {{ $method['border'] }} {{ $method['bg'] }} p-4">
                    <p class="text-xs font-medium {{ $method['text'] }} uppercase tracking-wide">{{ $method['label'] }}</p>
                    <p class="mt-2 text-2xl font-semibold text-zinc-900">
                        Rp {{ number_format($data?->total_nominal ?? 0, 0, ',', '.') }}
                    </p>
                    <p class="mt-1 text-sm text-zinc-500">{{ $data?->jumlah_transaksi ?? 0 }} transaksi</p>
                </div>
            @endforeach
        </div>

        {{-- ── Daily breakdown table ── --}}
        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 text-sm">
                    <thead class="bg-zinc-50">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left font-medium text-zinc-600">Tanggal</th>
                            <th scope="col" class="px-4 py-3 text-right font-medium text-zinc-600">Cash</th>
                            <th scope="col" class="px-4 py-3 text-right font-medium text-zinc-600">Transfer</th>
                            <th scope="col" class="px-4 py-3 text-right font-medium text-zinc-600">BPJS</th>
                            <th scope="col" class="px-4 py-3 text-right font-medium text-zinc-600">Asuransi</th>
                            <th scope="col" class="px-4 py-3 text-right font-medium text-zinc-600">Total Hari</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @forelse ($dailyBreakdown as $tanggal => $rows)
                            @php
                                $byMethod = $rows->keyBy('payment_method');
                                $cash     = $byMethod->get('cash')?->total ?? 0;
                                $transfer = $byMethod->get('transfer')?->total ?? 0;
                                $bpjs     = $byMethod->get('bpjs')?->total ?? 0;
                                $insurance = $byMethod->get('insurance')?->total ?? 0;
                                $dayTotal = $cash + $transfer + $bpjs + $insurance;
                            @endphp
                            <tr wire:key="pend-{{ $tanggal }}" class="transition-colors hover:bg-zinc-50/80">
                                <td class="whitespace-nowrap px-4 py-3 font-medium text-zinc-900">{{ $tanggal }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-zinc-600">
                                    {{ $cash > 0 ? 'Rp '.number_format($cash, 0, ',', '.') : '—' }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-zinc-600">
                                    {{ $transfer > 0 ? 'Rp '.number_format($transfer, 0, ',', '.') : '—' }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-zinc-600">
                                    {{ $bpjs > 0 ? 'Rp '.number_format($bpjs, 0, ',', '.') : '—' }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-zinc-600">
                                    {{ $insurance > 0 ? 'Rp '.number_format($insurance, 0, ',', '.') : '—' }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right font-semibold text-zinc-900">
                                    Rp {{ number_format($dayTotal, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center text-sm text-zinc-500">
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
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-base font-semibold text-zinc-900">Obat Hampir Kedaluwarsa</h3>
            <button type="button" wire:click="exportExpiringCsv" wire:loading.attr="disabled" wire:target="exportExpiringCsv"
                class="inline-flex items-center rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50">
                <span wire:loading.remove wire:target="exportExpiringCsv">Export CSV — Kedaluwarsa</span>
                <span wire:loading wire:target="exportExpiringCsv">Mengekspor...</span>
            </button>
        </div>

        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 text-sm">
                    <thead class="bg-zinc-50">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left font-medium text-zinc-600">Nama Obat</th>
                            <th scope="col" class="px-4 py-3 text-left font-medium text-zinc-600">Nama Generik</th>
                            <th scope="col" class="px-4 py-3 text-left font-medium text-zinc-600">Kategori</th>
                            <th scope="col" class="px-4 py-3 text-center font-medium text-zinc-600">Stok</th>
                            <th scope="col" class="px-4 py-3 text-left font-medium text-zinc-600">Tanggal Kedaluwarsa</th>
                            <th scope="col" class="px-4 py-3 text-center font-medium text-zinc-600">Sisa Hari</th>
                            <th scope="col" class="px-4 py-3 text-left font-medium text-zinc-600">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @forelse ($expiringMedicines as $medicine)
                            @php
                                $daysLeft = (int) now()->diffInDays($medicine->expiry_date, false);
                                $badgeClass = $this->expiryBadgeClass($medicine->expiry_date);
                                $badgeLabel = $daysLeft < 30 ? 'Kritis' : 'Perhatian';
                            @endphp
                            <tr wire:key="expiring-{{ $medicine->id }}" class="transition-colors hover:bg-zinc-50/80">
                                <td class="px-4 py-3 font-medium text-zinc-900">{{ $medicine->name }}</td>
                                <td class="px-4 py-3 text-zinc-600">{{ $medicine->generic_name ?? '—' }}</td>
                                <td class="px-4 py-3 text-zinc-600">{{ $medicine->category?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-center text-zinc-700">{{ $medicine->stock }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-zinc-600">
                                    {{ $medicine->expiry_date?->format('d M Y') }}
                                </td>
                                <td class="px-4 py-3 text-center font-semibold text-zinc-900">{{ $daysLeft }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badgeClass }}">
                                        {{ $badgeLabel }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-sm text-zinc-500">
                                    Tidak ada obat yang hampir kedaluwarsa.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="my-6 border-t border-zinc-200"></div>

        {{-- ── Section B: Obat Stok Menipis ── --}}
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-base font-semibold text-zinc-900">Obat Stok Menipis</h3>
            <button type="button" wire:click="exportLowStockCsv" wire:loading.attr="disabled" wire:target="exportLowStockCsv"
                class="inline-flex items-center rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50">
                <span wire:loading.remove wire:target="exportLowStockCsv">Export CSV — Low Stock</span>
                <span wire:loading wire:target="exportLowStockCsv">Mengekspor...</span>
            </button>
        </div>

        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 text-sm">
                    <thead class="bg-zinc-50">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left font-medium text-zinc-600">Nama Obat</th>
                            <th scope="col" class="px-4 py-3 text-left font-medium text-zinc-600">Nama Generik</th>
                            <th scope="col" class="px-4 py-3 text-left font-medium text-zinc-600">Kategori</th>
                            <th scope="col" class="px-4 py-3 text-center font-medium text-zinc-600">Stok Saat Ini</th>
                            <th scope="col" class="px-4 py-3 text-center font-medium text-zinc-600">Min. Stok</th>
                            <th scope="col" class="px-4 py-3 text-center font-medium text-zinc-600">Selisih</th>
                            <th scope="col" class="px-4 py-3 text-left font-medium text-zinc-600">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @forelse ($lowStockMedicines as $medicine)
                            <tr wire:key="lowstock-{{ $medicine->id }}" class="transition-colors hover:bg-zinc-50/80">
                                <td class="px-4 py-3 font-medium text-zinc-900">{{ $medicine->name }}</td>
                                <td class="px-4 py-3 text-zinc-600">{{ $medicine->generic_name ?? '—' }}</td>
                                <td class="px-4 py-3 text-zinc-600">{{ $medicine->category?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-center text-zinc-700">{{ $medicine->stock }}</td>
                                <td class="px-4 py-3 text-center text-zinc-600">{{ $medicine->min_stock }}</td>
                                <td class="px-4 py-3 text-center font-semibold text-zinc-900">
                                    {{ $medicine->min_stock - $medicine->stock }}
                                </td>
                                <td class="px-4 py-3">
                                    @if ($medicine->stock === 0)
                                        <span class="inline-flex rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-700">
                                            Habis
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800">
                                            Menipis
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-sm text-zinc-500">
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
