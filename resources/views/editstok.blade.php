@extends('layouts.main')

@section('title', 'Edit Stok')

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
        <h1 class="form-title">Edit Stok</h1>

        {{-- Pastikan parameter id dikirim --}}
        <form action="{{ route('stok.update', $stokItem->ID_Barang) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- Nama Item --}}
            <div class="form-group">
                <label for="nama">Nama Item</label>
                <input type="text" id="nama" name="nama" value="{{ $stokItem->Nama }}" required>
            </div>

            {{-- Jumlah Item --}}
            <div class="form-group">
                <label for="jumlah">Jumlah Item</label>
                <input type="number" id="jumlah" name="jumlah" value="{{ $stokItem->Jumlah_Item }}" required>
            </div>

            {{-- Kategori (mengganti posisi Satuan sebelumnya) --}}
            <div class="form-group">
                <label for="kategori">Kategori</label>
                <input type="text" id="kategori" name="kategori" value="{{ $stokItem->Kategori }}" required>
            </div>

            <div class="form-footer">
                <a href="{{ route('stok.index') }}" class="link-daftar">Kembali</a>
                <button type="submit" class="btn-simpan">Perbarui</button>
            </div>
        </form>
    </div>
</div>
@endsection
