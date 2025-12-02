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
        </ul>
    </aside>
</header>



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
