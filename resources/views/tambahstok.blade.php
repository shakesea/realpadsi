@extends('layouts.main')

@section('title', 'Tambah Stok Baru')

@section('content')
<div class="form-container">
    <div class="form-card">
        <h1 class="form-title">Tambah Stok Baru</h1>

        <form action="#" method="POST">
            @csrf
            <div class="form-group">
                <label for="nama">Nama Item</label>
                <input type="text" id="nama" name="nama" placeholder="Masukkan nama item..." required>
            </div>

            <div class="form-group">
                <label for="jumlah">Jumlah</label>
                <input type="number" id="jumlah" name="jumlah" placeholder="Masukkan jumlah..." required>
            </div>

            <div class="form-group">
                <label for="satuan">Satuan</label>
                <input type="text" id="satuan" name="satuan" placeholder="Misal: PCS, Liter..." required>
            </div>

            <div class="form-footer">
                <a href="{{ route('stok.index') }}" class="link-daftar">Kembali</a>
                <button type="submit" class="btn-simpan">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
