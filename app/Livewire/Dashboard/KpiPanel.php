<?php

namespace App\Livewire\Dashboard;

use App\Services\DashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

/** Widget panel KPI untuk tampilan dashboard */
class KpiPanel extends Component
{
    public float $todayRevenue = 0;

    public int $todayTransactionCount = 0;

    public int $lowStockCount = 0;

    public int $expiringSoonCount = 0;

    public bool $alertPanelOpen = false;

    public string $activeTab = 'low_stock';

    /** Daftar obat stok rendah */
    public ?Collection $lowStockMedicines = null;

    /** Daftar obat yang akan segera kedaluwarsa */
    public ?Collection $expiringSoonMedicines = null;

    /** Memuat data KPI saat komponen dimuat */
    public function mount(DashboardService $dashboard): void
    {
        $this->loadKpis($dashboard);
    }

    /** Menyegarkan semua hitungan KPI melalui polling */
    public function refreshData(DashboardService $dashboard): void
    {
        $this->loadKpis($dashboard);

        if ($this->alertPanelOpen) {
            $this->loadAlertData($dashboard);
        }
    }

    /** Membuka/menutup panel notifikasi dan memuat data secara lazy */
    public function toggleAlertPanel(DashboardService $dashboard): void
    {
        $this->alertPanelOpen = ! $this->alertPanelOpen;

        if ($this->alertPanelOpen) {
            $this->loadAlertData($dashboard);
        }
    }

    /** Mengganti tab notifikasi aktif */
    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    /** Menampilkan tampilan panel KPI */
    public function render(): View
    {
        return view('livewire.dashboard.kpi-panel');
    }

    /** Mengisi nilai KPI dari layanan dashboard */
    private function loadKpis(DashboardService $dashboard): void
    {
        $this->todayRevenue = $dashboard->getTodayRevenue();
        $this->todayTransactionCount = $dashboard->getTodayTransactionCount();
        $this->lowStockCount = $dashboard->getLowStockMedicines()->count();
        $this->expiringSoonCount = $dashboard->getExpiringSoonMedicines(3)->count();
    }

    /** Memuat data notifikasi detail untuk panel notifikasi */
    private function loadAlertData(DashboardService $dashboard): void
    {
        $this->lowStockMedicines = $dashboard->getLowStockMedicines();
        $this->expiringSoonMedicines = $dashboard->getExpiringSoonMedicines(3);
    }
}
