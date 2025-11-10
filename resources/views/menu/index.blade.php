@extends('layouts.main')
@section('title', 'NutaPOS - Menu')

@section('content')

<div class="menu-container">
    <!-- Filter dan Pencarian -->
    <div class="menu-header">
        <div class="menu-search">
            <input type="text" placeholder="Cari menu..." id="searchMenu" onkeyup="filterMenu()">
        </div>
        <div class="menu-filter">
            @foreach($categories as $category)
            <button class="filter-btn" data-category="{{ $category }}">{{ $category }}</button>
            @endforeach
        </div>
        <button class="btn-green" onclick="openModal('addModal')">
            <i class="fas fa-plus"></i> Tambah Menu
        </button>
    </div>

    <!-- Grid Menu -->
    <div class="menu-grid">
        @foreach($menus as $menu)
        <div class="menu-card"
            data-id="{{ $menu->ID_Menu }}"
            data-nama="{{ $menu->Nama }}"
            data-harga="{{ $menu->Harga }}"
            data-kategori="{{ $menu->Kategori }}"
            data-deskripsi="{{ $menu->Deskripsi }}">
            <div class="menu-image">
                <img src="{{ $menu->Foto ? 'data:image/jpeg;base64,'.base64_encode($menu->Foto) : asset('img/sample-product.png') }}"
                    alt="{{ $menu->Nama }}">
            </div>
            <div class="menu-info">
                <h3>{{ $menu->Nama }}</h3>
                <p class="menu-category">{{ $menu->Kategori }}</p>
                <p class="menu-price">Rp {{ number_format($menu->Harga, 0, ',', '.') }}</p>
                <p class="menu-desc">{{ $menu->Deskripsi ?: 'Tidak ada deskripsi' }}</p>
                <div class="menu-actions">
                    <button class="btn-yellow" onclick="editMenu({{ $menu->ID_Menu }})">Edit</button>
                    <button class="btn-red" onclick="deleteMenu({{ $menu->ID_Menu }})">Hapus</button>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- Modal Tambah Menu -->
<div id="addModal" class="modal-overlay" style="display:none">
    <div class="modal-card">
        <h2 class="modal-title">Tambah Menu</h2>
        <form action="{{ route('menu.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-body">
                <div class="form-left">
                    <label for="foto-upload" class="foto-box" id="add-preview-box">
                        <span id="add-preview-text">Pilih Foto</span>
                        <img id="add-preview-img" style="display:none;width:100%;height:100%;object-fit:cover;border-radius:10px;">
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
                        <select name="Kategori" required>
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($categories as $category)
                            <option value="{{ $category }}">{{ $category }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea name="Deskripsi" rows="3"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Bahan Penyusun</label>
                        <div id="bahan-container">
                            <div class="bahan-row">
                                <select name="bahan[]" class="bahan-select" required>
                                    <option value="">-- Pilih Bahan --</option>
                                    @foreach($stok as $item)
                                    <option value="{{ $item->ID_Barang }}">{{ $item->Nama }} ({{ $item->Jumlah_Item }})</option>
                                    @endforeach
                                </select>
                                <input type="number" name="jumlah_digunakan[]" placeholder="Jumlah">
                                <button type="button" class="btn-remove-bahan">&times;</button>
                            </div>
                        </div>
                        <button type="button" class="btn-add-bahan">+ Tambah Bahan</button>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-grey" onclick="closeModal('addModal')">Batal</button>
                <button type="submit" class="btn-green">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Menu -->
<div id="editModal" class="modal-overlay" style="display:none">
    <div class="modal-card">
        <h2 class="modal-title">Edit Menu</h2>
        <form id="editForm" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-left">
                    <label for="edit-foto" class="foto-box">
                        <span id="edit-preview-text">Pilih Foto</span>
                        <img id="edit-preview-img" style="width:100%;height:100%;object-fit:cover;border-radius:10px;">
                    </label>
                    <input type="file" name="Foto" id="edit-foto" accept="image/*" style="display:none"
                        onchange="preview('edit-preview-img','edit-preview-text',event)">
                </div>

                <div class="form-right">
                    <div class="form-group">
                        <label>Nama</label>
                        <input type="text" name="Nama" id="editNama" required>
                    </div>

                    <div class="form-group">
                        <label>Harga (Rp)</label>
                        <input type="number" name="Harga" id="editHarga" required>
                    </div>

                    <div class="form-group">
                        <label>Kategori</label>
                        <select name="Kategori" id="editKategori" required>
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($categories as $category)
                            <option value="{{ $category }}">{{ $category }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea name="Deskripsi" id="editDeskripsi" rows="3"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Bahan Penyusun</label>
                        <div id="edit-bahan-container">
                            <!-- Akan diisi secara dinamis -->
                        </div>
                        <button type="button" class="btn-add-bahan" onclick="addEditBahanRow()">+ Tambah Bahan</button>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-grey" onclick="closeModal('editModal')">Batal</button>
                <button type="submit" class="btn-green">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Preview fungsi untuk gambar
        window.preview = function(imgId, textId, e) {
            const file = e.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById(imgId);
                img.src = e.target.result;
                img.style.display = 'block';
                if (textId) {
                    document.getElementById(textId).style.display = 'none';
                }
            }
            reader.readAsDataURL(file);
        }

        // Fungsi Modal
        window.openModal = function(id) {
            document.getElementById(id).style.display = 'flex';
        }

        window.closeModal = function(id) {
            document.getElementById(id).style.display = 'none';
        }

        // Fungsi Filter Menu
        window.filterMenu = function() {
            const keyword = document.getElementById('searchMenu').value.toLowerCase();
            const cards = document.querySelectorAll('.menu-card');

            cards.forEach(card => {
                const nama = card.dataset.nama.toLowerCase();
                const kategori = card.dataset.kategori.toLowerCase();
                const match = nama.includes(keyword) || kategori.includes(keyword);
                card.style.display = match ? 'flex' : 'none';
            });
        }

        // Filter berdasarkan kategori
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const category = this.dataset.category;
                const cards = document.querySelectorAll('.menu-card');

                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                if (category === 'all') {
                    cards.forEach(card => card.style.display = 'flex');
                } else {
                    cards.forEach(card => {
                        card.style.display = card.dataset.kategori === category ? 'flex' : 'none';
                    });
                }
            });
        });

        // Fungsi Edit Menu
        window.editMenu = async function(id) {
            const card = document.querySelector(`.menu-card[data-id="${id}"]`);
            if (!card) return;

            // Set form values
            document.getElementById('editNama').value = card.dataset.nama;
            document.getElementById('editHarga').value = card.dataset.harga;
            document.getElementById('editKategori').value = card.dataset.kategori;
            document.getElementById('editDeskripsi').value = card.dataset.deskripsi;
            document.getElementById('edit-preview-img').src = card.querySelector('img').src;
            document.getElementById('editForm').action = `/menu/${id}`;

            // Get bahan penyusun
            try {
                const response = await fetch(`/menu/${id}/bahan`);
                const bahan = await response.json();
                const container = document.getElementById('edit-bahan-container');
                container.innerHTML = '';

                if (bahan.length === 0) {
                    addEditBahanRow();
                } else {
                    bahan.forEach(b => {
                        const row = createBahanRow(b.ID_Barang, b.Jumlah_Item);
                        container.appendChild(row);
                    });
                }
            } catch (error) {
                console.error('Error fetching bahan:', error);
                addEditBahanRow();
            }

            openModal('editModal');
        }

        // Fungsi Delete Menu
        window.deleteMenu = function(id) {
            if (confirm('Yakin ingin menghapus menu ini?')) {
                fetch(`/menu/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            const card = document.querySelector(`.menu-card[data-id="${id}"]`);
                            card.remove();
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }
        }

        // Fungsi Bahan Penyusun
        function createBahanRow(selectedId = '', jumlah = '') {
            const row = document.createElement('div');
            row.className = 'bahan-row';
            row.innerHTML = `
      <select name="bahan[]" class="bahan-select" required>
        <option value="">-- Pilih Bahan --</option>
        @foreach($stok as $item)
          <option value="{{ $item->ID_Barang }}"${selectedId == {{ $item->ID_Barang }} ? ' selected' : ''}>
            {{ $item->Nama }} ({{ $item->Jumlah_Item }})
          </option>
        @endforeach
      </select>
      <input type="number" name="jumlah_digunakan[]" placeholder="Jumlah" value="${jumlah}">
      <button type="button" class="btn-remove-bahan" onclick="this.parentElement.remove()">&times;</button>
    `;
            return row;
        }

        window.addEditBahanRow = function() {
            const container = document.getElementById('edit-bahan-container');
            container.appendChild(createBahanRow());
        }

        // Add event listener untuk tambah bahan di form tambah
        document.querySelector('.btn-add-bahan').addEventListener('click', function() {
            const container = document.getElementById('bahan-container');
            container.appendChild(createBahanRow());
        });

        // Handle remove bahan button clicks
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-remove-bahan')) {
                e.target.closest('.bahan-row').remove();
            }
        });
    });
</script>

<style>
    .menu-container {
        padding: 20px;
    }

    .menu-header {
        display: flex;
        align-items: center;
        gap: 20px;
        margin-bottom: 20px;
    }

    .menu-search {
        flex: 1;
    }

    .menu-search input {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
    }

    .menu-filter {
        display: flex;
        gap: 10px;
    }

    .filter-btn {
        padding: 8px 15px;
        border: 1px solid #ddd;
        border-radius: 5px;
        background: white;
        cursor: pointer;
    }

    .filter-btn.active {
        background: #4CAF50;
        color: white;
        border-color: #4CAF50;
    }

    .menu-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
    }

    .menu-card {
        display: flex;
        flex-direction: column;
        border: 1px solid #ddd;
        border-radius: 10px;
        overflow: hidden;
        background: white;
    }

    .menu-image {
        height: 200px;
        overflow: hidden;
    }

    .menu-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .menu-info {
        padding: 15px;
    }

    .menu-info h3 {
        margin: 0 0 5px;
        font-size: 1.2em;
    }

    .menu-category {
        color: #666;
        font-size: 0.9em;
        margin: 0 0 5px;
    }

    .menu-price {
        font-weight: bold;
        color: #4CAF50;
        margin: 0 0 10px;
    }

    .menu-desc {
        font-size: 0.9em;
        color: #666;
        margin: 0 0 15px;
    }

    .menu-actions {
        display: flex;
        gap: 10px;
    }

    /* Modal styles */
    .modal-card {
        max-width: 800px;
    }

    .modal-body {
        display: grid;
        grid-template-columns: 300px 1fr;
        gap: 20px;
    }

    .form-left {
        width: 100%;
    }

    .foto-box {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 300px;
        border: 2px dashed #ddd;
        border-radius: 10px;
        cursor: pointer;
    }

    .form-right {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .form-group label {
        font-weight: bold;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 5px;
    }

    .bahan-row {
        display: flex;
        gap: 10px;
        margin-bottom: 10px;
    }

    .bahan-select {
        flex: 1;
    }

    .btn-remove-bahan {
        padding: 0 10px;
        background: #ff4444;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    .btn-add-bahan {
        padding: 8px;
        background: #4CAF50;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    /* Button styles */
    .btn-green {
        padding: 8px 15px;
        background: #4CAF50;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    .btn-yellow {
        padding: 8px 15px;
        background: #FFC107;
        color: black;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    .btn-red {
        padding: 8px 15px;
        background: #f44336;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    .btn-grey {
        padding: 8px 15px;
        background: #9e9e9e;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }
</style>

@endsection