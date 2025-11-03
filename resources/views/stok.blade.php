@extends('layouts.main')

@section('title', 'NutaPOS - Stok')

@section('content')
<div class="stok-container">
    <div class="stok-header">
        <div class="stok-date">{{ now()->format('d M Y') }}</div>
        <a href="{{ route('stok.create') }}" class="btn-add">Buat Stok +</a>
    </div>

    <div class="stok-table-wrap">
        <table class="stok-table">
            <thead>
                <tr>
                    <th>Nama Item</th>
                    <th>Jumlah</th>
                    <th>Kategori</th>
                    <th>Tindakan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($stokData as $item)
                <tr>
                    <td>{{ $item->Nama }}</td>
                    <td>{{ $item->Jumlah_Item }}</td>
                    <td>{{ $item->Kategori }}</td>
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
    </div>
</div>
@endsection
