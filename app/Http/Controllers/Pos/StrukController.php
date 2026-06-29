<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

/**
 * Membuat PDF struk untuk penjualan yang selesai.
 */
class StrukController extends Controller
{
    /**
     * Membuat dan mengalirkan PDF struk untuk penjualan yang diberikan.
     */
    public function __invoke(Sale $sale): Response
    {
        $sale->loadMissing(['saleItems.medicine', 'cashier']);

        $pdf = Pdf::loadView('pdf.struk', ['sale' => $sale])
            ->setPaper([0, 0, 164.41, 841.89], 'portrait'); // 58mm thermal width

        return $pdf->stream("struk-{$sale->invoice_no}.pdf");
    }
}
