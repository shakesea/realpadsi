@extends('layouts.main')

@section('title', 'Menu Baru Ditemukan dari Import')

@section('content')

{{-- Flash Messages --}}
@if(session('flash_success'))
<div class="flash-alert flash-success">{{ session('flash_success') }}</div>
@endif

@if(session('flash_error'))
<div class="flash-alert flash-error">{{ session('flash_error') }}</div>
@endif

@if($errors->any())
<div class="flash-alert flash-error">
    @foreach($errors->all() as $error)
    {{ $error }}<br>
    @endforeach
</div>
@endif

<div class="modal-overlay" style="display:flex;">

    <style>
        /* Flash alerts */
        .flash-alert {
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-weight: 500;
            animation: fadeIn 0.3s ease-in-out;
        }

        .flash-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .flash-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-5px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Scroll container: only the form body should scroll */
        .modal-scroll {
            max-height: calc(85vh - 170px);
            /* leave space for header + info + footer */
            overflow-y: auto;
            padding-right: 8px;
            /* avoid content hidden under scrollbar */
        }

        /* Responsive form layout */
        .modal-body {
            display: flex;
            gap: 20px;
            align-items: flex-start;
        }

        .form-left {
            flex: 0 0 220px;
            max-width: 220px;
        }

        .form-right {
            flex: 1 1 360px;
            min-width: 260px;
        }

        @media (max-width: 800px) {
            .modal-card {
                width: min(95%, 700px);
            }

            .modal-body {
                flex-direction: column;
            }

            .form-left,
            .form-right {
                max-width: 100%;
                flex: 1 1 100%;
            }
        }

        /* nicer scrollbar for the scroll container */
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

        /* fallback for firefox */
        .modal-scroll {
            scrollbar-width: thin;
            scrollbar-color: #bbb #f5f5f5;
        }
    </style>

    <div class="modal-card"
        style="max-width:700px; width:min(95%,700px); display:flex; flex-direction:column;
               height:auto; max-height:85vh; background:white; border-radius:12px;">

        <!-- HEADER -->
        <h2 class="modal-title" style="flex-shrink:0; padding:16px; margin:0;">
            ⚠️ Menu Belum Ada — Mohon Lengkapi Data Menu
        </h2>

        <form action="{{ route('menu.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <input type="hidden" name="ID_Menu" value="{{ $autoFill['id_menu'] }}">
            <input type="hidden" name="continue_import" value="1">

            <!-- SCROLLABLE SECTION -->
            <div class="modal-scroll" style="padding:0 16px 16px 16px;">

                <div class="modal-body" style="display:flex; gap:20px;">

                    <!-- LEFT FOTO -->
                    <div class="form-left">
                        <label class="foto-box" for="foto-upload">
                            <span id="preview-text">Pilih Foto</span>
                            <img id="preview-img" style="display:none;width:100%;border-radius:10px;">
                        </label>
                        <input type="file" name="Foto" id="foto-upload" accept="image/*"
                            style="display:none"
                            onchange="preview('preview-img','preview-text',event)">
                    </div>

                    <!-- RIGHT FORM -->
                    <div class="form-right">

                        <div class="form-group">
                            <label>Nama</label>
                            <input type="text" name="Nama" value="{{ $autoFill['nama'] }}" required>
                        </div>

                        <div class="form-group">
                            <label>Harga (Rp)</label>
                            <input type="number" name="Harga" value="{{ $autoFill['harga'] }}" required>
                        </div>

                        <div class="form-group">
                            <label>Kategori</label>
                            <select id="kategori-select" onchange="toggleKategoriCustom()"
                                style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;">
                                <option value="">-- Pilih Kategori --</option>

                                @foreach($categories as $category)
                                <option value="{{ $category }}"
                                    {{ $autoFill['kategori'] == $category ? 'selected' : '' }}>
                                    {{ $category }}
                                </option>
                                @endforeach

                                <option value="__custom__">+ Tambah Kategori Baru</option>
                            </select>

                            <div id="kategori-custom-container" style="display:none;margin-top:8px;">
                                <input type="text" id="kategori-custom-input"
                                    placeholder="Masukkan kategori baru..."
                                    style="width:100%;padding:10px;border:1px solid #ffc107;border-radius:6px;background:#fffbf0;">
                                <small style="color:#856404;display:block;margin-top:4px;">
                                    💡 Contoh: Dessert, Combo Menu, dll.
                                </small>
                            </div>

                            <input type="hidden" name="Kategori" id="kategori-final"
                                value="{{ $autoFill['kategori'] }}" required>
                        </div>

                        <div class="form-group">
                            <label>Deskripsi</label>
                            <textarea name="Deskripsi" rows="3"></textarea>
                        </div>

                        <div class="form-group">
                            <label>Bahan Penyusun <span style="color:#888;font-weight:normal;">(Wajib)</span></label>
                            <div id="bahan-container">
                                <div class="bahan-row" style="display:flex;gap:10px;margin-bottom:8px;">
                                    <select name="bahan[]" class="bahan-select" style="flex:1;">
                                        <option value="">-- Pilih Bahan --</option>
                                        @foreach ($stok as $item)
                                        <option value="{{ $item->ID_Barang }}">
                                            {{ $item->Nama }} ({{ $item->Jumlah_Item }})
                                        </option>
                                        @endforeach
                                    </select>
                                    <input type="number" name="jumlah_digunakan[]" placeholder="Jumlah" min="1"
                                        style="width:100px;">
                                </div>
                            </div>
                            <button type="button" onclick="addBahanRow()" class="btn-yellow" style="margin-top:5px;">
                                + Tambah Bahan
                            </button>
                        </div>
                    </div>
                </div>

            </div> <!-- modal-scroll -->

            <!-- FOOTER (outside scroll, inside form) -->
            <div style="flex-shrink:0; display:flex; justify-content:flex-end; gap:10px; padding:12px 16px; border-top:1px solid #eee; background:#fff;">
                <a href="{{ route('penjualan.index') }}" class="modal-cancel">❌ Batalkan Import</a>
                <button type="submit" class="btn-green">✅ Simpan Menu & Lanjutkan Import</button>
            </div>

        </form>
    </div>
</div>

<script>
    function toggleKategoriCustom() {
        const select = document.getElementById('kategori-select');
        const custom = document.getElementById('kategori-custom-container');
        const customInput = document.getElementById('kategori-custom-input');
        const final = document.getElementById('kategori-final');

        if (select.value === '__custom__') {
            custom.style.display = 'block';
            customInput.focus();
            final.value = '';
        } else {
            custom.style.display = 'none';
            customInput.value = '';
            final.value = select.value;
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const customInput = document.getElementById('kategori-custom-input');
        const final = document.getElementById('kategori-final');
        const select = document.getElementById('kategori-select');

        customInput.addEventListener('input', () => final.value = customInput.value);

        const exists = [...select.options].some(opt => opt.value === final.value);
        if (!exists && final.value) {
            select.value = '__custom__';
            document.getElementById('kategori-custom-container').style.display = 'block';
            customInput.value = final.value;
        }
    });

    function preview(imgId, textId, e) {
        const file = e.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = () => {
            document.getElementById(imgId).src = reader.result;
            document.getElementById(imgId).style.display = 'block';
            document.getElementById(textId).style.display = 'none';
        };
        reader.readAsDataURL(file);
    }

    function addBahanRow() {
        const container = document.getElementById('bahan-container');
        const row = document.querySelector('.bahan-row').cloneNode(true);
        row.querySelector('select').value = '';
        row.querySelector('input').value = '';
        container.appendChild(row);
    }
</script>

@endsection