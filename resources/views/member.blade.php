@extends('layouts.main')
@section('title','NutaPOS - Member')

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
    <form id="addMemberForm" onsubmit="return addMember()">
      <input type="text" placeholder="Nama Pelanggan" required>
      <input type="text" placeholder="Nomor HP" required>
      <input type="email" placeholder="Email" required>
      <input type="text" placeholder="Alamat">
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
    <form id="deleteMemberForm" onsubmit="return deleteMember()">
      <input type="text" id="deleteName" readonly>
      <input type="text" id="deletePhone" readonly>
      <input type="email" id="deleteEmail" readonly>
      <input type="text" id="deleteAddress" readonly>

      <p><strong>Member Sejak:</strong> <span id="memberSince"></span></p>
      <p><strong>Tanggal Sekarang:</strong> <span id="currentDate"></span></p>

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
  const now = new Date();
  const today = `${now.getDate()}/${now.getMonth()+1}/${now.getFullYear()}`;
  const memberSince = '10/5/2025'; 

  document.getElementById('deleteMemberModal').style.display = 'flex';
  document.getElementById('deleteName').value = name;
  document.getElementById('deletePhone').value = '08675672351822'; 
  document.getElementById('deleteEmail').value = email;
  document.getElementById('deleteAddress').value = 'Jl. Porwonegoro No.40';

  document.getElementById('memberSince').innerText = memberSince;
  document.getElementById('currentDate').innerText = today;
}

function closeModal() {
  document.getElementById('addMemberModal').style.display = 'none';
  document.getElementById('deleteMemberModal').style.display = 'none';
}

// Dummy actions
function addMember() {
  alert('Member baru berhasil dibuat! (Dummy Mode)');
  closeModal();
  return false;
}

function deleteMember() {
  alert('Member berhasil dihapus! (Dummy Mode)');
  closeModal();
  return false;
}
</script>
@endsection
