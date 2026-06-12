@extends('sistem.layout')

@section('title', 'Dashboard')

@section('content')

    {{-- ── Header ── --}}
    <div class="mb-6 flex items-start justify-between">
        <div>
            <h2 class="text-xl font-semibold text-zinc-900">
                Selamat datang, {{ auth()->user()->name }}
            </h2>
            <p class="mt-1 flex items-center gap-2 text-sm text-zinc-500">
                <span class="inline-flex items-center rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-medium capitalize text-zinc-700">
                    {{ auth()->user()->role }}
                </span>
                <span>{{ now()->translatedFormat('l, d F Y') }}</span>
            </p>
        </div>
        <a href="{{ route('pos.kasir') }}"
           class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-3 py-2 text-sm font-medium text-white hover:bg-zinc-800"
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
    <div class="mt-6 grid gap-4 lg:grid-cols-2">

        {{-- Transaksi Terbaru --}}
        <div class="rounded-xl border border-zinc-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-zinc-100 px-4 py-3">
                <h3 class="text-sm font-semibold text-zinc-900">Transaksi Terbaru</h3>
                <a href="{{ route('pos.riwayat') }}"
                   class="text-xs font-medium text-zinc-500 hover:text-zinc-800 hover:underline"
                >
                    Lihat semua →
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-50 text-sm">
                    <thead class="bg-zinc-50">
                        <tr>
                            <th scope="col" class="px-4 py-2.5 text-left text-xs font-medium text-zinc-500">Invoice</th>
                            <th scope="col" class="px-4 py-2.5 text-left text-xs font-medium text-zinc-500">Pembeli</th>
                            <th scope="col" class="px-4 py-2.5 text-right text-xs font-medium text-zinc-500">Total</th>
                            <th scope="col" class="px-4 py-2.5 text-left text-xs font-medium text-zinc-500">Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-50">
                        @forelse ($recentSales as $sale)
                            <tr class="hover:bg-zinc-50/60">
                                <td class="px-4 py-2.5">
                                    <span class="font-mono text-xs text-zinc-700">{{ $sale->invoice_no }}</span>
                                </td>
                                <td class="px-4 py-2.5 text-zinc-800">{{ $sale->buyer_name }}</td>
                                <td class="px-4 py-2.5 text-right text-xs font-semibold text-zinc-900">
                                    Rp {{ number_format($sale->grand_total, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-2.5 text-xs text-zinc-400">
                                    {{ $sale->sale_date->diffForHumans() }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-xs text-zinc-400">
                                    Belum ada transaksi hari ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Ringkasan Pendapatan per Payment Method (hari ini) --}}
        <div class="rounded-xl border border-zinc-200 bg-white shadow-sm">
            <div class="border-b border-zinc-100 px-4 py-3">
                <h3 class="text-sm font-semibold text-zinc-900">Pendapatan per Metode Bayar</h3>
                <p class="mt-0.5 text-xs text-zinc-400">Hari ini</p>
            </div>
            <div class="divide-y divide-zinc-50">
                @php
                    $methodLabels = [
                        'cash'      => ['label' => 'Cash',      'color' => 'bg-zinc-200 text-zinc-700'],
                        'transfer'  => ['label' => 'Transfer',  'color' => 'bg-sky-100 text-sky-700'],
                        'bpjs'      => ['label' => 'BPJS',      'color' => 'bg-emerald-100 text-emerald-700'],
                        'insurance' => ['label' => 'Asuransi',  'color' => 'bg-violet-100 text-violet-700'],
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
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $meta['color'] }}">
                                {{ $meta['label'] }}
                            </span>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-zinc-900">
                                    Rp {{ number_format($row->total, 0, ',', '.') }}
                                </p>
                                <p class="text-xs text-zinc-400">{{ $row->count }} transaksi</p>
                            </div>
                        </div>
                        {{-- Progress bar --}}
                        <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-zinc-100">
                            <div class="h-full rounded-full bg-zinc-900 transition-all" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-8 text-center text-xs text-zinc-400">
                        Belum ada transaksi hari ini.
                    </div>
                @endforelse

                @if ($paymentSummary->isNotEmpty())
                    <div class="flex justify-between px-4 py-3">
                        <span class="text-xs font-medium text-zinc-500">Total</span>
                        <span class="text-sm font-bold text-zinc-900">
                            Rp {{ number_format($totalToday, 0, ',', '.') }}
                        </span>
                    </div>
                @endif
            </div>
        </div>
    </div>

@endsection
