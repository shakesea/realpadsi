@extends('layouts.main')

@section('title', 'Edit Stok')

@section('content')
<div class="form-container">
    <div class="form-card">
        <h1 class="form-title">Edit Stok</h1>

        <form action="{{ route('stok.update', $stokItem['id']) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="nama">Nama Item</label>
                <input type="text" id="nama" name="nama" value="{{ $stokItem['nama'] }}" required>
            </div>

            <div class="form-group">
                <label for="jumlah">Jumlah</label>
                <input type="number" id="jumlah" name="jumlah" value="{{ $stokItem['jumlah'] }}" required>
            </div>

            <div class="form-group">
                <label for="satuan">Satuan</label>
                <input type="text" id="satuan" name="satuan" value="{{ $stokItem['satuan'] }}" required>
            </div>

            <div class="form-footer">
                <a href="{{ route('stok.index') }}" class="link-daftar">Kembali</a>
                <button type="submit" class="btn-simpan">Perbarui</button>
            </div>
        </form>
    </div>
</div>
@endsection
