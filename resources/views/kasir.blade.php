@extends('layouts.main')

@section('title', 'NutaPOS - Kasir')

@section('content')
<div class="menu-container">
    <div class="menu-layout">

        <!-- Kolom kiri: Pelanggan -->
        <div class="menu-left">
            <h3 class="pelanggan-title">Pelanggan</h3>
            <div class="pelanggan-list">
                <!-- nanti bisa isi pelanggan -->
            </div>
            <div class="total-section">
                <div>Total</div>
                <div class="harga-total">Rp 0</div>
            </div>
        </div>

        <!-- Kolom kanan: Produk -->
        <div class="menu-right">
            <div class="menu-search">
                <input type="text" placeholder="Cari Produk" />
                <button class="dropdown-btn">⌄</button>
            </div>

            <div class="menu-filter">
                <button class="filter-btn active">Coffee (15)</button>
                <button class="filter-btn">Makanan (23)</button>
                <button class="filter-btn">Snack (3)</button>
                <button class="filter-btn">Dessert (2)</button>
                <button class="filter-btn">Alacarte (20)</button>
            </div>

            <div class="produk-grid">
                {{-- Produk contoh --}}
                @for ($i = 0; $i < 11; $i++)
                <div class="produk-card" onclick="openModal('modalEdit')">
                    <img src="{{ asset('img/sample-product.png') }}" alt="produk">
                    <div class="produk-name">Mata Kodok</div>
                    <div class="produk-price">Rp 50.000</div>
                </div>
                @endfor

                <!-- Card tambah -->
                <div class="produk-card add-card" onclick="openModal('modalTambah')">
                    <span>+</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============== MODAL TAMBAH PRODUK ============== -->
<div id="modalTambah" class="modal-overlay">
  <div class="modal-card">
    <h2 class="modal-title">Tambah Produk Baru</h2>
    <form method="POST" action="{{ route('kasir.store') }}" enctype="multipart/form-data">
      @csrf
      <div class="modal-body">
        <div class="form-left">
          <div class="foto-box">Foto</div>
        </div>
        <div class="form-right">
          <div class="form-group">
            <label>Nama Produk:</label>
            <input type="text" name="nama" required>
          </div>
          <div class="form-group">
            <label>Harga:</label>
            <input type="text" name="harga" required>
          </div>
          <div class="form-group">
            <label>Kategori:</label>
            <input type="text" name="kategori" required>
          </div>
          <div class="form-group">
            <label>Deskripsi:</label>
            <input type="text" name="deskripsi">
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <a href="#" class="modal-cancel" onclick="closeModal('modalTambah')">Kembali</a>
        <button type="submit" class="btn-green">Tambah</button>
      </div>
    </form>
  </div>
</div>

<!-- ============== MODAL EDIT PRODUK ============== -->
<div id="modalEdit" class="modal-overlay">
  <div class="modal-card">
    <div class="modal-body">
      <div class="form-left">
        <img src="{{ asset('img/sample-product.png') }}" class="modal-img">
      </div>
      <div class="form-right">
        <h2 class="modal-item-name">Nama : Mata Kodok</h2>
        <p class="modal-item-price">Harga : Rp 50.000</p>
        <p class="modal-item-category">Kategori : Coffee (Hot)</p>
        <div class="form-group">
          <label>Deskripsi :</label>
          <p>Dibuat dengan mata kodok segar dari gunung Himalaya</p>
        </div>
        <div class="form-group">
          <label>Bahan Penyusun :</label>
          <input type="text" placeholder="Item ini memiliki bahan">
        </div>
      </div>
    </div>

    <div class="modal-footer">
      <a href="#" class="modal-cancel" onclick="closeModal('modalEdit')">Kembali</a>
      <button class="btn-blue">Edit</button>
      <button class="btn-red">Hapus</button>
    </div>
  </div>
</div>

{{-- SCRIPT --}}
<script>
function openModal(id) {
    document.getElementById(id).style.display = 'flex';
}
function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}
</script>
@endsection
