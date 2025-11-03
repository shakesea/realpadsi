@extends('layouts.main')

@section('title', 'NutaPOS - Pegawai')

@section('content')


<div class="pegawai-container">
    <div class="pegawai-card">
        <h1 class="pegawai-title">Pilih Pelayan</h1>

        <div class="pegawai-search">
            <svg xmlns="http://www.w3.org/2000/svg" class="search-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1116.65 6.65a7.5 7.5 0 010 10.6z" />
            </svg>
            <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Cari Pelayan..." />
        </div>

        <p class="pegawai-sub">Tanpa Pelayan</p>

        <div class="pegawai-list">
            @foreach ($pegawai as $p)
            <div class="pegawai-item">
                <div class="pegawai-left">
                    <div class="pegawai-avatar">{{ strtoupper(substr($p['nama'], 0, 2)) }}</div>
                    <span class="pegawai-name">{{ $p['nama'] }}</span>
                </div>

                <div class="pegawai-actions">
                    <a href="mailto:{{ $p['email'] ?? '' }}" class="pegawai-btn email">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7l9 6 9-6M4 6h16a1 1 0 011 1v10a1 1 0 01-1 1H4a1 1 0 01-1-1V7z"/>
                        </svg>
                    </a>
                    <a href="tel:{{ $p['telp'] ?? '' }}" class="pegawai-btn telp">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h1.3a1 1 0 01.95.68l1.1 3.3a1 1 0 01-.27 1.04l-1.2 1.2a15 15 0 006.4 6.4l1.2-1.2a1 1 0 011.04-.27l3.3 1.1a1 1 0 01.68.95V19a2 2 0 01-2 2h-1C9.82 21 3 14.18 3 6V5z"/>
                        </svg>
                    </a>
                    <form method="POST" action="{{ route('pegawai.destroy', $p['id']) }}" onsubmit="return confirm('Hapus {{ $p['nama'] }}?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="pegawai-btn delete">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12M9 7V5a1 1 0 011-1h4a1 1 0 011 1v2m1 0v12a2 2 0 01-2 2H8a2 2 0 01-2-2V7z"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Tombol Buat Baru -->
        <a href="#" class="pegawai-add">+ Buat Baru</a>
    </div>
</div>
@endsection
