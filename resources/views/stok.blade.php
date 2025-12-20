@extends('layouts.main')
@section('title', 'NutaPOS - Stok')
@section('content')

<x-flash />

<div class="stok-container">
    <div class="stok-header">
        <div class="stok-date">{{ now()->format('d M Y') }}</div>

        <div style="display:flex; gap:10px;">
            <a href="{{ route('stok.export.pdf', request()->query()) }}" class="btn-export" id="exportPdfBtn">
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
            <!-- Search Box -->
            <div class="search-box-wrapper">
                <input
                    type="text"
                    id="stokSearch"
                    class="stok-search-input"
                    placeholder="🔍 Cari stok berdasarkan nama, kategori, atau status..."
                    onkeyup="searchStok()">
            </div>

            <table class="stok-table" id="stokTable">
                <thead>
                    <tr>
                        <th onclick="sortTable(0)" style="cursor:pointer;">
                            Nama Item <span class="sort-icon" data-col="0">⇅</span>
                        </th>
                        <th onclick="sortTable(1)" style="cursor:pointer;">
                            Jumlah <span class="sort-icon" data-col="1">⇅</span>
                        </th>
                        <th onclick="sortTable(2)" style="cursor:pointer;">
                            Kategori <span class="sort-icon" data-col="2">⇅</span>
                        </th>
                        <th onclick="sortTable(3)" style="cursor:pointer;">
                            Status <span class="sort-icon" data-col="3">⇅</span>
                        </th>
                        <th>Tindakan</th>
                    </tr>
                </thead>
                <tbody id="stokTableBody">
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

    // ================= SEARCH FUNCTION ================= 
    function searchStok() {
        const input = document.getElementById('stokSearch');
        const filter = input.value.toLowerCase();
        const tbody = document.getElementById('stokTableBody');
        const rows = tbody.getElementsByTagName('tr');

        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            const nama = row.cells[0].textContent.toLowerCase();
            const kategori = row.cells[2].textContent.toLowerCase();
            const status = row.cells[3].textContent.toLowerCase();

            // Search di nama, kategori, atau status
            if (nama.includes(filter) || kategori.includes(filter) || status.includes(filter)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }
    }

    // ================= TABLE SORTING ================= 
    let sortDirections = {}; // Track sort direction for each column
    let currentSortColumn = null;
    let currentSortDirection = null;

    // Column name mapping
    const columnNames = ['Nama', 'Jumlah_Item', 'Kategori', 'Status'];

    function sortTable(columnIndex) {
        const table = document.getElementById('stokTable');
        const tbody = document.getElementById('stokTableBody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const sortIcons = document.querySelectorAll('.sort-icon');

        // Toggle sort direction
        if (!sortDirections[columnIndex]) {
            sortDirections[columnIndex] = 'asc';
        } else {
            sortDirections[columnIndex] = sortDirections[columnIndex] === 'asc' ? 'desc' : 'asc';
        }

        const direction = sortDirections[columnIndex];
        const isNumeric = columnIndex === 1; // Column 1 is "Jumlah" (numeric)

        // Save current sort state
        currentSortColumn = columnNames[columnIndex];
        currentSortDirection = direction;

        // Update export PDF link
        updateExportLink();

        // Sort rows
        rows.sort((a, b) => {
            let aValue = a.cells[columnIndex].textContent.trim();
            let bValue = b.cells[columnIndex].textContent.trim();

            if (isNumeric) {
                aValue = parseFloat(aValue) || 0;
                bValue = parseFloat(bValue) || 0;
            } else {
                aValue = aValue.toLowerCase();
                bValue = bValue.toLowerCase();
            }

            if (direction === 'asc') {
                return aValue > bValue ? 1 : aValue < bValue ? -1 : 0;
            } else {
                return aValue < bValue ? 1 : aValue > bValue ? -1 : 0;
            }
        });

        // Clear and re-append sorted rows
        tbody.innerHTML = '';
        rows.forEach(row => tbody.appendChild(row));

        // Update sort icons
        sortIcons.forEach(icon => {
            if (icon.dataset.col == columnIndex) {
                icon.textContent = direction === 'asc' ? '↑' : '↓';
                icon.style.color = '#1DB954';
            } else {
                icon.textContent = '⇅';
                icon.style.color = '#999';
            }
        });
    }

    // Update export PDF link with current sort parameters
    function updateExportLink() {
        const exportBtn = document.getElementById('exportPdfBtn');
        if (!exportBtn) return;

        const url = new URL(exportBtn.href);

        if (currentSortColumn && currentSortDirection) {
            url.searchParams.set('sortBy', currentSortColumn);
            url.searchParams.set('sortDir', currentSortDirection);
        } else {
            url.searchParams.delete('sortBy');
            url.searchParams.delete('sortDir');
        }

        exportBtn.href = url.toString();
    }
</script>

<style>
    /* Search Box Styling */
    .search-box-wrapper {
        margin-bottom: 15px;
    }

    .stok-search-input {
        width: 100%;
        padding: 12px 20px;
        font-size: 14px;
        border: 2px solid #ddd;
        border-radius: 8px;
        outline: none;
        transition: border-color 0.3s;
    }

    .stok-search-input:focus {
        border-color: #1DB954;
    }

    .stok-search-input::placeholder {
        color: #999;
    }

    /* Sort Icon Styling */
    .sort-icon {
        font-size: 0.9em;
        color: #999;
        margin-left: 5px;
        display: inline-block;
        transition: color 0.2s;
    }

    .stok-table thead th {
        user-select: none;
    }

    .stok-table thead th:hover {
        background-color: #f0f0f0;
    }
</style>
{{-- ================= FLASH HANDLING (STOK PAGE) ================= --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const flash = document.querySelector('.flash-alert');
        if (flash) {
            setTimeout(() => {
                flash.style.animation = 'flashFadeOut 0.6s ease forwards';
                setTimeout(() => flash.remove(), 700);
            }, 7000); // tampil lebih lama (7 detik)
        }

        // Saat tombol export diklik: trigger download via iframe dan tampilkan notifikasi langsung (tanpa refresh)
        const exportBtn = document.getElementById('exportPdfBtn');
        if (exportBtn) {
            exportBtn.addEventListener('click', (e) => {
                e.preventDefault();

                // Tampilkan flash success langsung
                showTempFlash('PDF stok berhasil dibuat, unduhan dimulai.', 'success');

                // Buat iframe tersembunyi untuk memicu download tanpa tinggalkan halaman
                const iframe = document.createElement('iframe');
                iframe.style.display = 'none';
                iframe.src = exportBtn.href;
                document.body.appendChild(iframe);
            });
        }

        function showTempFlash(message, type = 'success') {
            let stack = document.querySelector('.flash-stack');
            if (!stack) {
                stack = document.createElement('div');
                stack.className = 'flash-stack';
                document.body.appendChild(stack);
            }

            const node = document.createElement('div');
            node.className = `flash-alert flash-${type}`;
            node.textContent = message;
            stack.appendChild(node);

            setTimeout(() => {
                node.style.animation = 'flashFadeOut 0.6s ease forwards';
                setTimeout(() => node.remove(), 700);
            }, 10000);
        }
    });
</script>
{{-- =============================================================== --}}

@endsection