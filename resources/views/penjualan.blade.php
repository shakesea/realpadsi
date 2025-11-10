@extends('layouts.main')
@section('title', 'NutaPOS - Laporan Penjualan')

@section('content')
<div class="laporan-container">
    <div class="laporan-header">
        <form class="laporan-filter" method="GET" action="{{ route('penjualan.index') }}">
            <label><strong>Periode:</strong></label>
            <input type="date" name="start" value="{{ request('start', \Carbon\Carbon::parse($start)->format('Y-m-d')) }}">
            <span> - </span>
            <input type="date" name="end" value="{{ request('end', \Carbon\Carbon::parse($end)->format('Y-m-d')) }}">
            <button type="submit" class="btn-filter">Terapkan</button>
        </form>
        <div class="laporan-controls">
            <label>Show
                <select>
                    <option>10</option>
                    <option>25</option>
                    <option>50</option>
                </select> entries
            </label>
            <span> | </span>
            <a href="{{ route('penjualan.index', ['export' => 'pdf', 'start' => request('start', $start), 'end' => request('end', $end)]) }}" class="pdf-link" target="_blank">Export PDF</a>
        </div>
    </div>

    <div class="table-list">
        <table class="laporan-table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Metode Pembayaran</th>
                    <th>Item & Kategori</th>
                    <th>Kasir/Member</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($laporan as $item)
                <tr>
                    <td>
                        <strong>{{ $item['tanggal'] }}</strong><br>
                        <small>{{ $item['waktu'] }}</small><br>
                        <small class="text-muted">#{{ $item['kode'] }}</small>
                    </td>
                    <td>{{ $item['metode'] }}</td>
                    <td>
                        @foreach($item['items'] as $kategori => $data)
                        <div class="kategori-group">
                            <strong class="kategori-title">{{ $kategori }}</strong>
                            <div class="kategori-items">
                                @foreach($data['items'] as $menu)
                                <div class="item-row">
                                    <span class="item-name">{{ $menu['nama'] }}</span>
                                    <span class="item-qty">{{ $menu['qty'] }}x</span>
                                    <span class="item-price">Rp {{ number_format($menu['subtotal'], 0, ',', '.') }}</span>
                                </div>
                                @endforeach
                                <div class="kategori-total">
                                    Total: {{ $data['total_qty'] }} items
                                    (Rp {{ number_format($data['total_amount'], 0, ',', '.') }})
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </td>
                    <td>
                        <strong>Kasir:</strong> {{ $item['nama'] }}<br>
                        @if($item['member'])
                        <div class="member-info">
                            <strong>Member:</strong> {{ $item['member']['nama'] }}<br>
                            <small>Poin digunakan: {{ $item['member']['poin_digunakan'] }}</small><br>
                            <small>Poin didapat: {{ $item['member']['poin_didapat'] }}</small>
                        </div>
                        @endif
                    </td>
                    <td>
                        @php
                        $poinDipakai = $item['member']['poin_digunakan'] ?? 0;
                        $potonganRp = $poinDipakai * 100; // 1 poin = Rp100
                        $totalBruto = $item['total']; // nilai dari DB
                        $totalFinal = max($totalBruto - $potonganRp, 0); // hitung potongan di sini
                        @endphp

                        @if($poinDipakai > 0)
                        <div style="text-align:right">
                        <div>Bruto: <small>Rp {{ number_format($totalBruto,0,',','.') }}</small></div>
                        <div>Potongan (Poin: {{ $poinDipakai }}): <small style="color:#d32;">- Rp {{ number_format($potonganRp,0,',','.') }}</small></div>
                        <div style="margin-top:6px"><strong>Bayar: Rp {{ number_format($totalFinal,0,',','.') }}</strong></div>
                        </div>
                        @else
                        <strong>Rp {{ number_format($totalFinal, 0, ',', '.') }}</strong>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center; color:#777;">Tidak ada transaksi pada periode ini</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <style>
        .laporan-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .laporan-table th,
        .laporan-table td {
            padding: 12px;
            border: 1px solid #ddd;
        }

        .laporan-table th {
            background: #f5f5f5;
            font-weight: bold;
            text-align: left;
        }

        .kategori-group {
            margin-bottom: 15px;
        }

        .kategori-group:last-child {
            margin-bottom: 0;
        }

        .kategori-title {
            display: block;
            margin-bottom: 5px;
            color: #666;
        }

        .kategori-items {
            padding-left: 10px;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }

        .item-name {
            flex: 1;
        }

        .item-qty {
            width: 50px;
            text-align: center;
        }

        .item-price {
            width: 120px;
            text-align: right;
        }

        .kategori-total {
            margin-top: 5px;
            padding-top: 5px;
            border-top: 1px dashed #ddd;
            font-size: 0.9em;
            color: #666;
            text-align: right;
        }

        .member-info {
            margin-top: 5px;
            padding: 5px;
            background: #f8f8f8;
            border-radius: 4px;
            font-size: 0.9em;
        }

        .text-muted {
            color: #666;
        }

        /* Filter styles */
        .laporan-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .laporan-filter {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .laporan-filter input[type="date"] {
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .btn-filter {
            padding: 5px 15px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-filter:hover {
            background: #45a049;
        }

        .pdf-link {
            padding: 5px 15px;
            background: #4CAF50; /* hijau sama seperti tombol Terapkan */
            color: #fff !important; /* teks putih permanen */
            text-decoration: none;
            border-radius: 4px;
            transition: background 0.2s;
        }

        .pdf-link:hover {
            background: #45a049; /* sedikit lebih gelap saat hover */
            color: #fff !important; /* tetap putih saat hover */
            text-decoration: none; /* hilangkan underline */
        }
    </style>
</div>
@endsection