<?php

namespace App\Services;

use App\Models\Medicine;
use App\Models\Sale;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Total revenue (grand_total) from sales today.
     */
    public function getTodayRevenue(): float
    {
        return (float) Sale::query()
            ->whereDate('sale_date', today())
            ->sum('grand_total');
    }

    /**
     * Number of transactions completed today.
     */
    public function getTodayTransactionCount(): int
    {
        return Sale::query()
            ->whereDate('sale_date', today())
            ->count();
    }

    /**
     * Medicines whose current stock is at or below their minimum stock threshold.
     *
     * @return Collection<int, Medicine>
     */
    public function getLowStockMedicines(): Collection
    {
        return Medicine::query()
            ->whereColumn('stock', '<=', 'min_stock')
            ->orderBy('stock')
            ->get();
    }

    /**
     * Medicines expiring within the given number of months.
     *
     * @return Collection<int, Medicine>
     */
    public function getExpiringSoonMedicines(int $months = 3): Collection
    {
        return Medicine::query()
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addMonths($months))
            ->where('expiry_date', '>=', today())
            ->orderBy('expiry_date')
            ->get();
    }

    /**
     * Aggregated sales data for the chart.
     *
     * Supported periods:
     *   'daily'   — last 7 days,  label: "Sen 9 Jun"
     *   'weekly'  — last 8 weeks, label: "Minggu ke-N"
     *   'monthly' — last 12 months, label: "Jan 2025"
     *
     * @return array{labels: list<string>, data: list<float>}
     */
    public function getSalesChartData(string $period = 'weekly'): array
    {
        return match ($period) {
            'daily' => $this->dailyChartData(),
            'monthly' => $this->monthlyChartData(),
            default => $this->weeklyChartData(),
        };
    }

    /**
     * Top N best-selling medicines by total quantity sold across all time.
     *
     * @return Collection<int, object{medicine_id: int, name: string, total_qty: int}>
     */
    public function getTopSellingMedicines(int $limit = 5): Collection
    {
        return DB::table('sale_items')
            ->join('medicines', 'sale_items.medicine_id', '=', 'medicines.id')
            ->select(
                'sale_items.medicine_id',
                'medicines.name',
                DB::raw('SUM(sale_items.quantity) as total_qty')
            )
            ->groupBy('sale_items.medicine_id', 'medicines.name')
            ->orderByDesc('total_qty')
            ->limit($limit)
            ->get();
    }

    // ── Private chart helpers ─────────────────────────────────────────────────

    /**
     * @return array{labels: list<string>, data: list<float>}
     */
    private function dailyChartData(): array
    {
        $labels = [];
        $data = [];

        // Indonesianised short day names
        $dayNames = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];

        $rows = Sale::query()
            ->select(
                DB::raw('DATE(sale_date) as day'),
                DB::raw('SUM(grand_total) as total')
            )
            ->where('sale_date', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day');

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $carbon = Carbon::parse($date);

            $labels[] = $dayNames[$carbon->dayOfWeek].' '.$carbon->format('j M');
            $data[] = (float) ($rows[$date] ?? 0);
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * @return array{labels: list<string>, data: list<float>}
     */
    private function weeklyChartData(): array
    {
        $labels = [];
        $data = [];

        $rows = Sale::query()
            ->select(
                DB::raw('YEARWEEK(sale_date, 1) as yw'),
                DB::raw('SUM(grand_total) as total')
            )
            ->where('sale_date', '>=', now()->subWeeks(7)->startOfWeek())
            ->groupBy('yw')
            ->orderBy('yw')
            ->pluck('total', 'yw');

        for ($i = 7; $i >= 0; $i--) {
            $weekStart = now()->subWeeks($i)->startOfWeek();
            $yw = $weekStart->format('oW'); // ISO year + 2-digit week

            $labels[] = $weekStart->format('j M');
            $data[] = (float) ($rows[$yw] ?? 0);
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * @return array{labels: list<string>, data: list<float>}
     */
    private function monthlyChartData(): array
    {
        $labels = [];
        $data = [];

        $monthNames = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        $rows = Sale::query()
            ->select(
                DB::raw('DATE_FORMAT(sale_date, "%Y-%m") as ym'),
                DB::raw('SUM(grand_total) as total')
            )
            ->where('sale_date', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('ym')
            ->orderBy('ym')
            ->pluck('total', 'ym');

        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $ym = $month->format('Y-m');

            $labels[] = $monthNames[(int) $month->format('n')].' '.$month->format('Y');
            $data[] = (float) ($rows[$ym] ?? 0);
        }

        return ['labels' => $labels, 'data' => $data];
    }
}
