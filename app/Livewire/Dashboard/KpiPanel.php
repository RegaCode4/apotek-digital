<?php

namespace App\Livewire\Dashboard;

use App\Models\Medicine;
use App\Services\DashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class KpiPanel extends Component
{
    // ── KPI values ────────────────────────────────────────────
    public float $todayRevenue = 0;

    public int $todayTransactionCount = 0;

    public int $lowStockCount = 0;

    public int $expiringSoonCount = 0;

    // ── Alert panel state ─────────────────────────────────────
    public bool $alertPanelOpen = false;

    public string $activeTab = 'low_stock';

    // ── Alert data (loaded lazily when panel opens) ───────────

    /** @var Collection<int, Medicine>|null */
    public ?Collection $lowStockMedicines = null;

    /** @var Collection<int, Medicine>|null */
    public ?Collection $expiringSoonMedicines = null;

    // ── Lifecycle ─────────────────────────────────────────────

    public function mount(DashboardService $dashboard): void
    {
        $this->loadKpis($dashboard);
    }

    /**
     * Called by wire:poll.60000ms — refreshes all KPI counts.
     */
    public function refreshData(DashboardService $dashboard): void
    {
        $this->loadKpis($dashboard);

        // Also refresh alert data if the panel is currently open
        if ($this->alertPanelOpen) {
            $this->loadAlertData($dashboard);
        }
    }

    // ── Actions ───────────────────────────────────────────────

    public function toggleAlertPanel(DashboardService $dashboard): void
    {
        $this->alertPanelOpen = ! $this->alertPanelOpen;

        if ($this->alertPanelOpen) {
            $this->loadAlertData($dashboard);
        }
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    // ── Render ────────────────────────────────────────────────

    public function render(): View
    {
        return view('livewire.dashboard.kpi-panel');
    }

    // ── Private helpers ───────────────────────────────────────

    private function loadKpis(DashboardService $dashboard): void
    {
        $this->todayRevenue = $dashboard->getTodayRevenue();
        $this->todayTransactionCount = $dashboard->getTodayTransactionCount();
        $this->lowStockCount = $dashboard->getLowStockMedicines()->count();
        $this->expiringSoonCount = $dashboard->getExpiringSoonMedicines(3)->count();
    }

    private function loadAlertData(DashboardService $dashboard): void
    {
        $this->lowStockMedicines = $dashboard->getLowStockMedicines();
        $this->expiringSoonMedicines = $dashboard->getExpiringSoonMedicines(3);
    }
}
