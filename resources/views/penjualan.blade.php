@extends('layouts.main')
@section('title', 'NutaPOS - Laporan Penjualan')

@section('content')

<x-flash />

<div class="laporan-container">
    <div class="laporan-header">
        {{-- 🔍 Filter Periode --}}
        <form class="laporan-filter" method="GET" action="{{ route('penjualan.index') }}" style="flex-wrap:wrap;gap:8px;">
            <label><strong>Periode:</strong></label>
            <input type="date" name="start" value="{{ request('start', \Carbon\Carbon::parse($start)->format('Y-m-d')) }}">
            <span> - </span>
            <input type="date" name="end" value="{{ request('end', \Carbon\Carbon::parse($end)->format('Y-m-d')) }}">
            <button type="submit" class="btn-filter">Terapkan</button>

            <span style="color:#666;font-size:0.9em;">
                📊 <strong>{{ $totalTransaksi ?? 0 }}</strong> transaksi
            </span>
        </form>

        <div class="laporan-controls">
            <form method="GET" action="{{ route('penjualan.index') }}" id="entries-form">
                <label>Show
                    <select name="entries" onchange="document.getElementById('entries-form').submit()">
                        <option value="10" {{ request('entries') == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('entries') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('entries') == 50 ? 'selected' : '' }}>50</option>
                    </select>
                    Entries</label>
                <input type="hidden" name="start" value="{{ request('start') }}">
                <input type="hidden" name="end" value="{{ request('end') }}">
            </form>

            <span> </span>

            {{-- Tombol Ekspor PDF --}}
            <form action="{{ route('penjualan.index') }}" method="GET" style="display:inline;">
                <input type="hidden" name="export" value="pdf">
                <input type="hidden" name="start" value="{{ request('start', $start) }}">
                <input type="hidden" name="end" value="{{ request('end', $end) }}">
                <button type="submit" class="pdf-link" id="exportPdfPenjualanBtn">Export PDF</button>
            </form>

            {{-- Tombol Import File --}}
            <form id="import-form" action="{{ route('penjualan.import') }}" method="POST" enctype="multipart/form-data" style="display:inline;">
                @csrf
                <input type="file" id="import-file" name="file" accept=".xlsx,.xls,.csv" style="display:none;"
                    onchange="document.getElementById('import-form').submit()">
                <button type="button" class="pdf-link" onclick="document.getElementById('import-file').click()">Import File</button>
            </form>
        </div>
    </div>

    {{-- 📊 Tabel Laporan --}}
    <div class="table-list">
        <table class="laporan-table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Metode Pembayaran</th>
                    <th>Item & Kategori</th>
                    <th>Kasir/Member</th>
                    <th style="text-align:center;">Poin</th>
                    <th style="text-align:right;">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($laporan as $item)
                <tr>
                    <td>
                        <strong>{{ $item['tanggal'] }}</strong><br>
                        <small>{{ $item['waktu'] }}</small><br>
                        <small class="text-muted">#{{ $item['kode'] }}</small>
                    </td>
                    <td>{{ $item['metode'] }}</td>
                    <td>
                        @foreach($item['items'] as $kategori => $data)
                        <div class="kategori-group">
                            <strong class="kategori-title">{{ $kategori }}</strong>
                            <div class="kategori-items">
                                @foreach($data['items'] as $menu)
                                <div class="item-row">
                                    <span class="item-name">{{ $menu['nama'] }}</span>
                                    <span class="item-qty">{{ $menu['qty'] }}x</span>
                                    <span class="item-price">Rp {{ number_format($menu['subtotal'], 0, ',', '.') }}</span>
                                </div>
                                @endforeach
                                <div class="kategori-total">
                                    Total: {{ $data['total_qty'] }} items (Rp {{ number_format($data['total_amount'], 0, ',', '.') }})
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </td>
                    <td>
                        <strong>Kasir:</strong> {{ $item['nama'] }}<br>
                        @if($item['member'])
                        <div class="member-info">
                            <strong>Member:</strong> {{ $item['member']['nama'] }}
                        </div>
                        @endif
                    </td>
                    <td style="text-align:center;">
                        @php
                        $poinDipakai = $item['member']['poin_digunakan'] ?? 0;
                        $poinDidapat = $item['member']['poin_didapat'] ?? 0;
                        @endphp
                        @if($item['member'])
                        <div class="poin-box">
                            @if($poinDipakai > 0)
                            <div class="poin-used">
                                <span class="poin-label">Digunakan:</span>
                                <span class="poin-value">{{ $poinDipakai }} pts</span>
                            </div>
                            @endif
                            @if($poinDidapat > 0)
                            <div class="poin-earned">
                                <span class="poin-label">Didapat:</span>
                                <span class="poin-value">+{{ $poinDidapat }} pts</span>
                            </div>
                            @endif
                            @if($poinDipakai == 0 && $poinDidapat == 0)
                            <span class="text-muted">-</span>
                            @endif
                        </div>
                        @else
                        <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td style="text-align:right;">
                        @php
                        $poinDipakai = $item['member']['poin_digunakan'] ?? 0;
                        $potonganRp = $poinDipakai * 100; // 1 poin = Rp 100
                        $totalBruto = $item['total'];
                        $totalFinal = max($totalBruto - $potonganRp, 0);
                        @endphp

                        @if($poinDipakai > 0)
                        <div class="price-bruto">Rp {{ number_format($totalBruto,0,',','.') }}</div>
                        <div class="price-discount">- Rp {{ number_format($potonganRp,0,',','.') }}</div>
                        <div class="price-final"><strong>Rp {{ number_format($totalFinal,0,',','.') }}</strong></div>
                        @else
                        <strong>Rp {{ number_format($totalFinal, 0, ',', '.') }}</strong>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center; color:#777;">Tidak ada transaksi pada periode ini</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- 🎨 Style --}}
<style>
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

    .btn-filter,
    .pdf-link {
        padding: 6px 15px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        background: #4CAF50;
        color: #fff;
        text-decoration: none;
        transition: background 0.2s ease;
    }

    .btn-filter:hover,
    .pdf-link:hover {
        background: #45a049;
    }

    .laporan-table {
        width: 100%;
        border-collapse: collapse;
    }

    .laporan-table th,
    .laporan-table td {
        border: 1px solid #ddd;
        padding: 10px;
    }

    .laporan-table th {
        background: #f5f5f5;
    }

    .member-info {
        background: #f8f8f8;
        padding: 5px;
        border-radius: 4px;
        margin-top: 5px;
        font-size: 0.9em;
    }

    .item-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 3px;
    }

    /* Styling untuk kolom poin */
    .poin-box {
        display: inline-block;
        text-align: left;
    }

    .poin-used,
    .poin-earned {
        margin: 2px 0;
        font-size: 0.9em;
    }

    .poin-used .poin-label {
        color: #dc3545;
        font-weight: 500;
    }

    .poin-used .poin-value {
        color: #dc3545;
        font-weight: bold;
        margin-left: 4px;
    }

    .poin-earned .poin-label {
        color: #28a745;
        font-weight: 500;
    }

    .poin-earned .poin-value {
        color: #28a745;
        font-weight: bold;
        margin-left: 4px;
    }

    /* Styling untuk kolom total dengan potongan */
    .price-bruto {
        font-size: 0.85em;
        color: #666;
        text-decoration: line-through;
    }

    .price-discount {
        font-size: 0.9em;
        color: #dc3545;
        font-weight: 500;
    }

    .price-final {
        margin-top: 4px;
        font-size: 1.1em;
        color: #28a745;
    }

    .text-muted {
        color: #999;
    }
</style>

<!-- Script buat import file -->
<script>
    document.getElementById('import-file').addEventListener('change', function() {
        document.getElementById('import-form').submit();
    });

    // Biarkan flash success/error tampil lebih lama (10 detik) sebelum hilang
    document.addEventListener('DOMContentLoaded', function() {
        const flash = document.querySelector('.flash-alert');
        if (!flash) return;

        setTimeout(() => {
            flash.style.animation = 'flashFadeOut 0.6s ease forwards';
            setTimeout(() => flash.remove(), 700);
        }, 10000);
    });

    // Export PDF: trigger download via iframe dan tampilkan notifikasi langsung (tanpa refresh)
    document.addEventListener('DOMContentLoaded', function() {
        const exportBtn = document.getElementById('exportPdfPenjualanBtn');
        if (!exportBtn) return;

        exportBtn.addEventListener('click', function(e) {
            e.preventDefault();

            // Feedback instan
            showTempFlash('PDF penjualan berhasil dibuat, unduhan dimulai.', 'success');

            // Fire download via iframe agar tetap di halaman
            const iframe = document.createElement('iframe');
            iframe.style.display = 'none';
            iframe.src = this.form.action + '?' + new URLSearchParams(new FormData(this.form)).toString();
            document.body.appendChild(iframe);
        });
    });

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
</script>

@endsection