<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pegawai.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/kasir.css') }}">
    <link rel="stylesheet" href="{{ asset('css/stok.css') }}">
    <link rel="stylesheet" href="{{ asset('css/member.css') }}">
    <link rel="stylesheet" href="{{ asset('css/laporan.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@300;400;600;700&display=swap" rel="stylesheet">

    <title>@yield('title', 'NutaPOS')</title>

    <style>
        /* ========================= TOPBAR FIXED ========================= */
        .topbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 65px;
            background: white;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        /* ========================= SIDEBAR ========================= */
        .sidebar {
            position: fixed;
            top: 65px;
            left: 0;
            width: 260px;
            height: calc(100vh - 65px);
            background: white;
            transition: transform .3s ease;
            z-index: 9990;
        }
        /* Saat sidebar collapse: MAIN FULL LEBAR */
        body.sidebar-hidden .sidebar {
            transform: translateX(-260px);
        }

        body.sidebar-hidden .main {
            margin-left: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
            padding-left: 20px !important;
            padding-right: 20px !important;
        }

        /* ========================= OVERRIDE “CONTAINER” BAWAAN ========================= */
        body.sidebar-hidden .main .container,
        body.sidebar-hidden .main .content,
        body.sidebar-hidden .main .wrapper,
        body.sidebar-hidden .main .dashboard-container,
        body.sidebar-hidden .main .dashboard-wrapper {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        /* Untuk setiap CARD supaya tetap rapi full width */
        body.sidebar-hidden .main .card,
        body.sidebar-hidden .main .card-box,
        body.sidebar-hidden .main .panel {
            max-width: 100% !important;
        }

        /* Hilangkan margin horizontal global */
        body.sidebar-hidden {
            padding: 0 !important;
        }

        /* ========================= PROFILE ========================= */
        .profile {
            position: relative;
            display: flex;
            align-items: center;
            cursor: pointer;
            gap: 10px;
        }
        .avatar {
            width: 40px;
            height: 40px;
            background: #007bff;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-weight: bold;
        }
        .profile-dropdown {
            display: none;
            position: absolute;
            top: 55px;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-radius: 10px;
            min-width: 150px;
            z-index: 10000;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .profile-dropdown button {
            width: 100%;
            padding: 10px 15px;
            background: none;
            border: none;
            text-align: left;
        }
        .profile-dropdown button:hover {
            background: #f5f5f5;
        }

    </style>
</head>

<body>

<!-- ===================== TOPBAR ===================== -->
<header class="topbar">
    <div class="left-section">
        <div class="menu-icon">☰</div>
        <span class="status">
            <span class="status-dot"></span>
            <span class="status-text">Online</span>
        </span>
    </div>

    <div class="right-section">
        <button class="icon">📄</button>

        <div class="profile" id="profileToggle">
            @php
                $username = Session::get('user.username', 'User');
                $userType = Session::get('user.type', 'Guest');
                $initial = strtoupper(substr($username, 0, 1));
                $displayName = ucfirst(explode('.', $username)[0]);
            @endphp

            <div class="avatar">{{ $initial }}</div>
            <div class="profile-info">
                <span class="name">{{ $displayName }}</span>
                <span class="role">{{ $userType }}</span>
            </div>

            <div class="profile-dropdown" id="profileDropdown">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit"><i class="fas fa-sign-out-alt"></i> Logout</button>
                </form>
            </div>
        </div>
    </div>
</header>

<!-- ===================== SIDEBAR ===================== -->
<aside class="sidebar">
    <div class="logo-wrap">
        <img src="{{ asset('img/nutapos_logo.png') }}" class="logo-icon">
        <span class="logo">nutapos</span>
    </div>

    <ul class="menu">

        <li class="{{ request()->is('dashboard') ? 'active' : '' }}">
            <a href="/dashboard"><i class="icon fas fa-th-large"></i>Dashboard</a>
        </li>

        @if(session('user.role') === 'manager')
        <li class="{{ request()->is('pegawai') ? 'active' : '' }}">
            <a href="/pegawai"><i class="icon fas fa-id-badge"></i>Pegawai</a>
        </li>
        @endif

        @if(session('user.role') !== 'finance')
        <li class="{{ request()->is('kasir') ? 'active' : '' }}">
            <a href="/kasir"><i class="icon fas fa-lock"></i>Kasir</a>
        </li>
        @endif

        @if(session('user.role') !== 'finance')
        <li class="{{ request()->is('stok') ? 'active' : '' }}">
            <a href="/stok"><i class="icon fas fa-boxes"></i>Stok</a>
        </li>
        @endif

        @if(session('user.role') !== 'pegawai')
        <li class="{{ request()->is('penjualan') ? 'active' : '' }}">
            <a href="/penjualan"><i class="icon fas fa-receipt"></i>Riwayat Penjualan</a>
        </li>
        @endif

        @if(session('user.role') !== 'finance')
        <li class="{{ request()->is('member') ? 'active' : '' }}">
            <a href="/member"><i class="icon fas fa-user"></i>Member</a>
        </li>
        @endif

        <li><a href="/"><i class="icon fas fa-store"></i>Tutup Outlet</a></li>
        <li><a href="/"><i class="icon fas fa-cog"></i>Pengaturan</a></li>

    </ul>
</aside>

<!-- ===================== MAIN CONTENT ===================== -->
<main class="main">
@yield('content')
</main>

<!-- ================= SCRIPT ================= -->
<script>
    // Dropdown profil
    document.getElementById('profileToggle').addEventListener('click', function(e) {
        e.stopPropagation();
        const dropdown = document.getElementById('profileDropdown');
        dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
    });

    document.addEventListener('click', () => {
        document.getElementById('profileDropdown').style.display = 'none';
    });

    // Sidebar collapse toggle
    document.querySelector('.menu-icon').addEventListener('click', () => {
        document.body.classList.toggle('sidebar-hidden');
    });
</script>

</body>
</html>
