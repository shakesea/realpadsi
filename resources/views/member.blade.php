@extends('layouts.main')
@section('title', 'NutaPOS - Member')

@section('content')
<div class="member-container">
  <div class="member-grid">
    @foreach($members as $m)
      <div class="member-card green-card" onclick="openDeleteModal('{{ $m['id'] }}', '{{ $m['nama'] }}', '{{ $m['email'] }}')">
        <div class="member-info">
          <h3>{{ $m['nama'] }}</h3>
          <p>{{ \Carbon\Carbon::parse($m['tanggal'])->format('d/m/Y') }}</p>
          <p>{{ $m['email'] }}</p>
        </div>
        <div class="member-footer">
          <p><strong>Total Points : {{ $m['poin'] }}</strong></p>
          <img src="{{ asset('img/NutaPOS_Logo.png') }}" alt="logo" class="member-badge">
        </div>
      </div>
    @endforeach

    <!-- Kartu Tambah (+) -->
    <div class="member-card add-card" onclick="openAddModal()">
      <span class="plus">+</span>
    </div>
  </div>
</div>

<!-- 🟢 Modal Tambah Member -->
<div id="addMemberModal" class="modal">
  <div class="modal-content">
    <h2>Tambah Member</h2>
    <form method="POST" action="{{ route('member.store') }}">
    @csrf
        <input type="text" name="nama" placeholder="Nama Pelanggan" required>
        <input type="text" name="no_telp" placeholder="Nomor HP" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="alamat" placeholder="Alamat">
        <div class="modal-buttons">
        <button type="button" class="btn-cancel" onclick="closeModal()">Kembali</button>
        <button type="submit" class="btn-green">Buat Baru</button>
    </div>
    </form>

  </div>
</div>

<!-- 🔴 Modal Hapus Member -->
<div id="deleteMemberModal" class="modal">
  <div class="modal-content">
    <h2 class="delete-title">Hapus Member</h2>
    <form id="deleteMemberForm" method="POST">
      @csrf
      @method('DELETE')
      <p><strong>Apakah Anda yakin ingin menghapus member ini?</strong></p>
      <div class="modal-buttons">
        <button type="button" class="btn-cancel" onclick="closeModal()">Kembali</button>
        <button type="submit" class="btn-red">Hapus</button>
      </div>
    </form>
  </div>
</div>

<script>
function openAddModal() {
  document.getElementById('addMemberModal').style.display = 'flex';
}

function openDeleteModal(id, name, email) {
  document.getElementById('deleteMemberModal').style.display = 'flex';
  const form = document.getElementById('deleteMemberForm');
  form.action = `/member/${id}`;
}

function closeModal() {
  document.getElementById('addMemberModal').style.display = 'none';
  document.getElementById('deleteMemberModal').style.display = 'none';
}
</script>
@endsection
