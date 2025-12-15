@extends('layouts.main')
@section('title', 'NutaPOS - Stok')
@section('content')

<x-flash />

<div class="stok-container">
    <div class="stok-header">
        <div class="stok-date">{{ now()->format('d M Y') }}</div>

        <div style="display:flex; gap:10px;">
            <a href="{{ route('stok.export.pdf', request()->query()) }}" class="btn-export">
                Export PDF
            </a>

            <a href="{{ route('stok.create') }}" class="btn-add">
                Buat Stok +
            </a>
        </div>
    </div>

    <!-- ================= LAYOUT ================= -->
    <div class="stok-layout">

        <!-- ===== SIDEBAR FILTER ===== -->
        <aside class="stok-filter">
            <h4>Filter Status</h4>
            <ul>
                <li>
                    <a href="{{ route('stok.index') }}"
                       class="{{ request('status') ? '' : 'active-filter' }}">
                        Semua
                    </a>
                </li>
                <li>
                    <a href="{{ route('stok.index', ['status'=>'Aman']) }}"
                       class="{{ request('status')=='Aman' ? 'active-filter' : '' }}">
                        Aman
                    </a>
                </li>
                <li>
                    <a href="{{ route('stok.index', ['status'=>'Menipis']) }}"
                       class="{{ request('status')=='Menipis' ? 'active-filter' : '' }}">
                        Menipis
                    </a>
                </li>
                <li>
                    <a href="{{ route('stok.index', ['status'=>'Habis']) }}"
                       class="{{ request('status')=='Habis' ? 'active-filter' : '' }}">
                        Habis
                    </a>
                </li>
            </ul>
        </aside>

        <!-- ===== TABEL STOK ===== -->
        <div class="stok-table-wrap">
            <table class="stok-table">
                <thead>
                    <tr>
                        <th>Nama Item</th>
                        <th>Jumlah</th>
                        <th>Kategori</th>
                        <th>Status</th>
                        <th>Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($stokData as $item)
                    <tr>
                        <td>{{ $item->Nama }}</td>
                        <td>{{ $item->Jumlah_Item }}</td>
                        <td>{{ $item->Kategori }}</td>
                        <td>
                            @if ($item->Status === 'Aman')
                                <span class="status-green">Aman</span>
                            @elseif ($item->Status === 'Menipis')
                                <span class="status-yellow">Menipis</span>
                            @else
                                <span class="status-red">Habis</span>
                            @endif
                        </td>
                        <td class="aksi-btns">
                            <a href="{{ route('stok.edit', $item->ID_Barang) }}" class="btn-edit">
                                Edit
                            </a>

                            <!-- 🔴 HAPUS (PAKAI MODAL) -->
                            <button
                                type="button"
                                class="btn-delete"
                                onclick="openDeleteStokModal(
                                    '{{ route('stok.destroy', $item->ID_Barang) }}',
                                    '{{ $item->Nama }}'
                                )">
                                Hapus
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
</div>

<!-- ================================================= -->
<!-- 🔴 MODAL HAPUS STOK -->
<!-- ================================================= -->
<div id="deleteStokModal" class="modal-overlay" style="display:none;">
    <div class="modal-card delete-modal">
        <h2 class="delete-title">Hapus Stok</h2>

        <form id="deleteStokForm" method="POST">
            @csrf
            @method('DELETE')

            <p class="delete-text">
                Apakah Anda yakin ingin menghapus
                <strong id="deleteStokName"></strong>?
            </p>

            <div class="modal-footer delete-footer">
                <button type="button" class="btn-gray" onclick="closeDeleteStokModal()">
                    Kembali
                </button>
                <button type="submit" class="btn-red">
                    Hapus
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= SCRIPT MODAL ================= -->
<script>
    function openDeleteStokModal(actionUrl, nama) {
        document.getElementById('deleteStokModal').style.display = 'flex';
        document.getElementById('deleteStokName').innerText = nama;
        document.getElementById('deleteStokForm').action = actionUrl;
    }

    function closeDeleteStokModal() {
        document.getElementById('deleteStokModal').style.display = 'none';
    }
</script>
{{-- ================= FLASH AUTO FADE (STOK PAGE) ================= --}}
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const flash = document.querySelector('.flash-alert');
    if (!flash) return;

    // tampil lebih lama (7 detik)
    setTimeout(() => {
      flash.style.animation = 'flashFadeOut 0.6s ease forwards';
      setTimeout(() => flash.remove(), 700);
    }, 7000);
  });
</script>
{{-- =============================================================== --}}

@endsection
