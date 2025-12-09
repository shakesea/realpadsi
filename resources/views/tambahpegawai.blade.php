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

<div class="pegawai-container">
    <div class="pegawai-card">
        <h1 class="pegawai-title">Tambah Pelayan Baru</h1>

        <form action="{{ route('pegawai.store') }}" method="POST">
            @csrf

            {{-- Nama --}}
            <div class="form-group">
                <label for="nama">Nama</label>
                <input type="text" id="nama" name="nama" value="{{ old('nama') }}" required
                       class="@error('nama') input-error @enderror">
                @error('nama')
                    <span class="text-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- Email --}}
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required
                       class="@error('email') input-error @enderror">
                @error('email')
                    <span class="text-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- No Handphone / Whatsapp --}}
            <div class="form-group">
                <label for="telp">No Handphone / Whatsapp</label>
                <input type="text" id="telp" name="telp" value="{{ old('telp') }}" required
                       class="@error('telp') input-error @enderror">
                @error('telp')
                    <span class="text-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- Tanggal Lahir --}}
            <div class="form-group">
                <label for="tanggal_lahir">Tanggal Lahir</label>
                <input type="date" id="tanggal_lahir" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}" required
                       class="@error('tanggal_lahir') input-error @enderror">
                @error('tanggal_lahir')
                    <span class="text-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- Alamat --}}
            <div class="form-group">
                <label for="alamat">Alamat</label>
                <input id="alamat" name="alamat" value="{{ old('alamat') }}" required
                       class="@error('alamat') input-error @enderror">
                @error('alamat')
                    <span class="text-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- Footer --}}
            <div class="form-footer">
                <a href="{{ route('pegawai.index') }}" class="pegawai-add">Lihat Daftar</a>
                <button type="submit" class="btn-simpan">Simpan</button>
            </div>
        </form>
    </div>
</div>

@endsection
