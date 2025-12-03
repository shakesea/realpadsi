@extends('layouts.main')

@section('title', 'NutaPOS - Pegawai')

@section('content')
@if ($errors->any())
<div class="flash-error">
    @foreach ($errors->all() as $error)
        <p>{{ $error }}</p>
    @endforeach
</div>
@endif

@if (session('success'))
<div class="flash-success">
    {{ session('success') }}
</div>
@endif


<div class="pegawai-container">
    <div class="pegawai-card">
        <div class="pilih-pelayan">
            <h1 class="pegawai-title">Pilih Pelayan</h1>

            <!-- Form pencarian -->
            <form method="GET" action="{{ route('pegawai.index') }}" class="pegawai-search">
                <svg xmlns="http://www.w3.org/2000/svg" class="search-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1116.65 6.65a7.5 7.5 0 010 10.6z" />
                </svg>
                <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Cari Pelayan...">
            </form>
        </div>    

        <!-- Form Pegawai -->
        <div class="pegawai-list">
            @forelse ($pegawai as $p)
            <div class="pegawai-item">
                <div class="pegawai-left">
                    <div class="pegawai-avatar">{{ strtoupper(substr($p->Username, 0, 2)) }}</div>
                    <span class="pegawai-name">{{ $p->Username }}</span>
                    <span class="pegawai-role">({{ $p->ID_Role }})</span>
                </div>

                <div class="pegawai-actions">
                    <!-- Tombol delete -->
                    <form method="POST" action="{{ route('pegawai.destroy', $p->ID) }}" onsubmit="return confirm('Hapus {{ $p->Username }}?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="pegawai-btn delete" title="Hapus">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12M9 7V5a1 1 0 011-1h4a1 1 0 011 1v2m1 0v12a2 2 0 01-2 2H8a2 2 0 01-2-2V7z"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <p style="text-align:center;">Tidak ada pegawai ditemukan.</p>
            @endforelse
        </div>

        <a href="{{ route('pegawai.create') }}" class="pegawai-add">+ Buat Baru</a>
    </div>
</div>
@endsection
