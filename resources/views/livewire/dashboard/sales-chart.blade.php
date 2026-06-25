<div>
    {{-- Chart.js via CDN — loaded once per page via @assets --}}
    @assets
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    @endassets

    <div class="grid gap-6 lg:grid-cols-3">

        {{-- ══════════════════════════════════════════════════
             LINE CHART — Penjualan Periodik (2/3 width)
        ═══════════════════════════════════════════════════ --}}
        <div class="lg:col-span-2 card-brutal p-4">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h3 class="text-sm font-bold text-[var(--color-ink)]">Grafik Penjualan</h3>

                {{-- Period toggle --}}
                <div class="flex rounded-lg border-2 border-[var(--color-brutal)] bg-[var(--color-surface-muted)] p-0.5 gap-0.5" role="group" aria-label="Pilih periode">
                    @foreach (['daily' => 'Harian', 'weekly' => 'Mingguan', 'monthly' => 'Bulanan'] as $value => $label)
                        <button
                            type="button"
                            wire:click="setPeriod('{{ $value }}')"
                            wire:loading.attr="disabled"
                            wire:target="setPeriod"
                            aria-pressed="{{ $period === $value ? 'true' : 'false' }}"
                            class="rounded-md px-3 py-1 text-xs font-semibold transition-all border-2 cursor-pointer
                                {{ $period === $value
                                    ? 'bg-[var(--color-primary-soft)] text-[var(--color-primary-contrast)] border-[var(--color-brutal)] shadow-[1px_1px_0_var(--color-brutal)]'
                                    : 'bg-transparent text-[var(--color-muted)] hover:text-[var(--color-ink)] border-transparent' }}"
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
                    <svg class="h-5 w-5 animate-spin text-[var(--color-primary)]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════
             BAR CHART — Top 5 Obat Terlaris (1/3 width)
        ═══════════════════════════════════════════════════ --}}
        <div class="card-brutal p-4">
            <h3 class="mb-4 text-sm font-bold text-[var(--color-ink)]">Top 5 Obat Terlaris</h3>

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
    const gridColor  = '#E2E8F0'; // border-soft
    const tickColor  = '#64748B'; // muted
    const fontFamily = "'Instrument Sans', ui-sans-serif, system-ui, sans-serif";

    Chart.defaults.font.family = fontFamily;
    Chart.defaults.color       = tickColor;

    // ── 1. Line chart — sales over time ───────────────────────
    const lineCanvas = document.getElementById('salesLineChart');

    const initialData = @json($chartData);

    // Factory so the gradient can be recreated after destroy/reinit
    function buildSalesChartOptions(ctx) {
        const gradient = ctx.createLinearGradient(0, 0, 0, 200);
        gradient.addColorStop(0, 'rgba(20, 184, 166, 0.25)'); // primary/teal soft gradient
        gradient.addColorStop(1, 'rgba(20, 184, 166, 0)');

        return {
            type: 'line',
            data: {
                labels:   [],
                datasets: [{
                    label:                'Pendapatan',
                    data:                 [],
                    borderColor:          '#14B8A6', // primary (teal)
                    backgroundColor:      gradient,
                    borderWidth:          3,
                    pointBackgroundColor: '#14B8A6',
                    pointRadius:          4,
                    pointHoverRadius:     6,
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
    const barColors = ['#14B8A6', '#0D9488', '#2DD4BF', '#99F6E4', '#CCFBF1']; // Soft Neubrutal Mint palette

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
                borderColor:     '#0B1220', // brutal border color
                borderWidth:     1.5,
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
