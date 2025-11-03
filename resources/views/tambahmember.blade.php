@extends('layouts.main')
@section('title','Tambah Member Baru')

@section('content')
<div class="form-container">
  <div class="form-card">
    <h1 class="form-title">Tambah Member Baru</h1>

    <form action="{{ route('member.store') }}" method="POST">
      @csrf
      <div class="form-group">
        <label>Nama</label>
        <input type="text" name="nama" required>
      </div>
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" required>
      </div>
      <div class="form-group">
        <label>Tanggal Gabung</label>
        <input type="date" name="tanggal" required>
      </div>
      <div class="form-group">
        <label>Poin</label>
        <input type="number" name="poin" value="0">
      </div>

      <div class="form-footer">
        <a href="{{ route('member.index') }}" class="link-daftar">Kembali</a>
        <button type="submit" class="btn-simpan">Simpan</button>
      </div>
    </form>
  </div>
</div>
@endsection
