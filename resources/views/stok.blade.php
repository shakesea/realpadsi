@extends('layouts.main')
@section('title', 'NutaPOS - Stok')
@section('content')

<x-flash />

<div class="stok-container">
    <div class="stok-header">
    <div class="stok-date">{{ now()->format('d M Y') }}</div>

    <div style="display: flex; gap: 10px;">
        <a href="{{ route('stok.export.pdf', request()->query()) }}" 
           class="btn-export">
           Export PDF
        </a>

        <a href="{{ route('stok.create') }}" class="btn-add">
            Buat Stok +
        </a>
    </div>
</div>


    <!-- WRAPPER UTAMA: SIDEBAR FILTER + TABEL -->
    <div class="stok-layout">

        <!-- ========== SIDEBAR FILTER ========== -->
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
                    <a href="{{ route('stok.index', ['status' => 'Aman']) }}"
                       class="{{ request('status') == 'Aman' ? 'active-filter' : '' }}">
                       Aman
                    </a>
                </li>

                <li>
                    <a href="{{ route('stok.index', ['status' => 'Menipis']) }}"
                       class="{{ request('status') == 'Menipis' ? 'active-filter' : '' }}">
                       Menipis
                    </a>
                </li>

                <li>
                    <a href="{{ route('stok.index', ['status' => 'Habis']) }}"
                       class="{{ request('status') == 'Habis' ? 'active-filter' : '' }}">
                       Habis
                    </a>
                </li>
            </ul>
        </aside>

        <!-- ========== TABEL STOK ========== -->
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

                        <!-- STATUS -->
                        <td>
                            @if ($item->Status === 'Aman')
                                <span class="status-green">Aman</span>
                            @elseif ($item->Status === 'Menipis')
                                <span class="status-yellow">Menipis</span>
                            @else
                                <span class="status-red">Habis</span>
                            @endif
                        </td>

                        <!-- AKSI -->
                        <td class="aksi-btns">
                            <a href="{{ route('stok.edit', $item->ID_Barang) }}" class="btn-edit">Edit</a>

                            <form action="{{ route('stok.destroy', $item->ID_Barang) }}" method="POST"
                                  onsubmit="return confirm('Hapus {{ $item->Nama }}?')" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-delete">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div> <!-- end stok-table-wrap -->

    </div> <!-- end stok-layout -->

</div> <!-- end stok-container -->

@endsection
