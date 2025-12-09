@extends('layouts.main')

@section('title', 'NutaPOS - Dashboard')

@section('content')

<div class="container">
    <div class="dashboard">

        <h2 class="section-title">Ringkasan Penjualan</h2>

        <!-- FILTER FORM -->
        <form method="POST" id="filterForm" action="{{ route('dashboard.filter.ajax') }}" class="filter-bar">
            @csrf
            <input type="date" name="start_date" class="filter-btn" value="{{ request('start_date', $firstDate) }}">
            <input type="date" name="end_date" class="filter-btn" value="{{ request('end_date', $today) }}">
            <button type="submit" class="filter-btn btn-filter">
                Terapkan Filter
            </button>
        </form>

        <!-- CARD SUMMARY -->
        <div class="cards">
            <div class="card card-large">
                <p>Total Penjualan</p>
                <h3 id="card-total">Rp {{ number_format($totalPenjualan,0,',','.') }}</h3>
            </div>
            <div class="card">
                <p>Jumlah Transaksi</p>
                <h3 id="card-transaksi">{{ $jumlahTransaksi }} transaksi</h3>
            </div>
            <div class="card">
                <p>Rata-rata Transaksi</p>
                <h3 id="card-rata">Rp {{ number_format($rataRata,0,',','.') }}</h3>
            </div>
            <div class="card">
                <p>Laba Kotor</p>
                <h3 id="card-laba">Rp {{ number_format($labaKotor,0,',','.') }}</h3>
            </div>
            <div class="card">
                <p>Menu Paling Laris</p>
                <h3 id="card-menu">{{ $menuPalingLaris }}</h3>
            </div>
            <div class="card">
                <p>Total Member</p>
                <h3 id="card-member">{{ $totalMember }}</h3>
            </div>
        </div>

        <!-- LINE GRAFIK PENJUALAN -->
        <h2 class="subtitle">Grafik Penjualan</h2>
        <div class="chart-box" style="height:400px;position:relative;">
            <div id="chart-loader" class="chart-loader">⏳ Memuat data...</div>
            <canvas id="salesChart"></canvas>
        </div>

        <!-- GRAFIK STOK -->
        <div class="chart-row">
            <div class="chart-box-small">
                <h4 class="text-center">Top 10 Stok Sering Digunakan</h4>
                <canvas id="topStokChart"></canvas>
            </div>
            <div class="chart-box-small">
                <h4 class="text-center">Distribusi Status Stok</h4>
                <canvas id="stokChart"></canvas>
            </div>
        </div>

        <!-- GRAFIK MEMBER -->
        <div class="chart-row">
            <div class="chart-box-small">
                <h4 class="text-center">Jumlah Member Baru</h4>
                <canvas id="memberChart"></canvas>
            </div>
            <div class="chart-box-small">
                <h4 class="text-center">Top 5 Member Paling Aktif (Poin)</h4>
                <canvas id="topMemberChart"></canvas>
            </div>
        </div>

    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('filterForm');
    const loader = document.getElementById('chart-loader');
    const ctx = id => document.getElementById(id);

    // ======== LINE CHART (PENJUALAN) ========
    let salesChart = new Chart(ctx('salesChart'), {
        type: 'line',
        data: { 
            labels: {!! json_encode($labels) !!}, 
            datasets: [{
                label: 'Total Penjualan',
                data: {!! json_encode($data) !!},
                borderColor: '#1DB954',
                backgroundColor: 'rgba(29,185,84,0.15)',
                fill: true,
                tension: 0.4
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });

    // ======== BAR CHART (TOP 10 STOK SERING DIGUNAKAN) ========
    let topStokChart = new Chart(ctx('topStokChart'), {
        type: 'bar',
        data: { 
            labels: {!! json_encode($topStokNames) !!},
            datasets: [{
                label: 'Jumlah Item Tersisa',
                data: {!! json_encode($topStokCounts) !!},
                backgroundColor: 'rgba(52,152,219,0.6)',
                borderColor: '#2980b9',
                borderWidth: 1
            }]
        },
        options: { 
            responsive: true,
            maintainAspectRatio: false,
            scales: { 
                x: { grid: { display: false } },
                y: { beginAtZero: true } 
            },
            datasets: {
                bar: {
                    categoryPercentage: 0.6,
                    barPercentage: 0.8
                }
            }
        }
    });

    // ======== PIE CHART (DISTRIBUSI STOK) ========
    let stokChart = new Chart(ctx('stokChart'), {
        type: 'doughnut',
        data: { 
            labels: ['Aman','Menipis','Habis'], 
            datasets: [{
                data: [{{ $stokAman }}, {{ $stokMenipis }}, {{ $stokHabis }}],
                backgroundColor: ['#2ECC71','#F1C40F','#E74C3C']
            }] 
        },
        options: { plugins: { legend: { position: 'bottom' } } }
    });

    // ======== BAR CHART (JUMLAH MEMBER BARU) ========
    let memberChart = new Chart(ctx('memberChart'), {
        type: 'bar',
        data: { 
            labels: {!! json_encode($memberLabels) !!},
            datasets: [{
                label: 'Member Baru',
                data: {!! json_encode($memberData) !!},
                backgroundColor: 'rgba(46,204,113,0.6)', 
                borderColor: '#27AE60',
                borderWidth: 1
            }]
        },
        options: { 
            responsive: true,
            maintainAspectRatio: false,
            scales: { 
                x: { grid: { display: false } },
                y: { beginAtZero: true } 
            },
            datasets: {
                bar: {
                    categoryPercentage: 0.6,
                    barPercentage: 0.8
                }
            }
        }
    });

    // ======== PIE CHART (TOP 5 MEMBER PALING AKTIF) ========
    let topMemberChart = new Chart(ctx('topMemberChart'), {
        type: 'pie',
        data: { 
            labels: {!! json_encode($topMemberNames) !!}, 
            datasets: [{
                data: {!! json_encode($topMemberPoints) !!}, 
                backgroundColor: ['#1abc9c','#3498db','#9b59b6','#f1c40f','#e74c3c']
            }]
        },
        options: { plugins: { legend: { position: 'bottom' } } }
    });

    // ======== AJAX FILTER ========
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(form);
        const btn = form.querySelector('button');
        btn.innerText = '⏳ Memuat...';
        btn.disabled = true;
        loader.style.display = 'block';

        fetch(form.action, {
            method: "POST",
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            // Update card summary
            document.getElementById('card-total').innerText = 'Rp ' + data.totalPenjualan;
            document.getElementById('card-transaksi').innerText = data.jumlahTransaksi + ' transaksi';
            document.getElementById('card-rata').innerText = 'Rp ' + data.rataRata;
            document.getElementById('card-laba').innerText = 'Rp ' + data.labaKotor;
            document.getElementById('card-menu').innerText = data.menuPalingLaris;
            document.getElementById('card-member').innerText = data.totalMember;

            // Update chart data
            salesChart.data.labels = data.labels;
            salesChart.data.datasets[0].data = data.data;
            salesChart.update();

            topStokChart.data.labels = data.topStokNames;
            topStokChart.data.datasets[0].data = data.topStokCounts;
            topStokChart.update();

            stokChart.data.datasets[0].data = [data.stok.Aman, data.stok.Menipis, data.stok.Habis];
            stokChart.update();

            memberChart.data.labels = data.memberLabels;
            memberChart.data.datasets[0].data = data.memberData;
            memberChart.update();

            topMemberChart.data.labels = data.topMemberNames;
            topMemberChart.data.datasets[0].data = data.topMemberPoints;
            topMemberChart.update();
        })
        .catch(() => alert('❌ Gagal memuat data filter!'))
        .finally(() => {
            btn.innerText = 'Terapkan';
            btn.disabled = false;
            loader.style.display = 'none';
        });
    });
});
</script>

@endsection
