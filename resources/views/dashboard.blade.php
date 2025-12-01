@extends('layouts.main')

@section('title', 'NutaPOS - Dashboard')

@section('content')

<div class="container">
    <div class="dashboard">

        <h2 class="section-title">Ringkasan Penjualan</h2>

        <!-- FILTER FORM -->
        <form method="POST" id="filterForm" class="filter-bar">
            @csrf
            <input type="date" name="start_date" class="filter-btn" value="{{ date('Y-m-01') }}">
            <input type="date" name="end_date" class="filter-btn" value="{{ date('Y-m-t') }}">
            <button type="submit" class="filter-btn btn-filter" style="background:#33aa33; color:white;">
                Terapkan Filter
            </button>
        </form>

        <!-- CARD SUMMARY -->
        <div class="cards">
            <div class="card card-large">
                <p>Total Penjualan</p>
                <h3 id="card-total">Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</h3>
            </div>

            <div class="card">
                <p>Jumlah Transaksi</p>
                <h3 id="card-transaksi">{{ $jumlahTransaksi }} transaksi</h3>
            </div>

            <div class="card">
                <p>Rata-rata Transaksi</p>
                <h3 id="card-rata">Rp {{ number_format($rataRata, 0, ',', '.') }}</h3>
            </div>

            <div class="card">
                <p>Laba Kotor</p>
                <h3 id="card-laba">Rp {{ number_format($labaKotor, 0, ',', '.') }}</h3>
            </div>

            <div class="card">
                <p>Total Biaya</p>
                <h3 id="card-biaya">Rp {{ number_format($totalBiaya, 0, ',', '.') }}</h3>
            </div>
        </div>

        <!-- GRAFIK PENJUALAN -->
        <h2 class="subtitle">Grafik Penjualan</h2>
        <div class="chart-box" style="margin-bottom: 40px; height:400px; position:relative;">
            <div id="chart-loader" class="chart-loader">⏳ Memuat data...</div>
            <canvas id="salesChart"></canvas>
        </div>

        <!-- GRAFIK STOK & MEMBER -->
        <h2 class="subtitle">Distribusi Stok & Jumlah Member</h2>
        <div class="chart-row">
            <div class="chart-box-small">
                <h4 class="text-center mb-2">Distribusi Status Stok</h4>
                <canvas id="stokChart"></canvas>
            </div>

            <div class="chart-box-small">
                <h4 class="text-center mb-2">Jumlah Member Baru</h4>
                <canvas id="memberChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
.chart-loader {
    display: none;
    position: absolute;
    top: 45%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(255,255,255,0.9);
    padding: 10px 18px;
    border-radius: 8px;
    font-weight: 600;
    color: #1DB954;
    font-size: 15px;
}
.btn-filter:disabled { opacity: 0.7; cursor: not-allowed; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('filterForm');
    const loader = document.getElementById('chart-loader');
    const salesChartCanvas = document.getElementById('salesChart');
    const stokChartCanvas = document.getElementById('stokChart');
    const memberChartCanvas = document.getElementById('memberChart');

    let salesChart, stokChart, memberChart;

    // INIT CHARTS
    function initCharts() {
        salesChart = new Chart(salesChartCanvas, {
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
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
        });

        stokChart = new Chart(stokChartCanvas, {
            type: 'doughnut',
            data: {
                labels: ['Aman', 'Menipis', 'Habis'],
                datasets: [{ data: [{{ $stokAman }}, {{ $stokMenipis }}, {{ $stokHabis }}],
                backgroundColor: ['#2ECC71', '#F1C40F', '#E74C3C'], hoverOffset: 8 }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });

        memberChart = new Chart(memberChartCanvas, {
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
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
        });
    }
    initCharts();

    // FILTER AJAX
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(form);
        const btn = form.querySelector('button');
        btn.innerText = '⏳ Memuat...';
        btn.disabled = true;
        loader.style.display = 'block';

        fetch("{{ route('dashboard.filter.ajax') }}", {
            method: "POST",
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            document.getElementById('card-total').innerText = 'Rp ' + data.totalPenjualan;
            document.getElementById('card-transaksi').innerText = data.jumlahTransaksi + ' transaksi';
            document.getElementById('card-rata').innerText = 'Rp ' + data.rataRata;
            document.getElementById('card-laba').innerText = 'Rp ' + data.labaKotor;
            document.getElementById('card-biaya').innerText = 'Rp ' + data.totalBiaya;

            salesChart.data.labels = data.labels;
            salesChart.data.datasets[0].data = data.data;
            salesChart.update();

            stokChart.data.datasets[0].data = [data.stok.Aman, data.stok.Menipis, data.stok.Habis];
            stokChart.update();

            memberChart.data.labels = data.memberLabels;
            memberChart.data.datasets[0].data = data.memberData;
            memberChart.update();
        })
        .catch(err => alert('Gagal memuat data.'))
        .finally(() => {
            btn.innerText = 'Terapkan Filter';
            btn.disabled = false;
            loader.style.display = 'none';
        });
    });
});
</script>

@endsection
