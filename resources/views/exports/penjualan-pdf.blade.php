<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Penjualan Lengkap</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.5;
            margin: 25px;
            color: #333;
        }

        h2 {
            text-align: center;
            color: #22c55e;
            margin-bottom: 5px;
        }

        .periode {
            text-align: center;
            font-size: 14px;
            margin-bottom: 25px;
            color: #555;
        }

        .summary-section {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 25px;
        }

        .summary-box {
            flex: 1;
            background: #f8f9fa;
            padding: 12px;
            border-radius: 5px;
            text-align: center;
            border: 1px solid #ddd;
        }

        .summary-box h4 {
            margin: 0;
            font-size: 14px;
            color: #666;
        }

        .summary-box .value {
            font-size: 16px;
            font-weight: bold;
            color: #22c55e;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            font-size: 13px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            vertical-align: top;
        }

        th {
            background: #22c55e;
            color: #fff;
            text-align: left;
        }

        .kategori-title {
            font-weight: bold;
            color: #666;
            margin-top: 8px;
            margin-bottom: 4px;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px dashed #ddd;
            padding: 2px 0;
        }

        .item-name { flex: 1; }
        .item-qty { width: 40px; text-align: center; }
        .item-price { width: 100px; text-align: right; }

        .member-box {
            background: #f7f7f7;
            padding: 6px;
            border-radius: 4px;
            font-size: 12px;
            margin-top: 5px;
        }

        .total-box {
            text-align: right;
            font-size: 13px;
            line-height: 1.4;
        }

        .footer {
            text-align: center;
            font-size: 11px;
            color: #666;
            margin-top: 30px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }

        .right { text-align: right; }
        .text-muted { color: #666; }
    </style>
</head>

<body>
    <h2>Laporan Penjualan</h2>
    <div class="periode">
        Periode: {{ \Carbon\Carbon::parse($start)->isoFormat('D MMMM Y') }}
        - {{ \Carbon\Carbon::parse($end)->isoFormat('D MMMM Y') }}
    </div>

    {{-- RINGKASAN TRANSAKSI --}}
    @if(count($laporan) > 0)
    <div class="summary-section">
        <div class="summary-box">
            <h4>Total Transaksi</h4>
            <div class="value">{{ count($laporan) }}</div>
        </div>
        <div class="summary-box">
            <h4>Rata-rata Transaksi</h4>
            <div class="value">Rp {{ number_format($laporan->avg('total'), 0, ',', '.') }}</div>
        </div>
        <div class="summary-box">
            <h4>Transaksi Tertinggi</h4>
            <div class="value">Rp {{ number_format($laporan->max('total'), 0, ',', '.') }}</div>
        </div>
    </div>
    @endif

    {{-- DETAIL PER TRANSAKSI --}}
    <table>
        <thead>
            <tr>
                <th style="width:15%;">Tanggal</th>
                <th style="width:15%;">Metode</th>
                <th style="width:35%;">Item & Kategori</th>
                <th style="width:20%;">Kasir/Member</th>
                <th style="width:15%;" class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp
            @forelse($laporan as $item)
            @php
                $poinDipakai = $item['member']['poin_digunakan'] ?? 0;
                $potonganRp = $poinDipakai * 100;
                $totalBruto = $item['total'];
                $totalFinal = max($totalBruto - $potonganRp, 0);
                $grandTotal += $totalFinal;
            @endphp

            <tr>
                <td>
                    <strong>{{ $item['tanggal'] }}</strong><br>
                    <small>{{ $item['waktu'] }}</small><br>
                    <small class="text-muted">#{{ $item['kode'] }}</small>
                </td>
                <td>{{ $item['metode'] }}</td>
                <td>
                    @foreach($item['items'] as $kategori => $data)
                        <div class="kategori-title">{{ $kategori }}</div>
                        @foreach($data['items'] as $menu)
                            <div class="item-row">
                                <span class="item-name">{{ $menu['nama'] }}</span>
                                <span class="item-qty">{{ $menu['qty'] }}x</span>
                                <span class="item-price">Rp {{ number_format($menu['subtotal'],0,',','.') }}</span>
                            </div>
                        @endforeach
                        <div style="text-align:right; font-size:12px; color:#666; margin-top:3px;">
                            Total: {{ $data['total_qty'] }} item(s) — Rp {{ number_format($data['total_amount'],0,',','.') }}
                        </div>
                        <hr style="border:none;border-top:1px dashed #ccc;margin:5px 0;">
                    @endforeach
                </td>
                <td>
                    <strong>Kasir:</strong> {{ $item['nama'] }}<br>
                    @if($item['member'])
                        <div class="member-box">
                            <strong>Member:</strong> {{ $item['member']['nama'] }}<br>
                            <small>Poin digunakan: {{ $item['member']['poin_digunakan'] }}</small><br>
                            <small>Poin didapat: {{ $item['member']['poin_didapat'] }}</small>
                        </div>
                    @endif
                </td>
                <td class="total-box">
                    @if($poinDipakai > 0)
                        <div>Bruto: Rp {{ number_format($totalBruto,0,',','.') }}</div>
                        <div>Potongan ({{ $poinDipakai }} poin): -Rp {{ number_format($potonganRp,0,',','.') }}</div>
                        <div><strong>Bayar: Rp {{ number_format($totalFinal,0,',','.') }}</strong></div>
                    @else
                        <strong>Rp {{ number_format($totalBruto,0,',','.') }}</strong>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align:center;color:#777;">Tidak ada transaksi pada periode ini</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- TOTAL KESELURUHAN --}}
    @if(count($laporan) > 0)
    <div style="text-align:right; font-size:15px; margin-top:10px;">
        <span style="color:#555;">Total Penjualan Keseluruhan:</span>
        <strong style="color:#22c55e;">Rp {{ number_format($grandTotal, 0, ',', '.') }}</strong>
    </div>
    @endif

    <div class="footer">
        Dicetak pada {{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM Y HH:mm') }}<br>
        © {{ config('app.name', 'NutaPOS') }}
    </div>
</body>
</html>
