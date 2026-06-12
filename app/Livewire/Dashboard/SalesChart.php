<?php

namespace App\Livewire\Dashboard;

use App\Services\DashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class SalesChart extends Component
{
    // ── State ─────────────────────────────────────────────────
    public string $period = 'weekly';

    /** @var array{labels: list<string>, data: list<float>} */
    public array $chartData = ['labels' => [], 'data' => []];

    /** @var Collection<int, object> */
    public Collection $topMedicines;

    // ── Lifecycle ─────────────────────────────────────────────

    public function mount(DashboardService $dashboard): void
    {
        $this->chartData = $dashboard->getSalesChartData($this->period);
        $this->topMedicines = $dashboard->getTopSellingMedicines(5);
    }

    // ── Actions ───────────────────────────────────────────────

    /**
     * Switch chart period and push fresh data to the browser via a JS event.
     */
    public function setPeriod(string $period, DashboardService $dashboard): void
    {
        $this->period = $period;
        $this->chartData = $dashboard->getSalesChartData($period);

        $this->dispatch('update-sales-chart', chartData: $this->chartData);
    }

    // ── Render ────────────────────────────────────────────────

    public function render(): View
    {
        return view('livewire.dashboard.sales-chart');
    }
}
