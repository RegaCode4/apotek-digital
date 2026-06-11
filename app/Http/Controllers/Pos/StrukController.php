<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class StrukController extends Controller
{
    /**
     * Generate and stream a PDF receipt for the given sale.
     */
    public function __invoke(Sale $sale): Response
    {
        $sale->loadMissing(['saleItems.medicine', 'cashier']);

        $pdf = Pdf::loadView('pdf.struk', ['sale' => $sale])
            ->setPaper([0, 0, 226.77, 700], 'portrait'); // 80mm thermal width

        return $pdf->stream("struk-{$sale->invoice_no}.pdf");
    }
}
