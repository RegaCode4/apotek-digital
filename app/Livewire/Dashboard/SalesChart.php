<?php

namespace App\Livewire\Dashboard;

use App\Services\DashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

/** Widget grafik penjualan dengan pergantian periode */
class SalesChart extends Component
{
    public string $period = 'weekly';

    /** Label grafik dan nilai data */
    public array $chartData = ['labels' => [], 'data' => []];

    /** Daftar obat terlaris */
    public Collection $topMedicines;

    /** Memuat data grafik awal dan obat terlaris */
    public function mount(DashboardService $dashboard): void
    {
        $this->chartData = $dashboard->getSalesChartData($this->period);
        $this->topMedicines = $dashboard->getTopSellingMedicines(5);
    }

    /** Mengganti periode grafik dan mengirim data yang diperbarui ke browser */
    public function setPeriod(string $period, DashboardService $dashboard): void
    {
        $this->period = $period;
        $this->chartData = $dashboard->getSalesChartData($period);

        $this->dispatch('update-sales-chart', chartData: $this->chartData);
    }

    /** Menampilkan tampilan grafik penjualan */
    public function render(): View
    {
        return view('livewire.dashboard.sales-chart');
    }
}
