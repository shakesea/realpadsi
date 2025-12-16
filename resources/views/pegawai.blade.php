@extends('layouts.main')

@section('title', 'NutaPOS - Pegawai')

@section('content')
@if ($errors->any())
<div class="flash-error">
    @foreach ($errors->all() as $error)
    <p>{{ $error }}</p>
    @endforeach
</div>
@endif

@if (session('success'))
<div class="flash-success">
    {{ session('success') }}
</div>
@endif

<div class="pegawai-container">
    <div class="pegawai-card">
        <div class="pilih-pelayan">
            <h1 class="pegawai-title">Pilih Pelayan</h1>

            <!-- Form pencarian -->
            <form method="GET" action="{{ route('pegawai.index') }}" class="pegawai-search">
                <svg xmlns="http://www.w3.org/2000/svg" class="search-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1116.65 6.65a7.5 7.5 0 010 10.6z" />
                </svg>
                <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Cari Pelayan...">
            </form>
        </div>

        <!-- Form Pegawai -->
        <div class="pegawai-list">
            @forelse ($pegawai as $p)
            <div class="pegawai-item">
                <div class="pegawai-left">
                    <div class="pegawai-avatar">{{ strtoupper(substr($p->Username, 0, 2)) }}</div>
                    <span class="pegawai-name">{{ $p->Username }}</span>
                    <span class="pegawai-role">({{ $p->ID_Role }})</span>
                </div>

                <div class="pegawai-actions">
                    <!-- Tombol delete (TANPA confirm bawaan) -->
                    <form method="POST" action="{{ route('pegawai.destroy', $p->ID) }}" class="form-delete">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="pegawai-btn delete" title="Hapus">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12M9 7V5a1 1 0 011-1h4a1 1 0 011 1v2m1 0v12a2 2 0 01-2 2H8a2 2 0 01-2-2V7z" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <p style="text-align:center;">Tidak ada pegawai ditemukan.</p>
            @endforelse
        </div>

        <a href="{{ route('pegawai.create') }}" class="pegawai-add">+ Buat Baru</a>
    </div>
</div>

<!-- ========================== -->
<!-- MODAL KONFIRMASI HAPUS     -->
<!-- ========================== -->
<style>
    .pegawai-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    .pegawai-modal-box {
        background: #fff;
        padding: 30px 25px;
        width: 340px;
        border-radius: 12px;
        text-align: center;
        box-shadow: 0 5px 25px rgba(0, 0, 0, 0.3);
    }

    .pegawai-modal-title {
        font-size: 20px;
        font-weight: 700;
        margin-bottom: 10px;
        color: #333;
    }

    .pegawai-modal-text {
        font-size: 15px;
        color: #555;
        margin-bottom: 20px;
    }

    .pegawai-modal-buttons {
        display: flex;
        justify-content: center;
        gap: 15px;
    }

    .pegawai-modal-btn-cancel {
        background: #ddd;
        padding: 10px 20px;
        border-radius: 8px;
        border: none;
        font-weight: 600;
        cursor: pointer;
        font-size: 14px;
    }

    .pegawai-modal-btn-cancel:hover {
        background: #ccc;
    }

    .pegawai-modal-btn-delete {
        background: #dc3545;
        color: #fff;
        padding: 10px 20px;
        border-radius: 8px;
        border: none;
        font-weight: 600;
        cursor: pointer;
        font-size: 14px;
    }

    .pegawai-modal-btn-delete:hover {
        background: #c82333;
    }
</style>

<div id="deleteModal" class="pegawai-modal-overlay">
    <div class="pegawai-modal-box">
        <h2 class="pegawai-modal-title">Hapus Pegawai?</h2>
        <p id="deleteText" class="pegawai-modal-text">Anda yakin ingin menghapus pegawai ini?</p>

        <div class="pegawai-modal-buttons">
            <button id="cancelDelete" class="pegawai-modal-btn-cancel">Batal</button>
            <button id="confirmDelete" class="pegawai-modal-btn-delete">Hapus</button>
        </div>
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const flashMessages = document.querySelectorAll(".flash-success, .flash-error");
        flashMessages.forEach(msg => {
            setTimeout(() => {
                msg.style.opacity = "0";
                setTimeout(() => msg.remove(), 500);
            }, 3000);
        });
    });
</script>
<!-- SCRIPT untuk modal hapus -->
<script>
    document.addEventListener("DOMContentLoaded", () => {
        let targetForm = null;

        document.querySelectorAll(".form-delete").forEach(form => {
            form.addEventListener("submit", function(e) {
                e.preventDefault();
                targetForm = this;

                const name = this.closest(".pegawai-item")
                    .querySelector(".pegawai-name").textContent;

                document.getElementById("deleteText").textContent =
                    "Yakin ingin menghapus pegawai \"" + name + "\"?";

                document.getElementById("deleteModal").style.display = "flex";
            });
        });

        document.getElementById("cancelDelete").addEventListener("click", () => {
            document.getElementById("deleteModal").style.display = "none";
            targetForm = null;
        });

        document.getElementById("confirmDelete").addEventListener("click", () => {
            if (targetForm) targetForm.submit();
        });
    });
</script>

@endsection