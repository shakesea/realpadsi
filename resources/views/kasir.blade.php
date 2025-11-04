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
          <button class="btn-green" style=";width:40%" onclick="openModal('paymentModal')"> Bayar </button>
      </div>
    </div>

    <!-- Konten kanan -->
    <div class="menu-right">
      <!-- Pencarian produk -->
      <div class="menu-search">
        <input type="text" placeholder="Cari Produk" id="searchProduk" onkeyup="filterProduk()">
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
        @foreach ($menus as $menu)
<div class="produk-card"
     data-nama="{{ strtolower($menu->Nama) }}">            <img src="{{ $menu->Foto ? 'data:image/jpeg;base64,'.base64_encode($menu->Foto) : asset('img/sample-product.png') }}" alt="{{ $menu->Nama }}">
            <div class="produk-name">{{ $menu->Nama }}</div>
            <div class="produk-price">Rp {{ number_format($menu->Harga, 0, ',', '.') }}</div>
          </div>

        @endforeach


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

    <form action="{{ route('menu.store') }}" method="POST" enctype="multipart/form-data">
      @csrf
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
          <div class="form-group">
            <label>Nama</label>
            <input type="text" name="Nama" required>
          </div>

          <div class="form-group">
            <label>Harga (Rp)</label>
            <input type="number" name="Harga" required>
          </div>

          <div class="form-group">
            <label>Kategori</label>
            <input type="text" name="Kategori" required>
          </div>

          <div class="form-group">
            <label>Deskripsi</label>
            <textarea name="Deskripsi" rows="3"></textarea>
          </div>

          <!-- BAGIAN BARU: Bahan Penyusun -->
          <div class="form-group">
            <label>Bahan Penyusun</label>
            <div id="bahan-container">
              <div class="bahan-row" style="display:flex;gap:10px;margin-bottom:8px;">
                <select name="bahan[]" class="bahan-select" required style="flex:1;">
                  <option value="">-- Pilih Bahan --</option>
                  @foreach ($stok as $item)
                    <option value="{{ $item->ID_Barang }}">{{ $item->Nama }} ({{ $item->Jumlah_Item }})</option>
                  @endforeach
                </select>
                <input type="number" name="jumlah_digunakan[]" placeholder="Jumlah" style="width:100px;">
              </div>
            </div>
            <button type="button" onclick="addBahanRow()" class="btn-yellow" style="margin-top:5px;">+ Tambah Bahan</button>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <a href="#" class="modal-cancel" onclick="closeModal('addModal')">Kembali</a>
        <button type="submit" class="btn-green">Tambah</button>
      </div>
    </form>
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

<!-- Modal Pembayaran -->
<div id="paymentModal" class="modal-overlay" style="display:none">
  <div class="modal-card" style="max-width:700px">
    <h2 class="modal-title" style="text-align:center">Nominal Pembayaran : <span id="nominalBayar">Rp 0</span></h2>
    <hr style="margin:10px 0">

    <div class="modal-body" style="display:flex; flex-direction:column; gap:20px;">
      <!-- Tunai -->
      <div>
        <h3>Tunai</h3>
        <div style="display:flex; flex-wrap:wrap; gap:10px; margin-top:10px;">
          <button class="pay-btn" onclick="setPayment(0)">Uang Pas</button>
          <button class="pay-btn" onclick="setPayment(50000)">Rp 50.000</button>
          <button class="pay-btn" onclick="setPayment(100000)">Rp 100.000</button>
          <button class="pay-btn" onclick="setPayment(25000)">Rp 25.000</button>
          <input id="customPay" type="number" placeholder="Rp Custom" style="flex:1; padding:8px 10px; border:1px solid #ccc; border-radius:8px;">
        </div>
      </div>

      <hr>

      <!-- QRIS -->
      <div>
        <h3>QRIS</h3>
        <div style="display:flex; flex-wrap:wrap; gap:10px; margin-top:10px;">
          <button class="pay-btn">Ovo</button>
          <button class="pay-btn">ShopeePay</button>
          <button class="pay-btn">LinkAja</button>
          <button class="pay-btn">Gopay</button>
        </div>
      </div>
    </div>

    <div class="modal-footer" style="justify-content:space-between">
      <a href="#" class="modal-cancel" onclick="closeModal('paymentModal')">Kembali</a>
      <div style="display:flex; gap:10px;">
        <button class="btn-yellow">Simpan</button>
        <button class="btn-green" onclick="processPayment()">Proses Pembayaran</button>
      </div>
    </div>
  </div>
</div>

<script>
let cart = [];
const pelangganList = document.querySelector('.pelanggan-list');
const totalHargaEl = document.querySelector('.harga-total');

let pressTimer = null;
let isLongPress = false;

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

// ===== CART FUNCTIONS =====
function addToCart(nama, harga) {
  const item = { nama, harga };
  cart.push(item);
  renderCart();
}

function removeFromCart(index) {
  cart.splice(index, 1);
  renderCart();
}

function renderCart() {
  pelangganList.innerHTML = '';
  let total = 0;

  cart.forEach((item, index) => {
    const div = document.createElement('div');
    div.classList.add('pelanggan-item');
    div.style.display = 'flex';
    div.style.justifyContent = 'space-between';
    div.style.alignItems = 'center';
    div.style.marginBottom = '6px';

    div.innerHTML = `
      <div>
        <strong>${item.nama}</strong><br>
        <small>Rp ${item.harga.toLocaleString('id-ID')}</small>
      </div>
      <button 
        onclick="removeFromCart(${index})" 
        style="
          background: none;
          border: none;
          color: red;
          font-weight: bold;
          cursor: pointer;
          font-size: 16px;
        ">❌</button>
    `;

    pelangganList.appendChild(div);
    total += item.harga;
  });

  totalHargaEl.textContent = `Rp ${total.toLocaleString('id-ID')}`;
}

// ===== EVENT PRODUK =====
document.querySelectorAll('.produk-card:not(.add-card)').forEach(card => {
  const nama = card.querySelector('.produk-name').textContent;
  const hargaText = card.querySelector('.produk-price').textContent.replace(/[^\d]/g, '');
  const harga = parseInt(hargaText);

  // START tekan
  card.addEventListener('mousedown', startPress);
  card.addEventListener('touchstart', startPress);

  // STOP tekan
  card.addEventListener('mouseup', endPress);
  card.addEventListener('mouseleave', endPress);
  card.addEventListener('touchend', endPress);
  card.addEventListener('touchcancel', endPress);

  // Klik biasa
  card.addEventListener('click', () => {
    if (!isLongPress) {
      addToCart(nama, harga); // klik cepat → tambah ke pelanggan
    }
  });

  function startPress() {
    isLongPress = false;
    pressTimer = setTimeout(() => {
      isLongPress = true;
      openModal('editModal'); // tahan 2 detik → buka edit
    }, 2000);
  }

  function endPress() {
    clearTimeout(pressTimer);
  }
});

// ss //
function setPayment(amount) {
  if (amount > 0) {
    document.getElementById('customPay').value = amount;
  }
}

// Update nominal otomatis saat modal dibuka
function openModal(id) {
  document.getElementById(id).style.display = 'flex';
  if (id === 'paymentModal') {
    const totalText = document.querySelector('.harga-total').textContent;
    document.getElementById('nominalBayar').textContent = totalText;
  }
}

// Fungsi proses pembayaran
function processPayment() {
  const totalText = document.querySelector('.harga-total').textContent.replace(/[^\d]/g, '');
  const total = parseInt(totalText);
  const customPay = document.getElementById('customPay').value;
  const payAmount = parseInt(customPay) || 0;

  if (payAmount >= total && cart.length > 0) {
    // Kirim ke backend Laravel
    fetch('{{ route("transaksi.store") }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      body: JSON.stringify({
        items: cart.map(c => ({ id: c.id || null, qty: 1 })),
        total: total,
        metode: 'Tunai'
      })
    })
    .then(res => res.json())
    .then(data => {
      if (data.status === 'success') {
        alert("✅ Pembayaran Berhasil!\nID Transaksi: " + data.id_transaksi);
        cart = [];
        renderCart();
        closeModal('paymentModal');
      } else {
        alert("❌ Terjadi kesalahan: " + data.message);
      }
    })
    .catch(err => {
      console.error(err);
      alert("❌ Gagal menyimpan transaksi!");
    });
  } else {
    alert("❌ Pembayaran gagal, nominal kurang atau keranjang kosong!");
  }
}

function filterProduk() {
  const keyword = document.getElementById("searchProduk").value.toLowerCase().trim();
  const cards = document.querySelectorAll(".produk-card");

  cards.forEach(card => {
    // Abaikan card tambah (+)
    if (card.classList.contains("add-card")) {
      card.style.display = "block";
      return;
    }

    const nama = card.dataset.nama ? card.dataset.nama.toLowerCase() : "";

    if (!keyword || nama.includes(keyword)) {
      card.style.display = "block"; 
    } else {
      card.style.display = "none";
    }
  });
}


</script>

@endsection
