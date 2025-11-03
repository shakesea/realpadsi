<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pegawai.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@300;400;600;700&display=swap" rel="stylesheet">
    <title>@yield('title', 'NutaPOS')</title>
</head>

<body>
    <div class="container">

<!-- Sidebar -->
    <aside class="sidebar">
        <div class="logo-wrap">
            <img src="{{ asset('img/NutaPOS_Logo.png') }}" class="logo-icon">
            <span class="logo">nutapos</span>
        </div>

        <ul class="menu">
            <li class="{{ request()->is('dashboard') ? 'active' : '' }}">
                <a href="/dashboard">
                    <i class="icon fas fa-th-large"></i>
                    Dashboard
                </a>
            </li>
            <li class="{{ request()->is('pegawai') ? 'active' : '' }}">
                <a href="/pegawai">
                    <i class="icon fas fa-id-badge"></i>
                    Pegawai
                </a>
            </li>
            <li class="{{ request()->is('kasir') ? 'active' : '' }}">
                <a href="/kasir">
                    <i class="icon fas fa-lock"></i>
                    Kasir
                </a>
            </li>
            <li class="{{ request()->is('stok') ? 'active' : '' }}">
                <a href="/stok">
                    <i class="icon fas fa-boxes"></i>
                    Stok
                </a>
            </li>
            <li class="{{ request()->is('penjualan') ? 'active' : '' }}">
                <a href="/penjualan">
                    <i class="icon fas fa-receipt"></i>
                    Riwayat Penjualan
                </a>
            </li>
            <li class="{{ request()->is('member') ? 'active' : '' }}">
                <a href="/member">
                    <i class="icon fas fa-user"></i>
                    Member
                </a>
            </li>
            <li class="{{ request()->is('tutup-outlet') ? 'active' : '' }}">
                <a href="/">
                    <i class="icon fas fa-store"></i>
                    Tutup Outlet
                </a>
            </li>
            <li class="{{ request()->is('pengaturan') ? 'active' : '' }}">
                <a href="/">
                    <i class="icon fas fa-cog"></i>
                    Pengaturan
                </a>
            </li>
        </ul>
    </aside>

        <!-- Status Bar Atas -->
        <main class="main">
            <header class="topbar">
        <div class="left-section">
            <div class="menu-icon">☰</div>
            <span class="status">
            <span class="status-dot"></span>
            <span class="status-text">Online</span>
            </span>
        </div>

        <div class="right-section"> <!-- Profile  -->
            <button class="icon">📄</button>
            <div class="profile">
            <div class="avatar">P</div> 
            <div class="profile-info">
                <span class="name">PT Cunyau Terbang</span>
                <span class="role">Manager</span>
            </div>
            <div class="dropdown">
                <button>▾</button>
                <div class="content">  
                    <a href="/public/login">Logout</a>
                </div>
            </div>
        </div>
        </header>
            @yield('content')
        </main>
    </div>
</body>
</html>