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
                <h3>Rp 10.000.000</h3>
            </div>
            <div class="card">
                <p>Jumlah Transaksi</p>
                <h3>100 transaksi</h3>
            </div>
            <div class="card">
                <p>Rata-rata Transaksi</p>
                <h3>Rp 39.000</h3>
            </div>
            <div class="card">
                <p>Laba Kotor</p>
                <h3>Rp 18.000.000</h3>
            </div>
            <div class="card">
                <p>Total Biaya</p>
                <h3>Rp 15.240.000</h3>
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
        labels: ['30 Apr', '1 Mei', '2 Mei', '3 Mei', '4 Mei', '5 Mei'],
        datasets: [
            { label: 'April', data: [20, 15, 18, 17, 21, 19], borderWidth: 2, fill: true },
            { label: 'Mei', data: [18, 16, 19, 20, 22, 21], borderWidth: 2, fill: true }
        ]
    }
});
</script>


@endsection

