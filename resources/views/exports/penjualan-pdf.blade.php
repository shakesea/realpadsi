<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Penjualan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #22c55e;
            padding-bottom: 20px;
        }

        .header h2 {
            margin: 0;
            color: #22c55e;
            font-size: 24px;
        }

        .periode {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 25px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 25px 0;
            font-size: 14px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #22c55e;
            color: white;
            font-weight: bold;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .total-section {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 5px;
            text-align: right;
        }

        .summary-section {
            display: flex;
            justify-content: space-between;
            margin: 20px 0;
            gap: 15px;
        }

        .summary-box {
            flex: 1;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
            text-align: center;
        }

        .summary-box h4 {
            margin: 0 0 10px 0;
            color: #666;
        }

        .summary-box .value {
            font-size: 16px;
            font-weight: bold;
            color: #22c55e;
        }

        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>Laporan Penjualan</h2>
        <p style="margin:5px 0 0;color:#666;font-size:14px;">{{ config('app.name', 'NutaPOS') }}</p>
    </div>

    <div class="periode">
        <strong>Periode:</strong><br>
        {{ \Carbon\Carbon::parse($start)->isoFormat('D MMMM Y') }} - {{ \Carbon\Carbon::parse($end)->isoFormat('D MMMM Y') }}
    </div>

    <div class="summary-section">
        <div class="summary-box">
            <h4>Total Transaksi</h4>
            <div class="value">{{ count($laporan) }}</div>
        </div>
        @if(count($laporan) > 0)
        <div class="summary-box">
            <h4>Rata-rata Transaksi</h4>
            <div class="value">Rp {{ number_format($laporan->avg('total'), 0, ',', '.') }}</div>
        </div>
        <div class="summary-box">
            <h4>Transaksi Tertinggi</h4>
            <div class="value">Rp {{ number_format($laporan->max('total'), 0, ',', '.') }}</div>
        </div>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Waktu</th>
                <th>Kode Transaksi</th>
                <th>Kasir</th>
                <th style="text-align:right">Total</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @forelse($laporan as $item)
            @php $total += $item['total']; @endphp
            <tr>
                <td>{{ $item['tanggal'] }}</td>
                <td>{{ $item['waktu'] }}</td>
                <td>{{ $item['kode'] }}</td>
                <td>{{ $item['nama'] }}</td>
                <td style="text-align:right">Rp {{ number_format($item['total'], 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align:center;color:#666;">
                    Tidak ada transaksi pada periode ini
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if(count($laporan) > 0)
    <div class="total-section">
        <span style="font-size:16px;color:#666;">Total Penjualan:</span><br>
        <span style="font-size:20px;font-weight:bold;color:#22c55e;">
            Rp {{ number_format($total, 0, ',', '.') }}
        </span>
    </div>
    @endif

    <div class="footer">
        <p>Dokumen ini dicetak pada {{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM Y HH:mm') }}</p>
    </div>
</body>

</html>