@extends('layouts.main')

@section('title', 'Tambah Pelayan Baru')

@section('content')
<div class="form-container">
    <div class="form-card">
        <h1 class="form-title">Tambah Pelayan Baru</h1>

        <form action="{{ route('pegawai.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="nama">Nama</label>
                <input type="text" id="nama" name="nama" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="telp">No Handphone / Whatsapp</label>
                <input type="text" id="telp" name="telp" required>
            </div>

            <div class="form-group">
                <label for="tanggal_lahir">Tanggal Lahir</label>
                <input type="date" id="tanggal_lahir" name="tanggal_lahir" required>
            </div>

            <div class="form-group">
                <label for="alamat">Alamat</label>
                <input id="alamat" name="alamat" rows="3" required>
            </div>

            <div class="form-footer">
                <a href="{{ route('pegawai.index') }}" class="link-daftar">Lihat Daftar</a>
                <button type="submit" class="btn-simpan">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
