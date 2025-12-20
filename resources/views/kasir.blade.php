@extends('layouts.main')
@section('title', 'NutaPOS - Kasir')

@section('content')

<div class="menu-container">
  <!-- Flash notification container (dynamically filled by JS) -->
  <div id="flash-container" style="position:fixed;top:20px;left:0;right:0;z-index:1050;display:flex;flex-direction:column;align-items:center;pointer-events:none"></div>
  <div class="menu-layout">

    <!-- Sidebar pelanggan kiri -->
    <div class="menu-left">
      <h3 class="pelanggan-title">Pelanggan</h3>
      <div class="pelanggan-list"></div>

      <!-- ========= TAMBAHAN: Tombol Member di atas garis Total ========= -->
      <div class="member-inline">
        {{-- Ubah: buka modal, bukan pindah halaman --}}
        <button type="button" class="member-btn" onclick="openModal('memberModal')">
          <span class="member-ico"><i class="fas fa-user"></i></span>
          Member
        </button>
      </div>
      <!-- =============================================================== -->

      <div class="total-section">
        <div>Total</div>
        <div class="harga-total">Rp 0</div>
        <button class="btn-green" style="width:40%" onclick="openModal('paymentModal')">Bayar</button>
      </div>
    </div>

    <!-- Konten kanan -->
    <div class="menu-right">
      <div class="menu-search">
        <input type="text" placeholder="Cari Produk" id="searchProduk" onkeyup="filterProduk()">
        <button class="dropdown-btn" id="categoryDropdownBtn" onclick="toggleCategoryFilter()" title="Toggle Filter Kategori">
          <span id="dropdownIcon">▼</span>
        </button>
      </div>

      <!-- Filter kategori -->
      <div class="menu-filter" id="categoryFilterContainer">
        <button class="filter-btn active" data-category="all">Semua</button>
        @foreach($categories as $category)
        <button class="filter-btn" data-category="{{ $category }}">{{ $category }}</button>
        @endforeach
      </div>

      <!-- Grid produk -->
      <div class="produk-grid">
        @foreach ($menus as $menu)
        <div class="produk-card"
          data-id="{{ $menu->ID_Menu }}"
          data-nama="{{ $menu->Nama }}"
          data-harga="{{ $menu->Harga }}"
          data-kategori="{{ $menu->Kategori }}"
          data-deskripsi="{{ $menu->Deskripsi ?? '' }}">
          <img src="{{ $menu->Foto ? 'data:image/jpeg;base64,'.base64_encode($menu->Foto) : asset('img/sample-product.png') }}"
            alt="{{ $menu->Nama }}">
          <div class="produk-name">{{ $menu->Nama }}</div>
          <div class="produk-price">Rp {{ number_format($menu->Harga, 0, ',', '.') }}</div>
        </div>
        @endforeach

        <!-- Tombol Tambah Produk -->
        <div class="produk-card add-card" onclick="openModal('addModal')">
          <span>+</span>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Tambah Produk -->
<div id="addModal" class="modal-overlay" style="display:none">
  <div class="modal-card" style="max-width:700px; width:min(95%,700px); display:flex; flex-direction:column; height:auto; max-height:85vh; background:white; border-radius:12px;">
    <h2 class="modal-title" style="flex-shrink:0; padding:16px; margin:0;">Tambah Produk Baru</h2>
    <form action="{{ route('menu.store') }}" method="POST" enctype="multipart/form-data">
      @csrf

      <!-- SCROLLABLE SECTION -->
      <div class="modal-scroll" style="max-height:calc(85vh - 170px); overflow-y:auto; padding:0 16px 16px 16px;">
        <div class="modal-body">
          <div class="form-left">
            <label for="foto-upload" class="foto-box" id="add-preview-box">
              <span id="add-preview-text">Pilih Foto</span>
              <img id="add-preview-img" style="display:none;width:100%;border-radius:10px;">
            </label>
            <input type="file" name="Foto" id="foto-upload" accept="image/*" style="display:none"
              onchange="preview('add-preview-img','add-preview-text',event)">
          </div>

          <div class="form-right">
            <div class="form-group"><label>Nama</label><input type="text" name="Nama" required></div>
            <div class="form-group"><label>Harga (Rp)</label><input type="number" name="Harga" required></div>
            <div class="form-group">
              <label>Kategori</label>
              <select id="add-kategori-select" onchange="toggleAddKategoriCustom()" style="width:100%;">
                <option value="">-- Pilih Kategori --</option>
                @foreach($categories as $category)
                <option value="{{ $category }}">{{ $category }}</option>
                @endforeach
                <option value="__custom__">+ Tambah Kategori Baru</option>
              </select>
              <div id="add-kategori-custom-container" style="display:none;margin-top:8px;">
                <input type="text" id="add-kategori-custom-input" placeholder="Masukkan kategori baru..."
                  style="width:100%;padding:8px;border:1px solid #ffc107;border-radius:6px;background:#fffbf0;">
                <small style="color:#856404;display:block;margin-top:4px;">💡 Contoh: Dessert, Combo Menu, dll.</small>
              </div>
              <input type="hidden" name="Kategori" id="add-kategori-final" required>
            </div>

            <div class="form-group"><label>Deskripsi</label><textarea name="Deskripsi" rows="3"></textarea></div>

            <!-- Tambahan: Bahan penyusun (WAJIB) -->
            <div class="form-group">
              <label>Bahan Penyusun <span style="color:#e74c3c;font-weight:bold;">*</span></label>
              <div style="background:#fff3cd;border:1px solid #ffc107;border-radius:6px;padding:8px;margin-bottom:8px;font-size:0.85em;">
                ⚠️ <strong>Wajib:</strong> Menu harus memiliki minimal 1 bahan penyusun
              </div>
              <div id="bahan-container">
                <div class="bahan-row" style="display:flex;gap:10px;margin-bottom:8px;">
                  <select name="bahan[]" class="bahan-select" required style="flex:1;">
                    <option value="">-- Pilih Bahan --</option>
                    @foreach ($stok as $item)
                    <option value="{{ $item->ID_Barang }}">{{ $item->Nama }} ({{ $item->Jumlah_Item }})</option>
                    @endforeach
                  </select>
                  <input type="number" name="jumlah_digunakan[]" placeholder="Jumlah" min="1" required style="width:100px;">
                  <button type="button" onclick="removeBahanRow(this)" class="btn-red-small" style="display:none;padding:6px 10px;">✕</button>
                </div>
              </div>
              <button type="button" onclick="addBahanRow()" class="btn-yellow" style="margin-top:5px;">+ Tambah Bahan</button>
            </div>
          </div>
        </div>
      </div>

      <!-- FOOTER (outside scroll, inside form) -->
      <div class="modal-footer" style="flex-shrink:0; display:flex; justify-content:flex-end; gap:10px; padding:12px 16px; border-top:1px solid #eee; background:#fff;">
        <a href="#" class="modal-cancel" onclick="closeModal('addModal')">Kembali</a>
        <button type="submit" class="btn-green">Tambah</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Edit Produk -->
<div id="editModal" class="modal-overlay" style="display:none;">
  <div class="modal-card" style="max-width:700px; width:min(95%,700px); display:flex; flex-direction:column; height:auto; max-height:85vh; background:white; border-radius:12px;">
    <h2 class="modal-title" style="flex-shrink:0; padding:16px; margin:0;">Edit Produk</h2>
    <form id="editForm" method="POST" enctype="multipart/form-data">
      @csrf
      @method('PUT')

      <!-- SCROLLABLE SECTION -->
      <div class="modal-scroll" style="max-height:calc(85vh - 170px); overflow-y:auto; padding:0 16px 16px 16px;">
        <div class="modal-body">
          <div class="form-left">
            <label for="edit-foto" class="foto-box">
              <img id="edit-foto-img" src="{{ asset('img/sample-product.png') }}" style="width:100%;border-radius:10px;">
            </label>
            <input type="file" name="Foto" id="edit-foto" accept="image/*" style="display:none"
              onchange="preview('edit-foto-img', null, event)">
          </div>
          <div class="form-right">
            <div class="form-group"><label>Nama</label><input type="text" name="Nama" id="editNama" required></div>
            <div class="form-group"><label>Harga (Rp)</label><input type="number" name="Harga" id="editHarga" required></div>
            <div class="form-group">
              <label>Kategori</label>
              <select id="edit-kategori-select" onchange="toggleEditKategoriCustom()" style="width:100%;">
                <option value="">-- Pilih Kategori --</option>
                @foreach($categories as $category)
                <option value="{{ $category }}">{{ $category }}</option>
                @endforeach
                <option value="__custom__">+ Tambah Kategori Baru</option>
              </select>
              <div id="edit-kategori-custom-container" style="display:none;margin-top:8px;">
                <input type="text" id="edit-kategori-custom-input" placeholder="Masukkan kategori baru..."
                  style="width:100%;padding:8px;border:1px solid #ffc107;border-radius:6px;background:#fffbf0;">
                <small style="color:#856404;display:block;margin-top:4px;">💡 Contoh: Dessert, Combo Menu, dll.</small>
              </div>
              <input type="hidden" name="Kategori" id="edit-kategori-final" required>
            </div>

            <div class="form-group">
              <label>Deskripsi</label>
              <textarea name="Deskripsi" id="editDeskripsi" rows="3"></textarea>
            </div>

            <div class="form-group">
              <label>Bahan Penyusun <span style="color:#e74c3c;font-weight:bold;">*</span></label>
              <div style="background:#fff3cd;border:1px solid #ffc107;border-radius:6px;padding:8px;margin-bottom:8px;font-size:0.85em;">
                ⚠️ <strong>Wajib:</strong> Menu harus memiliki minimal 1 bahan penyusun
              </div>
              <div id="edit-bahan-container">
                <!-- Will be filled dynamically -->
              </div>
              <button type="button" onclick="addEditBahanRow()" class="btn-yellow">+ Tambah Bahan</button>
            </div>
          </div>
        </div>
      </div>

      <!-- FOOTER (outside scroll, inside form) -->
      <div class="modal-footer" style="flex-shrink:0; display:flex; justify-content:flex-end; gap:10px; padding:12px 16px; border-top:1px solid #eee; background:#fff;">
        <a href="#" class="modal-cancel" onclick="closeModal('editModal')">Kembali</a>
        <button type="submit" class="btn-green">Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- Context Menu (improved) -->
<div id="contextMenu" role="menu" aria-hidden="true" style="
    position:absolute; display:none; min-width:180px; background:#fff;
    border:1px solid #e5e7eb; border-radius:10px; box-shadow:0 12px 28px rgba(0,0,0,0.18);
    z-index:9999; overflow:hidden">
  <ul style="list-style:none;margin:0;padding:6px">
    <li>
      <button id="btnEdit" role="menuitem" style="display:flex;align-items:center;gap:8px;width:100%;padding:10px 12px;border:none;background:transparent;cursor:pointer;border-radius:8px">
        <span style="font-size:14px">✏</span>
        <span style="font-size:14px">Edit menu</span>
      </button>
    </li>
    <li>
      <button id="btnDelete" role="menuitem" style="display:flex;align-items:center;gap:8px;width:100%;padding:10px 12px;border:none;background:transparent;cursor:pointer;border-radius:8px;color:#ef4444">
        <span style="font-size:14px">🗑</span>
        <span style="font-size:14px">Hapus menu</span>
      </button>
    </li>
  </ul>
</div>

<!-- 🔴 MODAL HAPUS MENU -->
<div id="deleteMenuModal" class="modal-overlay" style="display:none;">
  <div class="modal-card delete-modal">
    <h2 class="delete-title">Hapus Menu</h2>
    <p class="delete-text">Apakah Anda yakin ingin menghapus menu ini?</p>

    <div class="modal-footer delete-footer">
      <button type="button" class="btn-gray" onclick="closeDeleteMenuModal()">Tidak</button>
      <button type="button" class="btn-red" id="btnConfirmDeleteMenu">Ya, Hapus</button>
    </div>
  </div>
</div>

<!-- Modal Pembayaran -->
<div id="paymentModal" class="modal-overlay" style="display:none;">
  <div class="modal-card" style="max-width:700px">
    <h2 class="modal-title" style="text-align:center">Nominal Pembayaran : <span id="nominalBayar">Rp 0</span></h2>
    <hr style="margin:10px 0">
    <div class="modal-body" style="display:flex;flex-direction:column;gap:20px;">
      <div>
        <h3>Tunai</h3>
        <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:10px;">
          <button class="pay-btn" onclick="setPaymentExact()">Uang Pas</button>
          <button class="pay-btn" onclick="setPayment(25000)">Rp 25.000</button>
          <button class="pay-btn" onclick="setPayment(50000)">Rp 50.000</button>
          <button class="pay-btn" onclick="setPayment(100000)">Rp 100.000</button>
          <input id="customPay" type="number" placeholder="Rp Custom"
            style="flex:1;padding:8px 10px;border:1px solid #ccc;border-radius:8px;">
        </div>
      </div>

      <hr>
      <div>
        <h3>QRIS</h3>
        <div style="display:flex; flex-wrap:wrap; gap:10px; margin-top:10px;">
          <button class="pay-btn" onclick="setPaymentMethod('qris')">Ovo</button>
          <button class="pay-btn" onclick="setPaymentMethod('qris')">ShopeePay</button>
          <button class="pay-btn" onclick="setPaymentMethod('qris')">LinkAja</button>
          <button class="pay-btn" onclick="setPaymentMethod('qris')">Gopay</button>
        </div>
      </div>
    </div>

    <div class="modal-footer" style="justify-content:space-between">
      <a href="#" class="modal-cancel" onclick="closeModal('paymentModal')">Kembali</a>
      <div style="display:flex; gap:10px;">
        <button class="btn-yellow" onclick="resetTransaksi(false)">Simpan</button>
        <button class="btn-green" onclick="processPayment()">Proses Pembayaran</button>
      </div>
    </div>
  </div>
</div>

{{-- ============= MODAL PILIH MEMBER (BARU) ============= --}}
<style>
  #memberModal tbody td {
    color: #000 !important;
    font-weight: 500 !important;
  }

  #memberModal tbody td:first-child {
    color: #000 !important;
    font-weight: 600 !important;
  }

  #memberModal tbody td:nth-child(5) {
    color: #000 !important;
    font-weight: 600 !important;
  }

  /* Scrollable modal styling */
  .modal-scroll {
    max-height: calc(85vh - 170px);
    overflow-y: auto;
    padding-right: 8px;
  }

  /* Nice scrollbar for modal scroll container */
  .modal-scroll::-webkit-scrollbar {
    width: 8px;
  }

  .modal-scroll::-webkit-scrollbar-thumb {
    background: #bbb;
    border-radius: 10px;
  }

  .modal-scroll::-webkit-scrollbar-track {
    background: #f5f5f5;
  }

  /* Fallback for Firefox */
  .modal-scroll {
    scrollbar-width: thin;
    scrollbar-color: #bbb #f5f5f5;
  }
</style>

<div id="memberModal" class="modal-overlay" style="display:none;">
  <div class="modal-card" style="max-width:1100px; width:min(95%,1100px); display:flex; flex-direction:column; height:auto; max-height:85vh; background:white; border-radius:12px;">
    <h2 class="modal-title" style="flex-shrink:0; padding:16px; margin:0;">Daftar Member</h2>

    <!-- SCROLLABLE SECTION -->
    <div class="modal-scroll" style="max-height:calc(85vh - 170px); overflow-y:auto; flex:1;">
      <div style="padding:0 16px; display:flex; flex-direction:column; gap:16px;">
        <!-- Search bar -->
        <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap; padding-top:4px;">
          <div class="mmx-inputwrap" style="flex:1; min-width:260px;">
            <i class="fa-solid fa-search"></i>
            <input class="mmx-input" id="memberSearch" type="text" placeholder="Cari nama, email, atau telepon">
          </div>
          <button type="button" class="btn-yellow" id="memberSearchReset" style="height:42px;">Reset</button>
        </div>

        <!-- Table -->
        <div class="table-responsive" style="max-height:220px; overflow-y:auto; border:1px solid #e0e0e0; border-radius:6px;">
          <table class="table" id="tblMembers" style="font-size:0.9em;">
            <thead>
              <tr style="font-size:0.85em;">
                <th style="width:45px; padding:8px;">NO</th>
                <th style="padding:8px;">NAMA</th>
                <th style="padding:8px;">EMAIL</th>
                <th style="padding:8px;">NO. TELEPON</th>
                <th style="width:100px; padding:8px;">TOTAL POIN</th>
                <th style="width:70px; text-align:center; padding:8px;">PILIH</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td colspan="6" style="padding:10px;">Memuat data...</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Form -->
        <form onsubmit="return false;" style="padding-bottom:16px;">
          <div class="mmx-formgrid">
            <!-- Nama -->
            <div class="mmx-field">
              <label for="m_nama">Nama</label>
              <div class="mmx-inputwrap">
                <i class="fa-solid fa-user"></i>
                <input class="mmx-input" id="m_nama" type="text" readonly>
              </div>
            </div>

            <!-- Email -->
            <div class="mmx-field">
              <label for="m_email">Email</label>
              <div class="mmx-inputwrap">
                <i class="fa-solid fa-envelope"></i>
                <input class="mmx-input" id="m_email" type="text" readonly>
              </div>
            </div>

            <!-- Telepon -->
            <div class="mmx-field">
              <label for="m_telp">No. Telepon</label>
              <div class="mmx-inputwrap">
                <i class="fa-solid fa-phone"></i>
                <input class="mmx-input" id="m_telp" type="text" readonly>
              </div>
            </div>

            <!-- Total Poin -->
            <div class="mmx-field">
              <label for="m_poin_total">Total Poin</label>
              <div class="mmx-inputwrap">
                <i class="fa-solid fa-star"></i>
                <input class="mmx-input" id="m_poin_total" type="number" readonly>
              </div>
            </div>

            <!-- Poin yang akan digunakan (span 2 kolom) -->
            <div class="mmx-field mmx-field--span2">
              <label for="m_poin_pakai">Poin yang akan digunakan</label>
              <div class="mmx-inputwrap">
                <i class="fa-solid fa-wallet"></i>
                <input class="mmx-input" id="m_poin_pakai" type="number" min="0" value="0">
              </div>
              <small id="m_poin_help" class="mmx-muted">Maksimal sesuai total poin.</small>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- FOOTER (outside scroll, inside modal) -->
    <div class="modal-footer" style="flex-shrink:0; display:flex; justify-content:space-between; gap:10px; padding:12px 16px; border-top:1px solid #eee; background:#fff;">
      <a href="#" class="modal-cancel" onclick="closeModal('memberModal')">Kembali</a>
      <button class="btn-green" id="btnMemberApply">Lanjutkan</button>
    </div>
  </div>
</div>
{{-- ===================================================== --}}

<script>
  window.paymentMethod = 'tunai';

  window.setPaymentMethod = function(method) {
    window.paymentMethod = method;
  };
</script>

<script src="https://app.sandbox.midtrans.com/snap/snap.js"
  data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}">
</script>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    let cart = [];
    const pelangganList = document.querySelector('.pelanggan-list');
    const totalHargaEl = document.querySelector('.harga-total');
    const contextMenu = document.getElementById('contextMenu');
    let currentCardId = null;

    // Global flash helper to show notifications (reusable across functions)
    window.showFlash = function(type, message, timeout = 5000) {
      const container = document.getElementById('flash-container');
      if (!container) return;
      const div = document.createElement('div');
      div.className = `flash-alert ${type === 'success' ? 'flash-success' : 'flash-error'}`;
      div.style.pointerEvents = 'auto';
      div.textContent = message;
      container.appendChild(div);
      setTimeout(() => {
        div.style.transition = 'opacity 0.3s, transform 0.3s';
        div.style.opacity = '0';
        div.style.transform = 'translateY(-6px)';
        setTimeout(() => div.remove(), 350);
      }, timeout);
    }

    // Alias untuk showNotification
    window.showNotification = window.showFlash;

    // Debug form edit submit
    const editForm = document.getElementById('editForm');
    if (editForm) {
      editForm.addEventListener('submit', function(e) {
        const formData = new FormData(this);
        console.log('📝 Form Edit Submit - Data yang dikirim:');
        console.log('  Nama:', formData.get('Nama'));
        console.log('  Harga:', formData.get('Harga'));
        console.log('  Kategori:', formData.get('Kategori'));

        const bahanArray = formData.getAll('bahan[]');
        const jumlahArray = formData.getAll('jumlah_digunakan[]');
        console.log('  Bahan[]:', bahanArray);
        console.log('  Jumlah[]:', jumlahArray);

        if (bahanArray.length === 0) {
          console.error('❌ TIDAK ADA BAHAN! Form akan ditolak oleh server.');
        }
      });
    }

    // Toggle kategori custom untuk form ADD
    window.toggleAddKategoriCustom = function() {
      const select = document.getElementById('add-kategori-select');
      const customContainer = document.getElementById('add-kategori-custom-container');
      const customInput = document.getElementById('add-kategori-custom-input');
      const finalInput = document.getElementById('add-kategori-final');

      if (select.value === '__custom__') {
        customContainer.style.display = 'block';
        customInput.focus();
        finalInput.value = '';
      } else {
        customContainer.style.display = 'none';
        customInput.value = '';
        finalInput.value = select.value;
      }
    }

    // Toggle kategori custom untuk form EDIT
    window.toggleEditKategoriCustom = function() {
      const select = document.getElementById('edit-kategori-select');
      const customContainer = document.getElementById('edit-kategori-custom-container');
      const customInput = document.getElementById('edit-kategori-custom-input');
      const finalInput = document.getElementById('edit-kategori-final');

      if (select.value === '__custom__') {
        customContainer.style.display = 'block';
        customInput.focus();
        finalInput.value = '';
      } else {
        customContainer.style.display = 'none';
        customInput.value = '';
        finalInput.value = select.value;
      }
    }

    // Update final input saat mengetik kategori custom
    const addCustomInput = document.getElementById('add-kategori-custom-input');
    const addFinalInput = document.getElementById('add-kategori-final');
    if (addCustomInput && addFinalInput) {
      addCustomInput.addEventListener('input', function() {
        addFinalInput.value = this.value;
      });
    }

    const editCustomInput = document.getElementById('edit-kategori-custom-input');
    const editFinalInput = document.getElementById('edit-kategori-final');
    if (editCustomInput && editFinalInput) {
      editCustomInput.addEventListener('input', function() {
        editFinalInput.value = this.value;
      });
    }
    // Category filter
    document.querySelectorAll('.filter-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        const category = this.dataset.category;
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        filterProducts(category);
      });
    });

    function filterProducts(category) {
      const cards = document.querySelectorAll('.produk-card:not(.add-card)');
      cards.forEach(card => {
        if (category === 'all' || card.dataset.kategori === category) {
          card.style.display = '';
        } else {
          card.style.display = 'none';
        }
      });
    }

    function openModal(id) {
      document.getElementById(id).style.display = 'flex';
      if (id === 'paymentModal') {
        const totalText = totalHargaEl.textContent;
        document.getElementById('nominalBayar').textContent = totalText;
      }
      // Saat memberModal dibuka, load data member
      if (id === 'memberModal') {
        if (typeof loadMembers === 'function') {
          loadMembers();
          clearMemberForm();
        }
      }
    }

    function closeModal(id) {
      document.getElementById(id).style.display = 'none';
    }

    function preview(imgId, textId, e) {
      const file = e.target.files[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = () => {
        const img = document.getElementById(imgId);
        img.src = reader.result;
        img.style.display = 'block';
        if (textId) document.getElementById(textId).style.display = 'none';
      };
      reader.readAsDataURL(file);
    }

    function addToCart(id, nama, harga) {
      const existing = cart.find(item => item.id === id);
      if (existing) {
        existing.qty += 1; // tambah quantity jika sudah ada
      } else {
        cart.push({
          id,
          nama,
          harga,
          qty: 1
        });
      }
      renderCart();
    }

    function removeFromCart(index) {
      if (cart[index].qty > 1) {
        cart[index].qty -= 1;
      } else {
        cart.splice(index, 1);
      }
      renderCart();
    }

    function renderCart() {
      pelangganList.innerHTML = '';
      let total = 0;
      cart.forEach((item, index) => {
        const div = document.createElement('div'); // ✅ tambahkan ini
        div.classList.add('pelanggan-item');
        div.innerHTML = `
          <div><strong>${item.nama}</strong><br>
          <small>${item.qty}x Rp ${item.harga.toLocaleString('id-ID')}</small></div>
          <button onclick="removeFromCart(${index})" style="background:none;border:none;color:red;font-weight:bold;cursor:pointer;">❌</button>
        `;
        pelangganList.appendChild(div);
        total += item.harga * item.qty;
      });
      totalHargaEl.textContent = `Rp ${total.toLocaleString('id-ID')}`;
      document.getElementById('nominalBayar').textContent = `Rp ${total.toLocaleString('id-ID')}`;
    }

    const cards = document.querySelectorAll('.produk-card:not(.add-card)');
    cards.forEach(card => {
      const id = card.dataset.id;
      const nama = card.dataset.nama;
      const harga = parseInt(card.dataset.harga);
      card.addEventListener('click', () => addToCart(id, nama, harga));

      // Right-click context menu
      card.addEventListener('contextmenu', e => {
        e.preventDefault();
        currentCardId = card.dataset.id;
        openContextMenu(e.clientX, e.clientY);
      });

      // Touch long-press for mobile
      let pressTimer;
      card.addEventListener('touchstart', e => {
        const touch = e.touches[0];
        pressTimer = setTimeout(() => {
          currentCardId = card.dataset.id;
          openContextMenu(touch.clientX, touch.clientY);
        }, 500);
      });
      card.addEventListener('touchend', () => clearTimeout(pressTimer));
      card.addEventListener('touchmove', () => clearTimeout(pressTimer));
    });

    function openContextMenu(x, y) {
      const padding = 12;
      const menuRect = { width: 200, height: 120 };
      const vw = window.innerWidth;
      const vh = window.innerHeight;
      
      // Posisikan di sebelah card (kanan dari card)
      const card = document.querySelector(`.produk-card[data-id="${currentCardId}"]`);
      if (card) {
        const rect = card.getBoundingClientRect();
        let left = rect.right + padding;  // Sebelah kanan card
        let top = rect.top;                 // Selaras dengan card
        
        // Cek apakah menu melebihi viewport
        if (left + menuRect.width + padding > vw) {
          left = rect.left - menuRect.width - padding;  // Posisi di sebelah kiri card
        }
        if (top + menuRect.height + padding > vh) {
          top = vh - menuRect.height - padding;  // Posisi di bawah jika melebihi
        }
        
        contextMenu.style.left = `${left}px`;
        contextMenu.style.top = `${top}px`;
      } else {
        // Fallback ke posisi cursor jika card tidak ditemukan
        let left = x, top = y;
        if (x + menuRect.width + padding > vw) left = vw - menuRect.width - padding;
        if (y + menuRect.height + padding > vh) top = vh - menuRect.height - padding;
        contextMenu.style.left = `${left}px`;
        contextMenu.style.top = `${top}px`;
      }
      contextMenu.style.display = 'block';
      contextMenu.setAttribute('aria-hidden', 'false');
    }

    document.addEventListener('click', e => {
      if (!contextMenu.contains(e.target)) {
        contextMenu.style.display = 'none';
        contextMenu.setAttribute('aria-hidden', 'true');
      }
      // 🔁 Reset transaksi
      window.resetTransaksi = function(isAfterPayment = false) {
        const pelangganList = document.querySelector('.pelanggan-list');
        const totalHargaEl = document.querySelector('.harga-total');
        const nominalBayar = document.getElementById('nominalBayar');
        const customPay = document.getElementById('customPay');

        if (!isAfterPayment) {
          if (!cart || cart.length === 0) {
            window.showFlash && window.showFlash('error', "⚠️ Tidak ada item dalam keranjang! Silakan tambahkan produk terlebih dahulu sebelum menyimpan.");
            return;
          }
          window.showFlash && window.showFlash('error', "❌ Belum ada transaksi yang berhasil disimpan. Silakan lakukan pembayaran terlebih dahulu.");
          return;
        }

        // Bersihkan keranjang setelah pembayaran berhasil
        cart.length = 0;
        if (pelangganList) pelangganList.innerHTML = '';
        if (totalHargaEl) totalHargaEl.textContent = 'Rp 0';
        if (nominalBayar) nominalBayar.textContent = 'Rp 0';
        if (customPay) customPay.value = '';

        // hapus member yang dipilih
        window.selectedMember = null;
        const pill = document.getElementById('selected-member-pill');
        if (pill) pill.remove();

        // tutup modal
        if (typeof closeModal === 'function') {
          closeModal('paymentModal');
        }
      };

    });

    document.getElementById('btnEdit').addEventListener('click', async () => {
      contextMenu.style.display = 'none';
      contextMenu.setAttribute('aria-hidden', 'true');
      const card = document.querySelector(`.produk-card[data-id="${currentCardId}"]`);
      if (!card) return;

      // Set basic info
      document.getElementById('editNama').value = card.dataset.nama;
      document.getElementById('editHarga').value = card.dataset.harga;
      document.getElementById('editDeskripsi').value = card.dataset.deskripsi; // ✅ FIX BEKERJA
      document.getElementById('editForm').action = `/menu/${currentCardId}`;
      document.getElementById('edit-foto-img').src = card.querySelector('img').src;
      // Set kategori dengan support custom kategori
      const kategoriValue = card.dataset.kategori;
      const editKategoriSelect = document.getElementById('edit-kategori-select');
      const editKategoriFinal = document.getElementById('edit-kategori-final');

      // Cek apakah kategori ada di dropdown
      const optionExists = Array.from(editKategoriSelect.options).some(opt => opt.value === kategoriValue);

      if (optionExists) {
        editKategoriSelect.value = kategoriValue;
        editKategoriFinal.value = kategoriValue;
      } else {
        // Kategori custom, tampilkan input custom
        editKategoriSelect.value = '__custom__';
        toggleEditKategoriCustom();
        document.getElementById('edit-kategori-custom-input').value = kategoriValue;
        editKategoriFinal.value = kategoriValue;
      }

      document.getElementById('editDeskripsi').value = card.dataset.deskripsi;
      document.getElementById('edit-foto-img').src = card.querySelector('img').src;
      document.getElementById('editForm').action = `/menu/${currentCardId}`;

      // Get bahan penyusun
      try {
        const response = await fetch(`/menu/${currentCardId}/bahan`);
        const bahan = await response.json();
        console.log('📦 Bahan data from API:', bahan);

        const container = document.getElementById('edit-bahan-container');
        container.innerHTML = '';

        if (bahan.length === 0) {
          console.warn('⚠️ No bahan found, adding empty row');
          addEditBahanRow();
        } else {
          console.log(`✅ Loading ${bahan.length} bahan items`);
          bahan.forEach(b => {
            console.log(`  → ID_Barang: ${b.ID_Barang}, Jumlah: ${b.Jumlah_Digunakan}`);
            const row = createBahanRowForEdit(b.ID_Barang, b.Jumlah_Digunakan);
            container.appendChild(row);
          });
          updateEditBahanDeleteButtons();
        }
      } catch (error) {
        console.error('❌ Error fetching bahan:', error);
        addEditBahanRow();
      }

      openModal('editModal');
    });


    document.getElementById('btnDelete').addEventListener('click', () => {
      contextMenu.style.display = 'none';
      contextMenu.setAttribute('aria-hidden', 'true');
      openDeleteMenuModal(currentCardId);
    });

    window.setPayment = function(amount) {
      document.getElementById('customPay').value = amount;

      // hanya set tunai jika user benar-benar mau tunai
      if (amount > 0 || amount === 0) {
        window.paymentMethod = 'tunai';
      }
    }

    window.setPaymentExact = function() {
      const totalText = totalHargaEl.textContent.replace(/[^\d]/g, '');
      const total = parseInt(totalText) || 0;
      document.getElementById('customPay').value = total;
      window.paymentMethod = 'tunai';
    }

    window.processPayment = function() {
      const totalText = totalHargaEl.textContent.replace(/[^\d]/g, '');
      const total = parseInt(totalText) || 0;
      const customPay = parseInt(document.getElementById('customPay').value) || 0;

      // Validasi item keranjang
      if (cart.length === 0) {
        window.showNotification('error', 'Pembayaran gagal, keranjang kosong!');
        return;
      }

      // 🟢 1. Jika pembayaran tunai → cek customPay
      if (paymentMethod === 'tunai') {
        if (customPay < total) {
          window.showNotification('error', 'Pembayaran gagal, nominal tunai kurang!');
          return;
        }

        // Simpan transaksi tunai
        return saveTransaksiKeDatabase(cart, total, 'Tunai');
      }

      // 🟢 2. Jika pembayaran QRIS/Midtrans → abaikan customPay
      if (paymentMethod === 'qris') {
        fetch('{{ route("payment.snap") }}', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
              items: cart.map(c => ({
                id: c.id,
                qty: c.qty
              })),
              total: total,
              metode: 'QRIS'
            })
          })
          .then(res => res.json())
          .then(data => {
            snap.pay(data.snapToken, {
              onSuccess: function(result) {
                window.showNotification('success', 'Pembayaran Berhasil!');
                saveTransaksiKeDatabase(cart, total, 'QRIS');
              },
              onPending: function(result) {
                window.showNotification('error', 'Menunggu pembayaran…');
              },
              onError: function(result) {
                window.showNotification('error', 'Pembayaran gagal!');
              }
            });
          })
          .catch(err => {
            console.error(err);
            window.showNotification('error', 'Gagal membuat transaksi digital!');
          });
      }
    }

    async function saveTransaksiKeDatabase(cart, total, metode) {
      try {
        // Siapkan payload dengan data member jika ada
        const payload = {
          items: cart.map(c => ({
            id: c.id,
            qty: c.qty
          })),
          total: total,
          metode: metode
        };

        // Tambahkan data member jika ada yang dipilih
        if (window.selectedMember) {
          payload.member = {
            id: window.selectedMember.id,
            poin_pakai: window.selectedMember.poin_pakai
          };
        }

        const res = await fetch('{{ route("transaksi.store") }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          body: JSON.stringify(payload)
        });

        let data = null;
        try {
          data = await res.json();
        } catch (e) {}

        if (!res.ok || (data && data.status === 'error')) {
          const msg = (data && data.message) ? data.message : 'Gagal menyimpan transaksi!';
          window.showFlash('error', msg);
          return;
        }

        window.showFlash('success', 'Transaksi berhasil disimpan!');
        resetTransaksi(true);
      } catch (err) {
        console.error(err);
        window.showFlash('error', 'Gagal menyimpan transaksi!');
      }
    }

    // ========== TOGGLE CATEGORY FILTER ==========
    window.toggleCategoryFilter = function() {
      const filterContainer = document.getElementById('categoryFilterContainer');
      const dropdownIcon = document.getElementById('dropdownIcon');
      const dropdownBtn = document.getElementById('categoryDropdownBtn');

      if (filterContainer.style.display === 'none' || !filterContainer.style.display) {
        // Show filter
        filterContainer.style.display = 'flex';
        filterContainer.style.maxHeight = '500px';
        filterContainer.style.opacity = '1';
        dropdownIcon.textContent = '▼';
        dropdownBtn.classList.remove('collapsed');
      } else {
        // Hide filter
        filterContainer.style.maxHeight = '0';
        filterContainer.style.opacity = '0';
        setTimeout(() => {
          filterContainer.style.display = 'none';
        }, 300);
        dropdownIcon.textContent = '▶';
        dropdownBtn.classList.add('collapsed');
      }
    }

    window.filterProduk = function() {
      const keyword = document.getElementById("searchProduk").value.toLowerCase().trim();
      document.querySelectorAll(".produk-card").forEach(card => {
        if (card.classList.contains("add-card")) return;
        const nama = card.dataset.nama.toLowerCase();
        card.style.display = (!keyword || nama.includes(keyword)) ? "block" : "none";
      });
    }

    function createBahanRow(selectedId = '', jumlah = '') {
      const row = document.createElement('div');
      row.classList.add('bahan-row');
      row.style.cssText = 'display:flex;gap:10px;margin-bottom:8px;';
      row.innerHTML = `
        <select name="bahan[]" class="bahan-select" required style="flex:1;">
          <option value="">-- Pilih Bahan --</option>
          @foreach ($stok as $item)
            <option value="{{ $item->ID_Barang }}" ${String(selectedId) === '{{ $item->ID_Barang }}' ? 'selected' : ''}>
              {{ $item->Nama }} ({{ $item->Jumlah_Item }})
            </option>
          @endforeach
        </select>
        <input type="number" name="jumlah_digunakan[]" placeholder="Jumlah" value="${jumlah}" min="1" required style="width:100px;">
        <button type="button" class="btn-remove-bahan" onclick="removeBahanRow(this)" style="padding:6px 10px;background:#e74c3c;color:#fff;border:none;border-radius:4px;cursor:pointer;">&times;</button>
      `;
      return row;
    }

    function createBahanRowForEdit(selectedId = '', jumlah = '') {
      const row = document.createElement('div');
      row.classList.add('bahan-row');
      row.style.cssText = 'display:flex;gap:10px;margin-bottom:8px;';
      row.innerHTML = `
        <select name="bahan[]" class="bahan-select" required style="flex:1;">
          <option value="">-- Pilih Bahan --</option>
          @foreach ($stok as $item)
            <option value="{{ $item->ID_Barang }}" ${String(selectedId) === '{{ $item->ID_Barang }}' ? 'selected' : ''}>
              {{ $item->Nama }} ({{ $item->Jumlah_Item }})
            </option>
          @endforeach
        </select>
        <input type="number" name="jumlah_digunakan[]" placeholder="Jumlah" value="${jumlah}" min="1" required style="width:100px;">
        <button type="button" class="btn-remove-bahan" onclick="removeEditBahanRow(this)" style="padding:6px 10px;background:#e74c3c;color:#fff;border:none;border-radius:4px;cursor:pointer;">&times;</button>
      `;
      return row;
    }

    window.addBahanRow = function() {
      const container = document.getElementById('bahan-container');
      const newRow = createBahanRow();
      container.appendChild(newRow);
      updateBahanDeleteButtons();
    }

    window.addEditBahanRow = function() {
      const container = document.getElementById('edit-bahan-container');
      const newRow = createBahanRow();
      container.appendChild(newRow);
      updateEditBahanDeleteButtons();
    }

    window.removeBahanRow = function(btn) {
      const container = document.getElementById('bahan-container');
      const rows = container.querySelectorAll('.bahan-row');

      // Cegah hapus jika hanya ada 1 bahan (wajib minimal 1)
      if (rows.length <= 1) {
        alert('⚠️ Menu harus memiliki minimal 1 bahan penyusun!');
        return;
      }

      btn.closest('.bahan-row').remove();
      updateBahanDeleteButtons();
    }

    window.removeEditBahanRow = function(btn) {
      const container = document.getElementById('edit-bahan-container');
      const rows = container.querySelectorAll('.bahan-row');

      if (rows.length <= 1) {
        alert('⚠️ Menu harus memiliki minimal 1 bahan penyusun!');
        return;
      }

      btn.closest('.bahan-row').remove();
      updateEditBahanDeleteButtons();
    }

    // Update visibility tombol hapus (sembunyikan jika hanya 1 bahan)
    function updateBahanDeleteButtons() {
      const container = document.getElementById('bahan-container');
      const rows = container.querySelectorAll('.bahan-row');
      rows.forEach((row, index) => {
        const deleteBtn = row.querySelector('.btn-red-small, .btn-remove-bahan');
        if (deleteBtn) {
          deleteBtn.style.display = rows.length > 1 ? 'block' : 'none';
        }
      });
    }

    function updateEditBahanDeleteButtons() {
      const container = document.getElementById('edit-bahan-container');
      const rows = container.querySelectorAll('.bahan-row');
      rows.forEach((row, index) => {
        const deleteBtn = row.querySelector('.btn-red-small, .btn-remove-bahan');
        if (deleteBtn) {
          deleteBtn.style.display = rows.length > 1 ? 'block' : 'none';
        }
      });
    }

    // Initialize delete button visibility on page load
    document.addEventListener('DOMContentLoaded', function() {
      updateBahanDeleteButtons();
    });

    window.openModal = openModal;
    window.closeModal = closeModal;
    window.removeFromCart = removeFromCart;
  });

  // ============ SCRIPT KHUSUS MODAL MEMBER ============
  window.selectedMember = null;
  window.membersCache = [];

  function handleMemberRadioChange(e) {
    const R = e.target;
    const nm = decodeURIComponent(R.dataset.nama || '');
    const em = decodeURIComponent(R.dataset.email || '');
    const tl = decodeURIComponent(R.dataset.telp || '');
    const pt = Number(R.dataset.poin || 0);
    document.getElementById('m_nama').value = nm;
    document.getElementById('m_email').value = em;
    document.getElementById('m_telp').value = tl;
    document.getElementById('m_poin_total').value = pt;
    const poinP = document.getElementById('m_poin_pakai');
    poinP.max = pt;
    if (Number(poinP.value) > pt) poinP.value = pt;
    document.getElementById('m_poin_help').textContent = `Maksimal ${pt} poin.`;
    poinP.dataset.memberId = R.value;
  }

  function renderMemberRows(list, opts = {}) {
    const {
      isFiltered = false
    } = opts;
    const tbody = document.querySelector('#tblMembers tbody');
    if (!tbody) return;
    if (!Array.isArray(list) || list.length === 0) {
      const emptyMsg = isFiltered && (window.membersCache || []).length ? 'Tidak ada hasil yang cocok.' : 'Belum ada data member.';
      tbody.innerHTML = `<tr><td colspan="6" style="padding:10px;">${emptyMsg}</td></tr>`;
      return;
    }

    tbody.innerHTML = list.map((m, i) => `
      <tr style="color: #000000; font-weight: 500;">
        <td style="padding:8px;">${String(i+1).padStart(2,'0')}</td>
        <td style="padding:8px;">${esc(m.nama||'')}</td>
        <td style="padding:8px;">${esc(m.email||'')}</td>
        <td style="padding:8px;">${esc(m.no_telp||'')}</td>
        <td style="padding:8px;">${Number(m.poin||0)}</td>
        <td style="text-align:center; padding:8px;">
          <input type="radio" name="pick_member" value="${m.id}"
            data-nama="${encodeURIComponent(m.nama||'')}"
            data-email="${encodeURIComponent(m.email||'')}"
            data-telp="${encodeURIComponent(m.no_telp||'')}"
            data-poin="${Number(m.poin||0)}">
        </td>
      </tr>
    `).join('');

    document.querySelectorAll('input[name="pick_member"]').forEach(r => {
      r.addEventListener('change', handleMemberRadioChange);
    });
  }

  function filterMembers(term) {
    const q = (term || '').trim().toLowerCase();
    if (!q) {
      renderMemberRows(window.membersCache || []);
      return;
    }
    const filtered = (window.membersCache || []).filter(m =>
      String(m.nama || '').toLowerCase().includes(q) ||
      String(m.email || '').toLowerCase().includes(q) ||
      String(m.no_telp || '').toLowerCase().includes(q)
    );
    renderMemberRows(filtered, {
      isFiltered: true
    });
  }

  async function loadMembers() {
    const tbody = document.querySelector('#tblMembers tbody');
    if (!tbody) return;
    tbody.innerHTML = `<tr><td colspan="6">Memuat data...</td></tr>`;
    try {
      const res = await fetch(`{{ route('kasir.members.json') }}`);
      const data = await res.json();
      window.membersCache = Array.isArray(data) ? data : [];
      const searchBox = document.getElementById('memberSearch');
      const term = searchBox ? searchBox.value : '';
      filterMembers(term);

    } catch (e) {
      console.error(e);
      tbody.innerHTML = `<tr><td colspan="6">Gagal memuat data.</td></tr>`;
      window.membersCache = [];
    }
  }

  function clearMemberForm() {
    document.getElementById('m_nama').value = '';
    document.getElementById('m_email').value = '';
    document.getElementById('m_telp').value = '';
    document.getElementById('m_poin_total').value = 0;
    const poinP = document.getElementById('m_poin_pakai');
    poinP.value = 0;
    poinP.removeAttribute('max');
    poinP.dataset.memberId = '';
    document.getElementById('m_poin_help').textContent = 'Maksimal sesuai total poin.';
    const searchInput = document.getElementById('memberSearch');
    if (searchInput) searchInput.value = '';
  }

  document.addEventListener('DOMContentLoaded', () => {
    const memberSearch = document.getElementById('memberSearch');
    const memberSearchReset = document.getElementById('memberSearchReset');

    if (memberSearch) {
      memberSearch.addEventListener('input', e => filterMembers(e.target.value));
      memberSearch.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
          memberSearch.value = '';
          filterMembers('');
        }
      });
    }

    if (memberSearchReset) {
      memberSearchReset.addEventListener('click', () => {
        if (memberSearch) {
          memberSearch.value = '';
          memberSearch.focus();
        }
        filterMembers('');
      });
    }

    document.getElementById('btnMemberApply').addEventListener('click', () => {
      const poinP = document.getElementById('m_poin_pakai');
      const id = poinP.dataset.memberId || '';
      const pt = Number(document.getElementById('m_poin_total').value || 0);
      const pp = Number(poinP.value || 0);
      if (!id) {
        window.showNotification('error', '⚠️ Silakan pilih member terlebih dahulu.');
        return;
      }
      if (pp < 0 || pp > pt) {
        window.showNotification('error', '❌ Poin yang dipakai tidak valid.');
        return;
      }

      window.selectedMember = {
        id,
        nama: document.getElementById('m_nama').value,
        email: document.getElementById('m_email').value,
        no_telp: document.getElementById('m_telp').value,
        poin_total: pt,
        poin_pakai: pp
      };

      // tampilkan ringkasan di panel kiri
      const host = document.querySelector('.pelanggan-list');
      if (host) {
        let pill = document.getElementById('selected-member-pill');
        if (!pill) {
          pill = document.createElement('div');
          pill.id = 'selected-member-pill';
          pill.className = 'selected-member-pill';
          host.prepend(pill);
        }
        pill.innerHTML = `
        <div>
          <strong>${esc(window.selectedMember.nama)}</strong><br>
          <small>${esc(window.selectedMember.email)} • ${esc(window.selectedMember.no_telp || '-')}</small><br>
          <small>Poin dipakai: ${window.selectedMember.poin_pakai} / ${window.selectedMember.poin_total}</small>
        </div>
        <button type="button" style="background:none;border:none;color:#d33;cursor:pointer;font-weight:700;"
          onclick="(function(){ const p=document.getElementById('selected-member-pill'); if(p) p.remove(); window.selectedMember=null; })()">×</button>
      `;
      }

      closeModal('memberModal');
    });
  });

  function esc(s) {
    return String(s).replace(/[&<>"']/g, c => ({
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#039;'
    } [c]));
  }
</script>
<script>
  // =============================
  // � MODAL HAPUS MENU (FINAL)
  // =============================

  let menuIdToDelete = null;

  function openDeleteMenuModal(menuId) {
    menuIdToDelete = menuId;
    document.getElementById('deleteMenuModal').style.display = 'flex';
  }

  function closeDeleteMenuModal() {
    document.getElementById('deleteMenuModal').style.display = 'none';
    menuIdToDelete = null;
  }

  document.getElementById('btnConfirmDeleteMenu').addEventListener('click', () => {
    if (!menuIdToDelete) return;

    fetch(`/menu/${menuIdToDelete}`, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
      })
      .then(res => {
        if (!res.ok) throw new Error();
        return res.text();
      })
      .then(() => {
        closeDeleteMenuModal();
        showFlash('success', '✅ Menu berhasil dihapus!');
        setTimeout(() => location.reload(), 700);
      })
      .catch(() => {
        showFlash('error', '❌ Gagal menghapus menu!');
      });
  });
</script>

{{-- ================= FLASH SESSION HANDLER (FINAL & AMAN) ================= --}}
<script>
  document.addEventListener('DOMContentLoaded', function() {

    @if(session('flash_success'))
    if (typeof window.showFlash === 'function') {
      window.showFlash(
        'success',
        @json(session('flash_success')),
        6000
      );
    }
    @endif

    @if(session('flash_error'))
    if (typeof window.showFlash === 'function') {
      window.showFlash(
        'error',
        @json(session('flash_error')),
        7000
      );
    }
    @endif

  });
</script>
{{-- ====================================================================== --}}

@endsection