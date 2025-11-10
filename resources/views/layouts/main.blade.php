<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pegawai.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/kasir.css') }}">
    <link rel="stylesheet" href="{{ asset('css/stok.css') }}">
    <link rel="stylesheet" href="{{ asset('css/member.css') }}">
    <link rel="stylesheet" href="{{ asset('css/stok.css') }}">
    <link rel="stylesheet" href="{{ asset('css/laporan.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@300;400;600;700&display=swap" rel="stylesheet">
    <title>@yield('title', 'NutaPOS')</title>
    <style>
        /* Tambahan untuk dropdown */
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
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }
        .profile-info {
            display: flex;
            flex-direction: column;
            text-align: left;
        }
        .profile-dropdown {
            display: none;
            position: absolute;
            top: 55px;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            min-width: 150px;
            z-index: 100;
        }
        .profile-dropdown a, .profile-dropdown button {
            display: block;
            width: 100%;
            padding: 10px 15px;
            text-align: left;
            background: none;
            border: none;
            color: #333;
            font-size: 14px;
            cursor: pointer;
        }
        .profile-dropdown a:hover,
        .profile-dropdown button:hover {
            background: #f5f5f5;
        }
    </style>
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
{{-- DEBUG --}}

    {{-- Dashboard: semua bisa --}}
    <li class="{{ request()->is('dashboard') ? 'active' : '' }}">
        <a href="/dashboard"><i class="icon fas fa-th-large"></i>Dashboard</a>
    </li>

    {{-- Pegawai: hanya untuk manager --}}
    @if(session('user.role') === 'manager')
    <li class="{{ request()->is('pegawai') ? 'active' : '' }}">
        <a href="/pegawai"><i class="icon fas fa-id-badge"></i>Pegawai</a>
    </li>
    @endif

    {{-- Kasir: semua kecuali finance --}}
    @if(session('user.role') !== 'finance')
    <li class="{{ request()->is('kasir') ? 'active' : '' }}">
        <a href="/kasir"><i class="icon fas fa-lock"></i>Kasir</a>
    </li>
    @endif

    {{-- Stok: semua kecuali finance --}}
    @if(session('user.role') !== 'finance')
    <li class="{{ request()->is('stok') ? 'active' : '' }}">
        <a href="/stok"><i class="icon fas fa-boxes"></i>Stok</a>
    </li>
    @endif

    {{-- Riwayat Penjualan: semua kecuali pegawai --}}
    @if(session('user.role') !== 'pegawai')
    <li class="{{ request()->is('penjualan') ? 'active' : '' }}">
        <a href="/penjualan"><i class="icon fas fa-receipt"></i>Riwayat Penjualan</a>
    </li>
    @endif

    {{-- Member: semua kecuali finance --}}
    @if(session('user.role') !== 'finance')
    <li class="{{ request()->is('member') ? 'active' : '' }}">
        <a href="/member"><i class="icon fas fa-user"></i>Member</a>
    </li>
    @endif

    {{-- Tutup Outlet & Pengaturan: semua bisa --}}
    <li>
        <a href="/"><i class="icon fas fa-store"></i>Tutup Outlet</a></li>
    <li>
        <a href="/"><i class="icon fas fa-cog"></i>Pengaturan</a></li>
</ul>
</aside>

<!-- Main content -->
<main class="main">
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
                
                // Ambil inisial dari username (huruf pertama)
                $initial = strtoupper(substr($username, 0, 1));
                
                // Format nama untuk display (capitalize dan hapus setelah titik jika ada)
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
                    <button type="submit">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>

    @yield('content')
</main>

</div>

<script>
    // Toggle dropdown saat klik profil
    const profileToggle = document.getElementById('profileToggle');
    const dropdown = document.getElementById('profileDropdown');

    profileToggle.addEventListener('click', (e) => {
        e.stopPropagation();
        dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
    });

    // Tutup dropdown jika klik di luar area
    document.addEventListener('click', () => {
        dropdown.style.display = 'none';
    });
</script>
<script>
  // Toggle dropdown profil (kode asli kamu – dipertahankan)
  const profileToggle = document.getElementById('profileToggle');
  const dropdown = document.getElementById('profileDropdown');

  profileToggle.addEventListener('click', (e) => {
      e.stopPropagation();
      dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
  });
  document.addEventListener('click', () => {
      dropdown.style.display = 'none';
  });

  // === Tambahan: Toggle hamburger (garis 3) untuk collapse sidebar (opsional) ===
  document.querySelector('.menu-icon')?.addEventListener('click', () => {
    document.body.classList.toggle('sidebar-collapsed');
  });

  // === (Opsional) Set status online/offline (hanya visual) ===
  const isOnline = true; // ganti ke false kalau mau lihat tampilan offline
  const statusEl = document.querySelector('.status');
  const statusText = document.querySelector('.status-text');
  if (statusEl && statusText) {
    if (isOnline) {
      statusEl.classList.remove('offline');
      statusText.textContent = 'Online';
    } else {
      statusEl.classList.add('offline');
      statusText.textContent = 'Offline';
    }
  }
</script>

</body>
</html>