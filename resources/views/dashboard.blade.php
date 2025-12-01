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

        <!-- Grafik Stok & Member sejajar -->
        <h2 class="subtitle">Distribusi Stok & Jumlah Member</h2>
        <div class="chart-row">
            <!-- Grafik Stok -->
            <div class="chart-box-small">
                <h4 class="text-center mb-2">Distribusi Status Stok</h4>
                <canvas id="stokChart"></canvas>
            </div>

            <!-- Grafik Member -->
            <div class="chart-box-small">
                <h4 class="text-center mb-2">Jumlah Member Baru per Bulan ({{ date('Y') }})</h4>
                <canvas id="memberChart"></canvas>
            </div>
        </div>

    </div>
</div>

<!-- Chart JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Pastikan chart tampil tajam di layar resolusi tinggi -->
<script>
Chart.defaults.devicePixelRatio = window.devicePixelRatio || 1;
</script>

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
            borderColor: '#1DB954',
            backgroundColor: 'rgba(29,185,84,0.15)',
            fill: true,
            tension: 0.3
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
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
            backgroundColor: ['#2ECC71', '#F1C40F', '#E74C3C'],
            hoverOffset: 8
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});


/* -----------------------------
   GRAFIK MEMBER PER BULAN (BAR)
------------------------------ */
const memberCtx = document.getElementById('memberChart');
new Chart(memberCtx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($memberLabels) !!},
        datasets: [{
            label: 'Jumlah Member Baru',
            data: {!! json_encode($memberData) !!},
            backgroundColor: 'rgba(40, 167, 69, 0.6)',
            borderColor: 'rgba(40, 167, 69, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: { precision: 0 }
            }
        },
        plugins: {
            legend: { display: false }
        }
    }
});
</script>

@endsection
