<div>
    {{-- ── Header ── --}}
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-zinc-900">Riwayat Transaksi</h2>
        <p class="mt-1 text-sm text-zinc-500">
            @if (auth()->user()->role === 'cashier')
                Transaksi yang Anda proses
            @else
                Seluruh transaksi penjualan
            @endif
        </p>
    </div>

    {{-- ── Filter panel ── --}}
    <div class="mb-6 rounded-xl border border-zinc-200 bg-white p-4 shadow-sm">
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div>
                <label for="search" class="mb-1.5 block text-sm font-medium text-zinc-700">Cari</label>
                <input
                    id="search"
                    type="search"
                    wire:model.live="search"
                    placeholder="No. invoice atau nama pembeli..."
                    class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-500"
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
                        <th scope="col" class="px-4 py-3 text-left font-medium text-zinc-600">Nama Pembeli</th>
                        <th scope="col" class="px-4 py-3 text-center font-medium text-zinc-600">Item</th>
                        <th scope="col" class="px-4 py-3 text-right font-medium text-zinc-600">Grand Total</th>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-zinc-600">Pembayaran</th>
                        <th scope="col" class="px-4 py-3 text-left font-medium text-zinc-600">Kasir</th>
                        <th scope="col" class="px-4 py-3 text-right font-medium text-zinc-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse ($sales as $sale)
                        @php
                            $isExpanded = $expandedSaleId === $sale->id;
                            $paymentBadge = match ($sale->payment_method) {
                                'cash'      => 'bg-zinc-100 text-zinc-700',
                                'transfer'  => 'bg-sky-100 text-sky-700',
                                'bpjs'      => 'bg-emerald-100 text-emerald-700',
                                'insurance' => 'bg-violet-100 text-violet-700',
                                default     => 'bg-zinc-100 text-zinc-600',
                            };
                        @endphp

                        {{-- Main row ── clickable to expand ── --}}
                        <tr
                            wire:key="sale-{{ $sale->id }}"
                            wire:click="toggleDetail({{ $sale->id }})"
                            class="cursor-pointer transition-colors hover:bg-zinc-50/80 {{ $isExpanded ? 'bg-zinc-50' : '' }}"
                        >
                            <td class="whitespace-nowrap px-4 py-3 font-mono text-xs font-medium text-zinc-900">
                                {{ $sale->invoice_no }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-zinc-600">
                                {{ $sale->sale_date->format('d M Y') }}
                                <span class="block text-xs text-zinc-400">{{ $sale->sale_date->format('H:i') }}</span>
                            </td>
                            <td class="px-4 py-3 text-zinc-900">{{ $sale->buyer_name }}</td>
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
                            <td class="px-4 py-3 text-zinc-600">{{ $sale->cashier?->name ?? '—' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                {{-- Stop click propagation so row expand doesn't fire when clicking the link --}}
                                <a
                                    href="{{ route('pos.struk', $sale->id) }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    wire:click.stop
                                    class="inline-flex items-center gap-1 rounded-md border border-zinc-300 bg-white px-2.5 py-1 text-xs font-medium text-zinc-700 hover:bg-zinc-50"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a1 1 0 001 1h8a1 1 0 001-1v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a1 1 0 00-1-1H6a1 1 0 00-1 1zm2 0h6v3H7V4zm-1 9h8v3H6v-3zm8-4a1 1 0 110 2 1 1 0 010-2z" clip-rule="evenodd"/>
                                    </svg>
                                    Struk
                                </a>

                                {{-- Expand indicator --}}
                                <span class="ml-1 inline-flex items-center text-zinc-400">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-4 w-4 transition-transform duration-200 {{ $isExpanded ? 'rotate-180' : '' }}"
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                        aria-hidden="true"
                                    >
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                </span>
                            </td>
                        </tr>

                        {{-- Expanded detail row ── sale items accordion ── --}}
                        @if ($isExpanded)
                            <tr wire:key="detail-{{ $sale->id }}">
                                <td colspan="8" class="bg-zinc-50 px-6 py-4">
                                    <div class="rounded-lg border border-zinc-200 bg-white overflow-hidden">
                                        <table class="min-w-full divide-y divide-zinc-100 text-xs">
                                            <thead class="bg-zinc-100">
                                                <tr>
                                                    <th scope="col" class="px-3 py-2 text-left font-medium text-zinc-600">Nama Obat</th>
                                                    <th scope="col" class="px-3 py-2 text-left font-medium text-zinc-600">No. Resep</th>
                                                    <th scope="col" class="px-3 py-2 text-center font-medium text-zinc-600">Qty</th>
                                                    <th scope="col" class="px-3 py-2 text-right font-medium text-zinc-600">Harga Satuan</th>
                                                    <th scope="col" class="px-3 py-2 text-right font-medium text-zinc-600">Diskon</th>
                                                    <th scope="col" class="px-3 py-2 text-right font-medium text-zinc-600">Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-zinc-50">
                                                @forelse ($sale->saleItems->load('medicine') as $item)
                                                    <tr wire:key="item-{{ $item->id }}">
                                                        <td class="px-3 py-2 font-medium text-zinc-900">
                                                            {{ $item->medicine?->name ?? '—' }}
                                                        </td>
                                                        <td class="px-3 py-2 text-zinc-500">
                                                            {{ $item->prescription_no ?? '—' }}
                                                        </td>
                                                        <td class="px-3 py-2 text-center text-zinc-700">{{ $item->quantity }}</td>
                                                        <td class="px-3 py-2 text-right text-zinc-700">
                                                            Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                                                        </td>
                                                        <td class="px-3 py-2 text-right text-zinc-500">
                                                            @if ($item->discount > 0)
                                                                - Rp {{ number_format($item->discount, 0, ',', '.') }}
                                                            @else
                                                                —
                                                            @endif
                                                        </td>
                                                        <td class="px-3 py-2 text-right font-medium text-zinc-900">
                                                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="px-3 py-4 text-center text-zinc-400">Tidak ada item.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>

                                        {{-- Summary footer ── --}}
                                        <div class="border-t border-zinc-200 bg-zinc-50 px-4 py-3">
                                            <div class="flex flex-wrap items-center justify-end gap-x-6 gap-y-1 text-xs">
                                                <span class="text-zinc-500">
                                                    Subtotal: <strong class="text-zinc-900">Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</strong>
                                                </span>
                                                @if ($sale->discount_amount > 0)
                                                    <span class="text-zinc-500">
                                                        Diskon: <strong class="text-zinc-900">- Rp {{ number_format($sale->discount_amount, 0, ',', '.') }}</strong>
                                                    </span>
                                                @endif
                                                @if ($sale->tax_amount > 0)
                                                    <span class="text-zinc-500">
                                                        PPN 11%: <strong class="text-zinc-900">Rp {{ number_format($sale->tax_amount, 0, ',', '.') }}</strong>
                                                    </span>
                                                @endif
                                                <span class="text-sm font-semibold text-zinc-900">
                                                    Grand Total: Rp {{ number_format($sale->grand_total, 0, ',', '.') }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endif

                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-sm text-zinc-500">
                                Tidak ada transaksi yang sesuai filter.
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
</div>
