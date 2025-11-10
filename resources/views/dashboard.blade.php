@extends('layouts.main')

@section('title', 'NutaPOS - Dashboard')

@section('content')

<div class="container">

    <!-- Main Content -->
    <div class="dashboard">
        <h2 class="section-title">Ringkasan Penjualan</h2>

        <!-- Filter bar -->
        <div class="filter-bar">
            <button class="filter-btn">🏪 Semua Outlet</button>
            <button class="filter-btn">📅 16 Apr 2025 - 20 Mei 2025</button>
        </div>

        <!-- Cards -->
        <div class="cards">
            <div class="card card-large">
                <p>Total Penjualan</p>
                <h3>Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</h3>
            </div>
            <div class="card">
                <p>Jumlah Transaksi</p>
                <h3>{{ $jumlahTransaksi }} transaksi</h3>
            </div>
            <div class="card">
                <p>Rata-rata Transaksi</p>
                <h3>Rp {{ number_format($rataRata, 0, ',', '.') }}</h3>
            </div>
            <div class="card">
                <p>Laba Kotor</p>
                <h3>Rp {{ number_format($labaKotor, 0, ',', '.') }}</h3>
            </div>
            <div class="card">
                <p>Total Biaya</p>
                <h3>Rp {{ number_format($totalBiaya, 0, ',', '.') }}</h3>
            </div>
        </div>

        <!-- Chart -->
        <h2 class="subtitle">Grafik Penjualan</h2>
        <div class="chart-box">
            <canvas id="salesChart"></canvas>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('salesChart');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: {!! json_encode($labels) !!}, // dari controller
        datasets: [{
            label: 'Total Penjualan',
            data: {!! json_encode($data) !!},
            borderWidth: 2,
            borderColor: 'rgba(75,192,192,1)',
            backgroundColor: 'rgba(75,192,192,0.2)',
            fill: true,
            tension: 0.3
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>



@endsection

