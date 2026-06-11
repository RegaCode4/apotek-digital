<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            color: #111;
            padding: 12px 10px;
            width: 100%;
        }

        /* ── Header ── */
        .header {
            text-align: center;
            border-bottom: 1px dashed #555;
            padding-bottom: 8px;
            margin-bottom: 8px;
        }

        .header h1 {
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .header p {
            font-size: 10px;
            color: #444;
            margin-top: 2px;
        }

        /* ── Meta info ── */
        .meta {
            margin-bottom: 8px;
        }

        .meta table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta td {
            font-size: 10px;
            padding: 1px 0;
            vertical-align: top;
        }

        .meta td:first-child {
            width: 80px;
            color: #555;
        }

        .meta td:nth-child(2) {
            width: 8px;
            color: #555;
        }

        /* ── Item table ── */
        .divider {
            border-top: 1px dashed #555;
            margin: 6px 0;
        }

        .items {
            width: 100%;
            border-collapse: collapse;
        }

        .items thead td {
            font-size: 10px;
            font-weight: bold;
            padding: 2px 0;
            border-bottom: 1px dashed #999;
        }

        .items tbody td {
            font-size: 10px;
            padding: 3px 0;
            vertical-align: top;
        }

        .items .name {
            width: 50%;
        }

        .items .qty {
            width: 12%;
            text-align: center;
        }

        .items .price {
            width: 19%;
            text-align: right;
        }

        .items .subtotal {
            width: 19%;
            text-align: right;
        }

        /* ── Summary ── */
        .summary {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2px;
        }

        .summary td {
            font-size: 10px;
            padding: 2px 0;
        }

        .summary .label {
            color: #555;
        }

        .summary .value {
            text-align: right;
        }

        .summary .grand-total td {
            font-size: 12px;
            font-weight: bold;
            padding-top: 4px;
            border-top: 1px dashed #555;
        }

        /* ── Footer ── */
        .footer {
            border-top: 1px dashed #555;
            margin-top: 10px;
            padding-top: 8px;
            text-align: center;
            font-size: 10px;
            color: #555;
        }
    </style>
</head>
<body>

    {{-- ═══════════════════════════ HEADER ═══════════════════════════ --}}
    <div class="header">
        <h1>Apotek Digital</h1>
        <p>Jl. Kesehatan No. 1, Jakarta</p>
        <p>Telp: (021) 000-0000</p>
    </div>

    {{-- ═══════════════════════════ META INFO ═══════════════════════════ --}}
    <div class="meta">
        <table>
            <tr>
                <td>Invoice</td>
                <td>:</td>
                <td><strong>{{ $sale->invoice_no }}</strong></td>
            </tr>
            <tr>
                <td>Tanggal</td>
                <td>:</td>
                <td>{{ $sale->sale_date->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td>Kasir</td>
                <td>:</td>
                <td>{{ $sale->cashier->name ?? '-' }}</td>
            </tr>
            <tr>
                <td>Pembeli</td>
                <td>:</td>
                <td>{{ $sale->buyer_name }}</td>
            </tr>
            <tr>
                <td>Pembayaran</td>
                <td>:</td>
                <td>{{ strtoupper($sale->payment_method) }}</td>
            </tr>
        </table>
    </div>

    <div class="divider"></div>

    {{-- ═══════════════════════════ ITEM TABLE ═══════════════════════════ --}}
    <table class="items">
        <thead>
            <tr>
                <td class="name">Nama Obat</td>
                <td class="qty">Qty</td>
                <td class="price">Harga</td>
                <td class="subtotal">Total</td>
            </tr>
        </thead>
        <tbody>
            @foreach ($sale->saleItems as $item)
                <tr>
                    <td class="name">{{ $item->medicine->name ?? '-' }}</td>
                    <td class="qty">{{ $item->quantity }}</td>
                    <td class="price">{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td class="subtotal">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
                @if ($item->discount > 0)
                    <tr>
                        <td class="name" colspan="3" style="color:#777; font-size:9px; padding-left:6px;">
                            Diskon item
                        </td>
                        <td class="subtotal" style="color:#777;">-{{ number_format($item->discount, 0, ',', '.') }}</td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    <div class="divider"></div>

    {{-- ═══════════════════════════ SUMMARY ═══════════════════════════ --}}
    <table class="summary">
        <tr>
            <td class="label">Subtotal</td>
            <td class="value">Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</td>
        </tr>
        @if ($sale->discount_amount > 0)
            <tr>
                <td class="label">Diskon</td>
                <td class="value">- Rp {{ number_format($sale->discount_amount, 0, ',', '.') }}</td>
            </tr>
        @endif
        @if ($sale->tax_amount > 0)
            <tr>
                <td class="label">PPN 11%</td>
                <td class="value">Rp {{ number_format($sale->tax_amount, 0, ',', '.') }}</td>
            </tr>
        @endif
        <tr class="grand-total">
            <td class="label">GRAND TOTAL</td>
            <td class="value">Rp {{ number_format($sale->grand_total, 0, ',', '.') }}</td>
        </tr>
    </table>

    {{-- ═══════════════════════════ FOOTER ═══════════════════════════ --}}
    <div class="footer">
        <p>Terima kasih atas kepercayaan Anda</p>
        <p style="margin-top:4px;">Barang yang sudah dibeli tidak dapat dikembalikan</p>
        <p style="margin-top:6px; font-size:9px; color:#999;">
            Dicetak: {{ now()->format('d/m/Y H:i:s') }}
        </p>
    </div>

</body>
</html>
