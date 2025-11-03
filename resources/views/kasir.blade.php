@extends('layouts.main')

@section('title', 'NutaPOS - Kasir')

@section('content')
<div class="menu-container">
  <div class="menu-layout">

    <!-- Sidebar pelanggan kiri -->
    <div class="menu-left">
      <h3 class="pelanggan-title">Pelanggan</h3>
      <div class="pelanggan-list"></div>
      <div class="total-section">
        <div>Total</div>
        <div class="harga-total">Rp 0</div>
      </div>
    </div>

    <!-- Konten kanan -->
    <div class="menu-right">
      <!-- Pencarian produk -->
      <div class="menu-search">
        <input type="text" placeholder="Cari Produk">
        <button class="dropdown-btn">⌄</button>
      </div>

      <!-- Filter kategori -->
      <div class="menu-filter">
        <button class="filter-btn active">Coffee (15)</button>
        <button class="filter-btn">Makanan (23)</button>
        <button class="filter-btn">Snack (3)</button>
        <button class="filter-btn">Dessert (2)</button>
        <button class="filter-btn">Alacarte (20)</button>
      </div>

      <!-- Grid produk -->
      <div class="produk-grid">
        @for ($i = 0; $i < 11; $i++)
        <div class="produk-card" onclick="openModal('editModal')">
          <img src="{{ asset('img/sample-product.png') }}" alt="produk">
          <div class="produk-name">Mata Kodok</div>
          <div class="produk-price">Rp 50.000</div>
        </div>
        @endfor

        <!-- Card tambah -->
        <div class="produk-card add-card" onclick="openModal('addModal')">
          <span>+</span>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Tambah Produk -->
<div id="addModal" class="modal-overlay" style="display:none">
  <div class="modal-card">
    <h2 class="modal-title">Tambah Produk Baru</h2>
    <div class="modal-body">
      <div class="form-left">
        <label for="foto-upload" class="foto-box" id="add-preview-box">
          <span id="add-preview-text">Pilih Foto</span>
          <img id="add-preview-img" style="display:none;width:100%;border-radius:10px;">
        </label>
        <input type="file" id="foto-upload" accept="image/*" style="display:none"
               onchange="preview('add-preview-img','add-preview-text',event)">
      </div>
      <div class="form-right">
        <div class="form-group"><label>Nama</label><input type="text"></div>
        <div class="form-group"><label>Harga (Rp)</label><input type="number"></div>
        <div class="form-group"><label>Kategori</label><input type="text"></div>
        <div class="form-group"><label>Deskripsi</label><textarea rows="3"></textarea></div>
      </div>
    </div>
    <div class="modal-footer">
      <a href="#" class="modal-cancel" onclick="closeModal('addModal')">Kembali</a>
      <button class="btn-green">Tambah</button>
    </div>
  </div>
</div>

<!-- Modal Edit Produk -->
<div id="editModal" class="modal-overlay" style="display:none">
  <div class="modal-card">
    <h2 class="modal-title">Detail Produk</h2>
    <div class="modal-body">
      <div class="form-left">
        <img id="edit-preview-img" src="{{ asset('img/sample-product.png') }}" style="width:100%;border-radius:10px;">
      </div>
      <div class="form-right">
        <p><strong>Nama:</strong> Mata Kodok</p>
        <p><strong>Harga:</strong> Rp 50.000</p>
        <p><strong>Kategori:</strong> Coffee (Hot)</p>
        <p><strong>Deskripsi:</strong> Dibuat dari mata kodok segar dari gunung Himalaya.</p>
      </div>
    </div>
    <div class="modal-footer">
      <a href="#" class="modal-cancel" onclick="closeModal('editModal')">Kembali</a>
      <button class="btn-blue" onclick="openModal('editFormModal')">Edit</button>
      <button class="btn-red">Hapus</button>
    </div>
  </div>
</div>

<!-- Modal Form Edit -->
<div id="editFormModal" class="modal-overlay" style="display:none">
  <div class="modal-card">
    <h2 class="modal-title">Edit Produk</h2>
    <div class="modal-body">
      <div class="form-left">
        <label for="edit-foto" class="foto-box" id="edit-preview-box">
          <img id="edit-foto-img" src="{{ asset('img/sample-product.png') }}" style="width:100%;border-radius:10px;">
        </label>
        <input type="file" id="edit-foto" accept="image/*" style="display:none"
               onchange="preview('edit-foto-img', null, event)">
      </div>
      <div class="form-right">
        <div class="form-group"><label>Nama</label><input type="text" value="Mata Kodok"></div>
        <div class="form-group"><label>Harga (Rp)</label><input type="number" value="50000"></div>
        <div class="form-group"><label>Kategori</label><input type="text" value="Coffee (Hot)"></div>
        <div class="form-group"><label>Deskripsi</label><textarea rows="3">Dibuat dari mata kodok segar dari gunung Himalaya.</textarea></div>
      </div>
    </div>
    <div class="modal-footer">
      <a href="#" class="modal-cancel" onclick="closeModal('editFormModal')">Kembali</a>
      <button class="btn-green">Simpan</button>
    </div>
  </div>
</div>

<!-- Script -->
<script>
function openModal(id){ document.getElementById(id).style.display='flex'; }
function closeModal(id){ document.getElementById(id).style.display='none'; }

function preview(imgId, textId, e){
  const file = e.target.files[0];
  if(!file) return;
  const reader = new FileReader();
  reader.onload = () => {
    const img = document.getElementById(imgId);
    img.src = reader.result; img.style.display='block';
    if (textId) document.getElementById(textId).style.display='none';
  };
  reader.readAsDataURL(file);
}
</script>
@endsection
