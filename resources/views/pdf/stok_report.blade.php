<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Stok Barang</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }
        h2, p {
            text-align: center;
            margin: 0;
            padding: 0;
        }
        .subtitle {
            margin-top: 4px;
            margin-bottom: 10px;
            font-size: 11px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            font-size: 11px;
        }
        th {
            background: #f0f0f0;
            font-weight: bold;
        }
        .right {
            text-align: right;
        }
        .center {
            text-align: center;
        }
    </style>
</head>
<body>

    <h2>Laporan Stok Barang</h2>
    <p class="subtitle">
        Tanggal cetak: {{ now()->format('d M Y') }}
        @if (!empty($filter))
            | Filter status: {{ $filter }}
        @else
            | Filter status: Semua
        @endif
    </p>

    <table>
        <thead>
            <tr>
                <th style="width: 15%;">ID Barang</th>
                <th style="width: 30%;">Nama</th>
                <th style="width: 10%;" class="center">Jumlah</th>
                <th style="width: 25%;">Kategori</th>
                <th style="width: 20%;" class="center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($stokData as $item)
                <tr>
                    <td>{{ $item->ID_Barang }}</td>
                    <td>{{ $item->Nama }}</td>
                    <td class="center">{{ $item->Jumlah_Item }}</td>
                    <td>{{ $item->Kategori }}</td>
                    <td class="center">{{ $item->Status }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="center">
                        Tidak ada data stok untuk filter ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
