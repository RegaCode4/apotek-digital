<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f8f9fa;
            color: #1a1a1a;
            padding: 20px;
        }
        h1 {
            font-size: 24px;
            font-weight: 900;
            margin-bottom: 5px;
            text-transform: uppercase;
            border-bottom: 4px solid #1a1a1a;
            padding-bottom: 10px;
        }
        p {
            font-size: 14px;
            font-weight: bold;
            color: #4a4a4a;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background-color: #ffffff;
            border: 3px solid #1a1a1a;
        }
        th {
            background-color: #93c5fd; /* blue-300 accent */
            color: #1a1a1a;
            font-weight: 900;
            text-align: left;
            padding: 10px;
            border: 2px solid #1a1a1a;
            text-transform: uppercase;
            font-size: 12px;
        }
        td {
            padding: 8px 10px;
            border: 2px solid #1a1a1a;
            font-size: 12px;
            font-weight: 600;
        }
        tr:nth-child(even) td {
            background-color: #e2e8f0; /* slate-200 */
        }
        .footer {
            margin-top: 30px;
            font-size: 10px;
            font-weight: bold;
            text-align: right;
            border-top: 2px solid #1a1a1a;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    @if(isset($subtitle))
        <p>{{ $subtitle }}</p>
    @endif

    <table>
        <thead>
            <tr>
                @foreach($headers as $index => $header)
                    @php
                        $col = strtolower($header);
                        $align = 'left';
                        if (in_array($col, ['total item', 'jumlah', 'stok', 'stok saat ini', 'min. stok', 'sisa hari'])) {
                            $align = 'center';
                        } elseif (in_array($col, ['grand total', 'cash', 'transfer', 'bpjs', 'asuransi', 'total'])) {
                            $align = 'right';
                        }
                    @endphp
                    <th style="text-align: {{ $align }}">{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
                <tr>
                    @foreach($row as $index => $cell)
                        @php
                            $align = 'left';
                            if (isset($headers[$index])) {
                                $col = strtolower($headers[$index]);
                                if (in_array($col, ['total item', 'jumlah', 'stok', 'stok saat ini', 'min. stok', 'sisa hari'])) {
                                    $align = 'center';
                                } elseif (in_array($col, ['grand total', 'cash', 'transfer', 'bpjs', 'asuransi', 'total'])) {
                                    $align = 'right';
                                }
                            }
                        @endphp
                        <td style="text-align: {{ $align }}">{{ $cell }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada: {{ now()->timezone('Asia/Jakarta')->format('d M Y H:i:s') }}
    </div>
</body>
</html>
