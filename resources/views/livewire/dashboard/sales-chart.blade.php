<div>
    {{-- Chart.js via CDN — loaded once per page via @assets --}}
    @assets
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    @endassets

    <div class="grid gap-4 lg:grid-cols-3">

        {{-- ══════════════════════════════════════════════════
             LINE CHART — Penjualan Periodik (2/3 width)
        ═══════════════════════════════════════════════════ --}}
        <div class="lg:col-span-2 rounded-xl border border-zinc-200 bg-white p-4 shadow-sm">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h3 class="text-sm font-semibold text-zinc-900">Grafik Penjualan</h3>

                {{-- Period toggle --}}
                <div class="flex rounded-lg border border-zinc-200 bg-zinc-50 p-0.5 gap-0.5" role="group" aria-label="Pilih periode">
                    @foreach (['daily' => 'Harian', 'weekly' => 'Mingguan', 'monthly' => 'Bulanan'] as $value => $label)
                        <button
                            type="button"
                            wire:click="setPeriod('{{ $value }}')"
                            wire:loading.attr="disabled"
                            wire:target="setPeriod"
                            aria-pressed="{{ $period === $value ? 'true' : 'false' }}"
                            class="rounded-md px-3 py-1 text-xs font-medium transition-colors
                                {{ $period === $value
                                    ? 'bg-white text-zinc-900 shadow-sm'
                                    : 'text-zinc-500 hover:text-zinc-700' }}"
                        >
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="relative h-56">
                <canvas
                    id="salesLineChart"
                    aria-label="Grafik penjualan {{ ['daily'=>'7 hari','weekly'=>'8 minggu','monthly'=>'12 bulan'][$period] }}"
                    role="img"
                ></canvas>
                {{-- Loading overlay --}}
                <div
                    wire:loading
                    wire:target="setPeriod"
                    class="absolute inset-0 flex items-center justify-center rounded-lg bg-white/70"
                >
                    <svg class="h-5 w-5 animate-spin text-zinc-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════
             BAR CHART — Top 5 Obat Terlaris (1/3 width)
        ═══════════════════════════════════════════════════ --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm">
            <h3 class="mb-4 text-sm font-semibold text-zinc-900">Top 5 Obat Terlaris</h3>

            <div class="relative h-56">
                <canvas
                    id="topMedicinesBarChart"
                    aria-label="Grafik 5 obat terlaris"
                    role="img"
                ></canvas>
            </div>
        </div>
    </div>
</div>

@script
<script>
    // ── Shared formatter ──────────────────────────────────────
    const formatRupiah = (value) =>
        'Rp ' + new Intl.NumberFormat('id-ID').format(value);

    // ── Common chart defaults ──────────────────────────────────
    const gridColor  = 'rgba(0,0,0,0.06)';
    const tickColor  = '#71717a'; // zinc-500
    const fontFamily = "'Inter', ui-sans-serif, system-ui, sans-serif";

    Chart.defaults.font.family = fontFamily;
    Chart.defaults.color       = tickColor;

    // ── 1. Line chart — sales over time ───────────────────────
    const lineCtx = document.getElementById('salesLineChart').getContext('2d');

    const initialData = @json($chartData);

    const gradient = lineCtx.createLinearGradient(0, 0, 0, 200);
    gradient.addColorStop(0,   'rgba(24, 24, 27, 0.15)');  // zinc-900
    gradient.addColorStop(1,   'rgba(24, 24, 27, 0)');

    const salesChart = new Chart(lineCtx, {
        type: 'line',
        data: {
            labels:   initialData.labels,
            datasets: [{
                label:           'Pendapatan',
                data:            initialData.data,
                borderColor:     '#18181b',      // zinc-900
                backgroundColor: gradient,
                borderWidth:     2,
                pointBackgroundColor: '#18181b',
                pointRadius:     3,
                pointHoverRadius: 5,
                tension:         0.35,
                fill:            true,
            }],
        },
        options: {
            responsive:          true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx) => ' ' + formatRupiah(ctx.parsed.y),
                    },
                },
            },
            scales: {
                x: {
                    grid: { color: gridColor },
                    ticks: { maxRotation: 0, maxTicksLimit: 8 },
                },
                y: {
                    grid:       { color: gridColor },
                    beginAtZero: true,
                    ticks: {
                        callback: (v) => {
                            if (v >= 1_000_000) return 'Rp ' + (v / 1_000_000).toFixed(1) + 'jt';
                            if (v >= 1_000)     return 'Rp ' + (v / 1_000).toFixed(0) + 'rb';
                            return formatRupiah(v);
                        },
                    },
                },
            },
        },
    });

    // Listen for Livewire period-change event and update the chart in place
    $wire.on('update-sales-chart', ({ chartData }) => {
        salesChart.data.labels         = chartData.labels;
        salesChart.data.datasets[0].data = chartData.data;
        salesChart.update('active');
    });

    // ── 2. Bar chart — top medicines ──────────────────────────
    const barCtx     = document.getElementById('topMedicinesBarChart').getContext('2d');
    const topData    = @json($topMedicines);

    const barColors  = ['#18181b', '#3f3f46', '#71717a', '#a1a1aa', '#d4d4d8'];

    new Chart(barCtx, {
        type: 'bar',
        data: {
            labels:   topData.map(m => m.name.length > 16 ? m.name.slice(0, 15) + '…' : m.name),
            datasets: [{
                label:           'Terjual (pcs)',
                data:            topData.map(m => m.total_qty),
                backgroundColor: barColors,
                borderRadius:    4,
                borderSkipped:   false,
            }],
        },
        options: {
            responsive:          true,
            maintainAspectRatio: false,
            indexAxis: 'y',     // horizontal bar — fits long medicine names
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx) => ` ${ctx.parsed.x} pcs`,
                        title: (items) => topData[items[0].dataIndex]?.name ?? '',
                    },
                },
            },
            scales: {
                x: {
                    grid:        { color: gridColor },
                    beginAtZero: true,
                    ticks: { precision: 0 },
                },
                y: { grid: { display: false } },
            },
        },
    });
</script>
@endscript
