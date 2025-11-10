@extends('layouts.main')

@section('title', 'Tambah Stok Baru')

@section('content')
{{-- Flash Error --}}
@if(session('error'))
<div class="flash-alert flash-error">
    {{ session('error') }}
</div>
@endif

@if(session('success'))
<div class="flash-alert flash-success">
    {{ session('success') }}
</div>
@endif


<div class="form-container">
    <div class="form-card">
        <h1 class="form-title">Tambah Stok Baru</h1>

        <form action="{{ route('stok.store') }}" method="POST">
            @csrf

            {{-- Nama Item --}}
            <div class="form-group">
                <label for="nama">Nama Item</label>
                <input type="text" id="nama" name="nama" placeholder="Masukkan nama item..." required>
            </div>

            {{-- Jumlah Item --}}
            <div class="form-group">
                <label for="jumlah">Jumlah</label>
                <input type="number" id="jumlah" name="jumlah" placeholder="Masukkan jumlah..." required>
            </div>

            {{-- Kategori (menggantikan Satuan) --}}
            <div class="form-group">
                <label for="kategori">Kategori</label>
                <input type="text" id="kategori" name="kategori" placeholder="Misal: Bahan, Minuman, Makanan..." required>
            </div>

            <div class="form-footer">
                <a href="{{ route('stok.index') }}" class="link-daftar">Kembali</a>
                <button type="submit" class="btn-simpan">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
