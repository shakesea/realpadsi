@extends('layouts.main')

@section('title', 'Tambah Pelayan Baru')

@section('content')

{{-- Flash Message --}}
@if(session('error'))
<div class="flash-error">
    {{ session('error') }}
</div>
@endif

@if(session('success'))
<div class="flash-success">
    {{ session('success') }}
</div>
@endif

<div class="form-container">
    <div class="form-card">
        <h1 class="form-title">Tambah Pelayan Baru</h1>

        <form action="{{ route('pegawai.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="nama">Nama</label>
                <input type="text" id="nama" name="nama" value="{{ old('nama') }}" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required>
            </div>

            <div class="form-group">
                <label for="telp">No Handphone / Whatsapp</label>
                <input type="text" id="telp" name="telp" value="{{ old('telp') }}" required>
            </div>

            <div class="form-group">
                <label for="tanggal_lahir">Tanggal Lahir</label>
                <input type="date" id="tanggal_lahir" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}" required>
            </div>

            <div class="form-group">
                <label for="alamat">Alamat</label>
                <input id="alamat" name="alamat" value="{{ old('alamat') }}" required>
            </div>

            <div class="form-footer">
                <a href="{{ route('pegawai.index') }}" class="link-daftar">Lihat Daftar</a>
                <button type="submit" class="btn-simpan">Simpan</button>
            </div>
        </form>
    </div>
</div>

@endsection
