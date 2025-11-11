@extends('layouts.main')
@section('title', 'NutaPOS - Laporan Penjualan')

@section('content')

{{-- ✅ Flash Notifikasi --}}
@if(session('success'))
<div class="flash-alert flash-success">
    {{ session('success') }}
</div>
@endif

@if($errors->any())
<div class="flash-alert flash-error">
    {{ $errors->first() }}
</div>
@endif



<div class="laporan-container">
    <div class="laporan-header">
        {{-- 🔍 Filter Periode --}}
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

            {{-- 📄 Tombol Ekspor PDF --}}
            <form action="{{ route('penjualan.index') }}" method="GET" style="display:inline;">
                <input type="hidden" name="export" value="pdf">
                <input type="hidden" name="start" value="{{ request('start', $start) }}">
                <input type="hidden" name="end" value="{{ request('end', $end) }}">
                <button type="submit" class="pdf-link">Export PDF</button>
            </form>
        </div>
    </div>

    {{-- 📊 Tabel Laporan --}}
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
                                    Total: {{ $data['total_qty'] }} items (Rp {{ number_format($data['total_amount'], 0, ',', '.') }})
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
                    <td style="text-align:right;">
                        @php
                            $poinDipakai = $item['member']['poin_digunakan'] ?? 0;
                            $potonganRp = $poinDipakai * 100;
                            $totalBruto = $item['total'];
                            $totalFinal = max($totalBruto - $potonganRp, 0);
                        @endphp

                        @if($poinDipakai > 0)
                            <div>Bruto: <small>Rp {{ number_format($totalBruto,0,',','.') }}</small></div>
                            <div>Potongan ({{ $poinDipakai }} pts): 
                                <small style="color:#d32;">- Rp {{ number_format($potonganRp,0,',','.') }}</small>
                            </div>
                            <div style="margin-top:6px"><strong>Bayar: Rp {{ number_format($totalFinal,0,',','.') }}</strong></div>
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
</div>

{{-- 🎨 Style --}}
<style>
.flash-alert {
    padding: 12px 20px;
    border-radius: 8px;
    margin-bottom: 15px;
    font-weight: 500;
    animation: fadeIn 0.3s ease-in-out;
}
.flash-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}
.flash-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-5px); }
    to { opacity: 1; transform: translateY(0); }
}
.btn-filter, .pdf-link {
    padding: 6px 15px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    background: #4CAF50;
    color: #fff;
    text-decoration: none;
    transition: background 0.2s ease;
}
.btn-filter:hover, .pdf-link:hover {
    background: #45a049;
}
.laporan-table {
    width: 100%;
    border-collapse: collapse;
}
.laporan-table th, .laporan-table td {
    border: 1px solid #ddd;
    padding: 10px;
}
.laporan-table th {
    background: #f5f5f5;
}
.member-info {
    background: #f8f8f8;
    padding: 5px;
    border-radius: 4px;
    margin-top: 5px;
    font-size: 0.9em;
}
.item-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 3px;
}
</style>
@endsection
 