@extends('layouts.main')
@section('title', 'NutaPOS - Laporan Penjualan')

@section('content')
<div class="laporan-container">
    <div class="laporan-header">
        <form class="laporan-filter" method="" action="{{ route('penjualan.index') }}">
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
            <a href="#">Column Settings</a>
            <a href="#" class="pdf-link">PDF</a>
        </div>
    </div>

    <table class="laporan-table">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Total</th>
                <th>Kode Transaksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($laporan as $item)
            <tr>
                <td><strong>{{ $item['nama'] }}</strong><br><small>{{ $item['waktu'] }}</small></td>
                <td>Rp {{ number_format($item['total'], 0, ',', '.') }}</td>
                <td>{{ $item['kode'] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="3" style="text-align:center; color:#777;">Tidak ada transaksi pada periode ini</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
