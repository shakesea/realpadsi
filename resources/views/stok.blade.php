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
                    <th>Satuan</th>
                    <th>Tindakan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($stokData as $item)
                <tr>
                    <td>{{ $item['nama'] }}</td>
                    <td>{{ $item['jumlah'] }}</td>
                    <td>{{ $item['satuan'] }}</td>
                    <td class="aksi-btns">
                        {{-- Tombol Edit --}}
                        <a href="{{ route('stok.edit', $item['id']) }}" class="btn-edit">
                            <i class="fa fa-pen"></i> Edit
                        </a>

                        {{-- Tombol Hapus --}}
                        <form action="{{ route('stok.destroy', $item['id']) }}" method="POST" 
                              onsubmit="return confirm('Hapus {{ $item['nama'] }}?')" 
                              style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-delete">
                                <i class="fa fa-trash"></i> Hapus
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
