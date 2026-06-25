<div>
    {{-- ── Header ── --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-[var(--color-ink)]">Riwayat Transaksi</h2>
        <p class="mt-1 text-sm font-semibold text-[var(--color-muted)]">
            @if (auth()->user()->role === 'cashier')
                Transaksi yang Anda proses
            @else
                Seluruh transaksi penjualan
            @endif
        </p>
    </div>

    {{-- ── Filter panel ── --}}
    <div class="mb-6 card-brutal p-4">
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div>
                <label for="search" class="mb-1.5 block text-sm font-bold text-[var(--color-ink)]">Cari</label>
                <input
                    id="search"
                    type="search"
                    wire:model.live="search"
                    placeholder="No. invoice atau nama pembeli..."
                    class="block w-full input-brutal text-sm text-[var(--color-ink)] placeholder-[var(--color-muted)] focus:outline-none"
                />
            </div>

            <div>
                <label for="paymentMethod" class="mb-1.5 block text-sm font-bold text-[var(--color-ink)]">Metode Pembayaran</label>
                <select
                    id="paymentMethod"
                    wire:model.live="paymentMethod"
                    class="block w-full input-brutal text-sm text-[var(--color-ink)] focus:outline-none"
                >
                    <option value="">Semua metode</option>
                    <option value="cash">Cash</option>
                    <option value="transfer">Transfer</option>
                    <option value="bpjs">BPJS</option>
                    <option value="insurance">Asuransi</option>
                </select>
            </div>

            <div>
                <label for="dateFrom" class="mb-1.5 block text-sm font-bold text-[var(--color-ink)]">Dari Tanggal</label>
                <input
                    id="dateFrom"
                    type="date"
                    wire:model.live="dateFrom"
                    class="block w-full input-brutal text-sm text-[var(--color-ink)] focus:outline-none"
                />
            </div>

            <div>
                <label for="dateTo" class="mb-1.5 block text-sm font-bold text-[var(--color-ink)]">Sampai Tanggal</label>
                <input
                    id="dateTo"
                    type="date"
                    wire:model.live="dateTo"
                    class="block w-full input-brutal text-sm text-[var(--color-ink)] focus:outline-none"
                />
            </div>
        </div>
    </div>

    {{-- ── Table ── --}}
    <div class="card-brutal overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-[var(--color-border-soft)] text-sm">
                <thead class="bg-[var(--color-surface-muted)] border-b-2 border-[var(--color-brutal)]">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-[var(--color-ink)]">No. Invoice</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-[var(--color-ink)]">Tanggal</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-[var(--color-ink)]">Nama Pembeli</th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-bold text-[var(--color-ink)]">Item</th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-bold text-[var(--color-ink)]">Grand Total</th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-bold text-[var(--color-ink)]">Pembayaran</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-[var(--color-ink)]">Kasir</th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-bold text-[var(--color-ink)]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--color-border-soft)]">
                    @forelse ($sales as $sale)
                        @php
                            $isExpanded = $expandedSaleId === $sale->id;
                            $paymentBadge = match ($sale->payment_method) {
                                'cash'      => 'bg-[var(--color-surface-muted)] text-[var(--color-ink)] border border-[var(--color-brutal)] shadow-[1px_1px_0_var(--color-brutal)]',
                                'transfer'  => 'bg-[var(--color-info-soft)] text-[var(--color-ink)] border border-[var(--color-brutal)] shadow-[1px_1px_0_var(--color-brutal)]',
                                'bpjs'      => 'bg-[var(--color-success-soft)] text-[var(--color-ink)] border border-[var(--color-brutal)] shadow-[1px_1px_0_var(--color-brutal)]',
                                'insurance' => 'bg-[var(--color-warning-soft)] text-[var(--color-ink)] border border-[var(--color-brutal)] shadow-[1px_1px_0_var(--color-brutal)]',
                                default     => 'bg-[var(--color-surface-muted)] text-[var(--color-ink)] border border-[var(--color-brutal)] shadow-[1px_1px_0_var(--color-brutal)]',
                            };
                        @endphp

                        {{-- Main row ── clickable to expand ── --}}
                        <tr
                            wire:key="sale-{{ $sale->id }}"
                            wire:click="toggleDetail({{ $sale->id }})"
                            class="cursor-pointer transition-all hover:bg-[var(--color-primary-soft)]/50 {{ $isExpanded ? 'bg-[var(--color-surface-muted)]' : '' }}"
                        >
                            <td class="whitespace-nowrap px-4 py-3 font-mono text-xs font-semibold text-[var(--color-ink)]">
                                {{ $sale->invoice_no }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-[var(--color-ink)] font-medium">
                                {{ $sale->sale_date->format('d M Y') }}
                                <span class="block text-xs font-semibold text-[var(--color-muted)]">{{ $sale->sale_date->format('H:i') }}</span>
                            </td>
                            <td class="px-4 py-3 font-bold text-[var(--color-ink)]">{{ $sale->buyer_name }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="badge-brutal text-xs font-bold bg-[var(--color-primary-soft)] text-[var(--color-primary-contrast)]">
                                    {{ $sale->sale_items_count }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-center font-extrabold text-[var(--color-ink)]">
                                Rp {{ number_format($sale->grand_total, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold {{ $paymentBadge }}">
                                    {{ strtoupper($sale->payment_method) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-[var(--color-ink)] font-medium">{{ $sale->cashier?->name ?? '—' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-center">
                                {{-- Stop click propagation so row expand doesn't fire when clicking the link --}}
                                <a
                                    href="{{ route('pos.struk', $sale->id) }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    wire:click.stop
                                    class="btn-brutal btn-secondary px-2.5 py-1 text-xs font-bold cursor-pointer shadow-[2px_2px_0_var(--color-brutal)]"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a1 1 0 001 1h8a1 1 0 001-1v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a1 1 0 00-1-1H6a1 1 0 00-1 1zm2 0h6v3H7V4zm-1 9h8v3H6v-3zm8-4a1 1 0 110 2 1 1 0 010-2z" clip-rule="evenodd"/>
                                    </svg>
                                    Struk
                                </a>

                                {{-- Expand indicator --}}
                                <span class="ml-1 inline-flex items-center text-[var(--color-muted)]">
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
                                <td colspan="8" class="bg-[var(--color-surface-muted)] px-6 py-4">
                                    <div class="card-brutal overflow-hidden bg-[var(--color-surface)]">
                                        <table class="min-w-full divide-y divide-[var(--color-border-soft)] text-xs">
                                            <thead class="bg-[var(--color-surface-muted)] border-b border-[var(--color-brutal)]">
                                                <tr>
                                                    <th scope="col" class="px-3 py-2 text-left font-bold text-[var(--color-ink)]">Nama Obat</th>
                                                    <th scope="col" class="px-3 py-2 text-left font-bold text-[var(--color-ink)]">No. Resep</th>
                                                    <th scope="col" class="px-3 py-2 text-center font-bold text-[var(--color-ink)]">Qty</th>
                                                    <th scope="col" class="px-3 py-2 text-right font-bold text-[var(--color-ink)]">Harga Satuan</th>
                                                    <th scope="col" class="px-3 py-2 text-right font-bold text-[var(--color-ink)]">Diskon</th>
                                                    <th scope="col" class="px-3 py-2 text-right font-bold text-[var(--color-ink)]">Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-[var(--color-border-soft)]">
                                                @forelse ($sale->saleItems->load('medicine') as $item)
                                                    <tr wire:key="item-{{ $item->id }}" class="hover:bg-[var(--color-primary-soft)]/20 transition-colors">
                                                        <td class="px-3 py-2 font-bold text-[var(--color-ink)]">
                                                            {{ $item->medicine?->name ?? '—' }}
                                                        </td>
                                                        <td class="px-3 py-2 font-semibold text-[var(--color-muted)]">
                                                            {{ $item->prescription_no ?? '—' }}
                                                        </td>
                                                        <td class="px-3 py-2 text-center font-bold text-[var(--color-ink)]">{{ $item->quantity }}</td>
                                                        <td class="px-3 py-2 text-right font-semibold text-[var(--color-ink)]">
                                                            Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                                                        </td>
                                                        <td class="px-3 py-2 text-right font-semibold text-[var(--color-danger)]">
                                                            @if ($item->discount > 0)
                                                                - Rp {{ number_format($item->discount, 0, ',', '.') }}
                                                            @else
                                                                —
                                                            @endif
                                                        </td>
                                                        <td class="px-3 py-2 text-right font-bold text-[var(--color-ink)]">
                                                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="px-3 py-4 text-center font-semibold text-[var(--color-muted)]">Tidak ada item.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>

                                        {{-- Summary footer ── --}}
                                        <div class="border-t-2 border-[var(--color-brutal)] bg-[var(--color-surface-muted)] px-4 py-3">
                                            <div class="flex flex-wrap items-center justify-end gap-x-6 gap-y-2 text-xs font-semibold">
                                                <span class="text-[var(--color-muted)]">
                                                    Subtotal: <strong class="text-[var(--color-ink)] font-bold">Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</strong>
                                                </span>
                                                @if ($sale->discount_amount > 0)
                                                    <span class="text-[var(--color-muted)]">
                                                        Diskon: <strong class="text-[var(--color-danger)] font-bold">- Rp {{ number_format($sale->discount_amount, 0, ',', '.') }}</strong>
                                                    </span>
                                                @endif
                                                @if ($sale->tax_amount > 0)
                                                    <span class="text-[var(--color-muted)]">
                                                        PPN 11%: <strong class="text-[var(--color-ink)] font-bold">Rp {{ number_format($sale->tax_amount, 0, ',', '.') }}</strong>
                                                    </span>
                                                @endif
                                                <span class="text-sm font-extrabold text-[var(--color-ink)] bg-[var(--color-primary-soft)] border border-[var(--color-brutal)] rounded-lg px-2.5 py-1 shadow-[2px_2px_0_var(--color-brutal)] ml-2 inline-block">
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
                            <td colspan="8" class="px-4 py-12 text-center text-sm font-bold text-[var(--color-muted)]">
                                Tidak ada transaksi yang sesuai filter.
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
</div>
