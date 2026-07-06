<?php

namespace App\Http\Controllers\Inventaris;

use App\Http\Controllers\Controller;
use App\Models\StockMutation;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Response;

/**
 * Controller untuk mencetak Laporan Hasil Stok Opname dalam bentuk PDF.
 */
class CetakStokOpnameController extends Controller
{
    /**
     * Membuat dan mengalirkan PDF laporan SO berdasarkan timestamp eksekusi.
     */
    public function __invoke(string $timestamp)
    {
        $time = Carbon::createFromTimestamp($timestamp);
        
        $mutations = StockMutation::with('medicine.category')
            ->where('type', 'adjustment')
            ->where('created_at', $time->format('Y-m-d H:i:s'))
            ->get();

        if ($mutations->isEmpty()) {
            return abort(404, 'Data Laporan Stok Opname tidak ditemukan untuk waktu tersebut.');
        }

        $headers = ['No', 'Kategori', 'Nama Obat', 'Selisih', 'Keterangan'];
        $rows = [];

        foreach ($mutations as $index => $mutation) {
            $prefix = $mutation->quantity > 0 ? '+' : '';
            $rows[] = [
                $index + 1,
                $mutation->medicine->category->name ?? '-',
                $mutation->medicine->name ?? 'Obat Dihapus',
                $prefix . $mutation->quantity,
                $mutation->notes,
            ];
        }

        $pdf = Pdf::loadView('pdf.brutalist-table', [
            'title' => 'Laporan Hasil Stok Opname',
            'subtitle' => 'Waktu Pelaksanaan: ' . $time->format('d M Y, H:i:s') . ' | Oleh: ' . (auth()->check() ? auth()->user()->name : 'Sistem'),
            'headers' => $headers,
            'rows' => $rows,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream("Laporan-SO-{$time->format('Ymd-His')}.pdf");
    }
}
