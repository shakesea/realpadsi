<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=h, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@300;400;600;700&display=swap" rel="stylesheet">
    <title>Kasir</title>
</head>
<body>
    
    <div class="container">

        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo-wrap">
                <img src="{{ asset('img/nutapos_logo.png') }}" class="logo-icon">
                <span class="logo">nutapos</span>
            </div>
                <ul class="menu">
                    <li><a href="/dashboard">Dashboard</a></li>
                    <li ><a href="/pegawai">Pegawai</a></li>
                    <li class="active"><a href="/kasir">Kasir</a></li>
                    <li><a href="/stok">Stok</a></li>
                    <li><a href="/penjualan">Riwayat Penjualan</a></li>
                    <li><a href="/member">Member</a></li>
                    <li><a href="/">Tutup Outlet</a></li>
                    <li><a href="/">Pengaturan</a></li>
                </ul>
        </aside>
    </div class="container">
</body>
</html>