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
            <button type="submit" class="filter-btn btn-filter">Terapkan Filter</button>
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

        <!-- LINE CHART -->
        <h2 class="subtitle">Grafik Penjualan</h2>
        <div class="chart-box" style="height:400px;position:relative;">
            <div id="chart-loader" class="chart-loader" style="display:none">⏳ Memuat data...</div>
            <canvas id="salesChart"></canvas>
        </div>

        <!-- STOK -->
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

        <!-- MEMBER -->
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

<!-- CHART JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">

<script>
document.addEventListener('DOMContentLoaded', function () {

    Chart.register(ChartDataLabels);

    const ctx = id => document.getElementById(id);
    const form = document.getElementById('filterForm');
    const loader = document.getElementById('chart-loader');

    // LINE CHART
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
        options: { responsive:true, maintainAspectRatio:false }
    });

    // BAR TOP STOK
    let topStokChart = new Chart(ctx('topStokChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($topStokNames) !!},
            datasets: [{
                label: 'Jumlah Item Tersisa',
                data: {!! json_encode($topStokCounts) !!},
                backgroundColor: 'rgba(52,152,219,0.6)'
            }]
        },
        options: { responsive:true }
    });

    // DOUGHNUT STOK (DENGAN LABEL)
    let stokChart = new Chart(ctx('stokChart'), {
        type: 'doughnut',
        data: {
            labels: ['Aman','Menipis','Habis'],
            datasets: [{
                data: [{{ $stokAman }}, {{ $stokMenipis }}, {{ $stokHabis }}],
                backgroundColor: ['#2ECC71','#F1C40F','#E74C3C']
            }]
        },
        options: {
            plugins: {
                legend: { position:'bottom' },
                datalabels: {
                    color:'#000',
                    font:{ weight:'bold', size:13 },
                    formatter:(value,ctx)=>{
                        return ctx.chart.data.labels[ctx.dataIndex] + "\n" + value;
                    }
                }
            }
        }
    });

    // BAR MEMBER
    let memberChart = new Chart(ctx('memberChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($memberLabels) !!},
            datasets: [{
                label: 'Member Baru',
                data: {!! json_encode($memberData) !!},
                backgroundColor: 'rgba(46,204,113,0.6)'
            }]
        },
        options:{ responsive:true }
    });

    // PIE TOP MEMBER (DENGAN LABEL)
    let topMemberChart = new Chart(ctx('topMemberChart'), {
        type: 'pie',
        data: {
            labels: {!! json_encode($topMemberNames) !!},
            datasets: [{
                data: {!! json_encode($topMemberPoints) !!},
                backgroundColor:['#1abc9c','#3498db','#9b59b6','#f1c40f','#e74c3c']
            }]
        },
        options: {
            plugins:{
                legend:{ position:'bottom' },
                datalabels:{
                    color:'#000',
                    font:{ weight:'bold', size:12 },
                    formatter:(value,ctx)=>{
                        return ctx.chart.data.labels[ctx.dataIndex] + "\n" + value + " poin";
                    }
                }
            }
        }
    });

    // AJAX FILTER
    form.addEventListener('submit',function(e){
        e.preventDefault();
        loader.style.display='block';

        fetch(form.action,{
            method:'POST',
            headers:{ 'X-CSRF-TOKEN':'{{ csrf_token() }}' },
            body:new FormData(form)
        })
        .then(r=>r.json())
        .then(d=>{
            document.getElementById('card-total').innerText='Rp '+d.totalPenjualan;
            document.getElementById('card-transaksi').innerText=d.jumlahTransaksi+' transaksi';
            document.getElementById('card-rata').innerText='Rp '+d.rataRata;
            document.getElementById('card-laba').innerText='Rp '+d.labaKotor;
            document.getElementById('card-menu').innerText=d.menuPalingLaris;
            document.getElementById('card-member').innerText=d.totalMember;

            salesChart.data.labels=d.labels;
            salesChart.data.datasets[0].data=d.data;
            salesChart.update();

            topStokChart.data.labels=d.topStokNames;
            topStokChart.data.datasets[0].data=d.topStokCounts;
            topStokChart.update();

            stokChart.data.datasets[0].data=[d.stok.Aman,d.stok.Menipis,d.stok.Habis];
            stokChart.update();

            memberChart.data.labels=d.memberLabels;
            memberChart.data.datasets[0].data=d.memberData;
            memberChart.update();

            topMemberChart.data.labels=d.topMemberNames;
            topMemberChart.data.datasets[0].data=d.topMemberPoints;
            topMemberChart.update();
        })
        .finally(()=>loader.style.display='none');
    });

});
</script>

@endsection
