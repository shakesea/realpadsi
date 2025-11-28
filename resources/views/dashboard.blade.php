@extends('layouts.main')

@section('title', 'NutaPOS - Dashboard')

@section('content')

<div class="container">

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

        <!-- Grafik Penjualan -->
        <h2 class="subtitle">Grafik Penjualan</h2>
        <div class="chart-box" style="margin-bottom: 40px;">
            <canvas id="salesChart"></canvas>
        </div>

        <!-- Grafik Stok -->
        <h2 class="subtitle">Distribusi Status Stok</h2>
        <div class="chart-box" style="max-width: 500px; margin: auto;">
            <canvas id="stokChart"></canvas>
        </div>

    </div>
</div>

<!-- Chart JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
/* -------------------------
   GRAFIK PENJUALAN (LINE)
-------------------------- */
const salesCtx = document.getElementById('salesChart');

new Chart(salesCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode($labels) !!},
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
            y: { beginAtZero: true }
        }
    }
});


/* -----------------------------
   GRAFIK STATUS STOK (DONUT)
------------------------------ */
const stokCtx = document.getElementById('stokChart');

new Chart(stokCtx, {
    type: 'doughnut',
    data: {
        labels: ['Aman', 'Menipis', 'Habis'],
        datasets: [{
            data: [
                {{ $stokAman ?? 0 }},
                {{ $stokMenipis ?? 0 }},
                {{ $stokHabis ?? 0 }}
            ],
            backgroundColor: [
                '#4CAF50',
                '#FFC107',
                '#F44336'
            ],
            hoverOffset: 8
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>

@endsection
