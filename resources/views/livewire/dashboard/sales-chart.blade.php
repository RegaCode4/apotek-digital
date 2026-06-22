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

            <div class="relative h-56" wire:ignore>
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
    const lineCanvas = document.getElementById('salesLineChart');

    const initialData = @json($chartData);

    // Factory so the gradient can be recreated after destroy/reinit
    function buildSalesChartOptions(ctx) {
        const gradient = ctx.createLinearGradient(0, 0, 0, 200);
        gradient.addColorStop(0, 'rgba(24, 24, 27, 0.15)'); // zinc-900
        gradient.addColorStop(1, 'rgba(24, 24, 27, 0)');

        return {
            type: 'line',
            data: {
                labels:   [],
                datasets: [{
                    label:                'Pendapatan',
                    data:                 [],
                    borderColor:          '#18181b',
                    backgroundColor:      gradient,
                    borderWidth:          2,
                    pointBackgroundColor: '#18181b',
                    pointRadius:          3,
                    pointHoverRadius:     5,
                    tension:              0.35,
                    fill:                 true,
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
                        grid:  { color: gridColor },
                        ticks: { maxRotation: 0, maxTicksLimit: 8 },
                    },
                    y: {
                        grid:         { color: gridColor },
                        beginAtZero:  true,
                        suggestedMax: 10000,
                        ticks: {
                            precision: 0,
                            callback: (v) => {
                                if (v % 1 !== 0)    return null;
                                if (v >= 1_000_000) return 'Rp ' + (v / 1_000_000).toFixed(1) + 'jt';
                                if (v >= 1_000)     return 'Rp ' + (v / 1_000).toFixed(0) + 'rb';
                                return 'Rp ' + v.toLocaleString('id-ID');
                            },
                        },
                    },
                },
            },
        };
    }

    // Destroy any pre-existing Chart instance on this canvas, then create fresh
    function initSalesChart(labels, data) {
        const existing = Chart.getChart(lineCanvas);
        if (existing) {
            existing.destroy();
        }

        const ctx     = lineCanvas.getContext('2d');
        const config  = buildSalesChartOptions(ctx);
        config.data.labels              = labels;
        config.data.datasets[0].data    = data;

        return new Chart(ctx, config);
    }

    let salesChart = initSalesChart(initialData.labels, initialData.data);

    // Listen for Livewire period-change event — destroy and recreate so gradient
    // and axes are always consistent with the new dataset
    $wire.on('update-sales-chart', ({ chartData }) => {
        salesChart = initSalesChart(chartData.labels, chartData.data);
    });

    // ── 2. Bar chart — top medicines ──────────────────────────
    const barCanvas = document.getElementById('topMedicinesBarChart');
    const topData   = @json($topMedicines);
    const barColors = ['#18181b', '#3f3f46', '#71717a', '#a1a1aa', '#d4d4d8'];

    const existingBar = Chart.getChart(barCanvas);
    if (existingBar) {
        existingBar.destroy();
    }

    new Chart(barCanvas.getContext('2d'), {
        type: 'bar',
        data: {
            labels:   topData.map(m => m.name.length > 16 ? m.name.slice(0, 15) + '…' : m.name),
            datasets: [{
                label:           'Terjual (pcs)',
                // cast to Number — DB raw SUM returns a string
                data:            topData.map(m => Number(m.total_qty)),
                backgroundColor: barColors,
                borderRadius:    4,
                borderSkipped:   false,
            }],
        },
        options: {
            responsive:          true,
            maintainAspectRatio: false,
            indexAxis: 'y',
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
