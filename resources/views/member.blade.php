@extends('layouts.main')
@section('title', 'NutaPOS - Member')

@section('content')

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

<div class="member-container">

  <!-- 🔍 Form Search -->
  <form method="GET" action="{{ route('member.index') }}" class="search-box" id="searchForm">
    <input type="text" name="q" placeholder="Cari nama / email..." value="{{ request('q') }}" id="searchInput">
    <button type="submit" id="searchButton">Search</button>
  </form>

  <!-- 🔄 Loader -->
  <div id="loadingIndicator" class="loading" style="display:none;">
    🔄 Memuat data...
  </div>

  <!-- ⚠️ Pesan Jika Kosong -->
  @if($members->isEmpty())
  <p class="no-result">⚠️ Tidak ada member yang cocok dengan pencarian.</p>
  @endif
  <p id="noResultDynamic" class="no-result" style="display:none;">⚠️ Tidak ada member yang cocok dengan pencarian.</p>

  <!-- 🧩 Grid Member -->
  <div class="member-grid">
    @foreach($members as $m)
    <div class="member-card green-card" data-nama="{{ $m['nama'] }}" data-email="{{ $m['email'] }}" onclick="openDeleteModal('{{ $m['id'] }}', '{{ $m['nama'] }}', '{{ $m['email'] }}')">
      <div class="member-info">
        <h3>{{ $m['nama'] }}</h3>
        <p>{{ \Carbon\Carbon::parse($m['tanggal'])->format('d/m/Y') }}</p>
        <p>{{ $m['email'] }}</p>
      </div>

      <div class="member-footer">
        <p><strong>Total Points : {{ $m['poin'] }}</strong></p>
        <img src="{{ asset('img/nutapos_logo.png') }}" alt="NutaPOS Logo" class="member-badge">
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

<!-- ======================= -->
<!-- 💡 SCRIPT -->
<!-- ======================= -->
<script>
  // Auto-dismiss flash notifications (mirip halaman lain)
  document.addEventListener('DOMContentLoaded', () => {
    const flashes = document.querySelectorAll('.flash-success, .flash-error, .flash-alert');
    flashes.forEach(msg => {
      setTimeout(() => {
        msg.style.transition = 'opacity 0.6s';
        msg.style.opacity = '0';
        setTimeout(() => msg.remove(), 700);
      }, 3000);
    });
  });

  function openAddModal() {
    document.getElementById('addMemberModal').style.display = 'flex';
  }

  function openDeleteModal(id) {
    document.getElementById('deleteMemberModal').style.display = 'flex';
    const form = document.getElementById('deleteMemberForm');
    form.action = `/member/${id}`;
  }

  function closeModal() {
    document.getElementById('addMemberModal').style.display = 'none';
    document.getElementById('deleteMemberModal').style.display = 'none';
  }

  // ======== Live filter member (nama/email) seperti kasir/pegawai/stok ========
  window.filterMembers = function() {
    const keyword = (document.getElementById('searchInput').value || '').toLowerCase().trim();
    const cards = document.querySelectorAll('.member-card.green-card');
    let visible = 0;

    cards.forEach(card => {
      const nama = (card.dataset.nama || '').toLowerCase();
      const email = (card.dataset.email || '').toLowerCase();
      const match = !keyword || nama.includes(keyword) || email.includes(keyword);
      card.style.display = match ? 'block' : 'none';
      if (match) visible++;
    });

    // Pastikan kartu tambah tetap terlihat
    const addCard = document.querySelector('.member-card.add-card');
    if (addCard) addCard.style.display = 'flex';

    // Tampilkan pesan jika kosong
    const emptyMsg = document.getElementById('noResultDynamic');
    if (emptyMsg) emptyMsg.style.display = visible === 0 ? 'block' : 'none';
  }

  // Trigger filter saat mengetik (tanpa submit)
  document.getElementById('searchInput').addEventListener('input', function() {
    filterMembers();
  });

  // Intersep submit: gunakan filter client-side
  document.getElementById('searchForm').addEventListener('submit', function(e) {
    e.preventDefault();
    filterMembers();
  });

  // Terapkan filter awal jika ada nilai dari request('q')
  document.addEventListener('DOMContentLoaded', function() {
    const val = (document.getElementById('searchInput').value || '').trim();
    if (val) filterMembers();
  });
</script>

<link rel="stylesheet" href="{{ asset('css/member.css') }}">

@endsection