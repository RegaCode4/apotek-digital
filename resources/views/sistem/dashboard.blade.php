@extends('sistem.layout')

@section('title', 'Dashboard')

@section('content')

    {{-- ── Header ── --}}
    <div class="mb-6 flex items-start justify-between">
        <div>
            <h2 class="text-xl font-bold text-[var(--color-ink)]">
                Selamat datang, {{ auth()->user()->name }}
            </h2>
            <p class="mt-1 flex items-center gap-2 text-sm text-[var(--color-muted)]">
                <span class="badge-brutal bg-[var(--color-primary-soft)] text-[var(--color-primary-contrast)] font-bold">
                    {{ auth()->user()->role }}
                </span>
                <span>{{ now()->translatedFormat('l, d F Y') }}</span>
            </p>
        </div>
        <a href="{{ route('pos.kasir') }}"
           class="btn-brutal btn-primary px-4 py-2 text-sm"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
                <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/>
            </svg>
            Buka Kasir
        </a>
    </div>

    {{-- ── KPI Panel ── --}}
    <livewire:dashboard.kpi-panel />

    {{-- ── Sales Chart ── --}}
    <div class="mt-6">
        <livewire:dashboard.sales-chart />
    </div>

    {{-- ── Bottom Panel: Transaksi Terbaru + Ringkasan Pembayaran ── --}}
    <div class="mt-6 grid gap-6 lg:grid-cols-2">

        {{-- Transaksi Terbaru --}}
        <div class="card-brutal">
            <div class="flex items-center justify-between border-b-2 border-[var(--color-brutal)] bg-[var(--color-surface-muted)] rounded-t-[var(--radius-brutal)] px-4 py-3">
                <h3 class="text-sm font-bold text-[var(--color-ink)]">Transaksi Terbaru</h3>
                <a href="{{ route('pos.riwayat') }}"
                   class="text-xs font-semibold text-[var(--color-muted)] hover:text-[var(--color-primary-hover)] hover:underline"
                >
                    Lihat semua →
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-[var(--color-border-soft)] text-sm">
                    <thead class="bg-[var(--color-surface-muted)] border-b-2 border-[var(--color-brutal)]">
                        <tr>
                            <th scope="col" class="px-4 py-2.5 text-left text-xs font-bold text-[var(--color-ink)]">Invoice</th>
                            <th scope="col" class="px-4 py-2.5 text-left text-xs font-bold text-[var(--color-ink)]">Pembeli</th>
                            <th scope="col" class="px-4 py-2.5 text-center text-xs font-bold text-[var(--color-ink)]">Total</th>
                            <th scope="col" class="px-4 py-2.5 text-left text-xs font-bold text-[var(--color-ink)]">Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--color-border-soft)]">
                        @forelse ($recentSales as $sale)
                            <tr class="hover:bg-[var(--color-primary-soft)]/50 transition-colors duration-150">
                                <td class="px-4 py-2.5">
                                    <span class="font-mono text-xs font-semibold text-[var(--color-ink)]">{{ $sale->invoice_no }}</span>
                                </td>
                                <td class="px-4 py-2.5 text-[var(--color-ink)]">{{ $sale->buyer_name }}</td>
                                <td class="px-4 py-2.5 text-center text-xs font-bold text-[var(--color-ink)] whitespace-nowrap">
                                    Rp {{ number_format($sale->grand_total, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-2.5 text-xs text-[var(--color-muted)]">
                                    {{ $sale->sale_date->diffForHumans() }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-xs text-[var(--color-muted)]">
                                    Belum ada transaksi hari ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Ringkasan Pendapatan per Payment Method (hari ini) --}}
        <div class="card-brutal">
            <div class="border-b-2 border-[var(--color-brutal)] bg-[var(--color-surface-muted)] rounded-t-[var(--radius-brutal)] px-4 py-3">
                <h3 class="text-sm font-bold text-[var(--color-ink)]">Pendapatan per Metode Bayar</h3>
                <p class="mt-0.5 text-xs font-medium text-[var(--color-muted)]">Hari ini</p>
            </div>
            <div class="divide-y divide-[var(--color-border-soft)]">
                @php
                    $methodLabels = [
                        'cash'      => ['label' => 'Cash',      'color' => 'bg-[var(--color-surface-muted)] text-[var(--color-ink)] border-2 border-[var(--color-brutal)] shadow-[1px_1px_0_var(--color-brutal)] rounded-full px-2.5 py-0.5 text-xs font-semibold'],
                        'transfer'  => ['label' => 'Transfer',  'color' => 'bg-[var(--color-info-soft)] text-[var(--color-ink)] border-2 border-[var(--color-brutal)] shadow-[1px_1px_0_var(--color-brutal)] rounded-full px-2.5 py-0.5 text-xs font-semibold'],
                        'bpjs'      => ['label' => 'BPJS',      'color' => 'bg-[var(--color-success-soft)] text-[var(--color-ink)] border-2 border-[var(--color-brutal)] shadow-[1px_1px_0_var(--color-brutal)] rounded-full px-2.5 py-0.5 text-xs font-semibold'],
                        'insurance' => ['label' => 'Asuransi',  'color' => 'bg-[var(--color-warning-soft)] text-[var(--color-ink)] border-2 border-[var(--color-brutal)] shadow-[1px_1px_0_var(--color-brutal)] rounded-full px-2.5 py-0.5 text-xs font-semibold'],
                    ];
                    $totalToday = $paymentSummary->sum('total');
                @endphp

                @forelse ($paymentSummary as $row)
                    @php
                        $meta    = $methodLabels[$row->payment_method] ?? ['label' => $row->payment_method, 'color' => 'bg-zinc-100 text-zinc-600'];
                        $pct     = $totalToday > 0 ? round(($row->total / $totalToday) * 100) : 0;
                    @endphp
                    <div class="px-4 py-3">
                        <div class="flex items-center justify-between">
                            <span class="inline-flex items-center {{ $meta['color'] }}">
                                {{ $meta['label'] }}
                            </span>
                            <div class="text-right">
                                <p class="text-sm font-bold text-[var(--color-ink)]">
                                    Rp {{ number_format($row->total, 0, ',', '.') }}
                                </p>
                                <p class="text-xs text-[var(--color-muted)]">{{ $row->count }} transaksi</p>
                            </div>
                        </div>
                        {{-- Progress bar --}}
                        <div class="mt-2 h-2.5 w-full overflow-hidden rounded-full border-2 border-[var(--color-brutal)] bg-[var(--color-surface-muted)]">
                            <div class="h-full bg-[var(--color-primary)] transition-all" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-8 text-center text-xs text-[var(--color-muted)]">
                        Belum ada transaksi hari ini.
                    </div>
                @endforelse

                @if ($paymentSummary->isNotEmpty())
                    <div class="flex justify-between px-4 py-3 bg-[var(--color-surface-muted)] rounded-b-[var(--radius-brutal)]">
                        <span class="text-xs font-bold text-[var(--color-muted)]">Total</span>
                        <span class="text-sm font-bold text-[var(--color-ink)]">
                            Rp {{ number_format($totalToday, 0, ',', '.') }}
                        </span>
                    </div>
                @endif
            </div>
        </div>
    </div>

@endsection
