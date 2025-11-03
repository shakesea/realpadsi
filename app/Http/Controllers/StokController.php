<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Stok;
use Illuminate\Support\Facades\DB;

class StokController extends Controller
{
    // SKPL-SPSFC-005-04: Tampil Detail Stok
    public function index()
    {
        $stokData = Stok::orderBy('ID_Barang')->get();
        return view('stok', compact('stokData'));
    }

    // SKPL-SPSFC-005-01: Tambah Informasi Stok
    public function create()
    {
        return view('tambahstok');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'jumlah' => 'required|numeric|min:0',
            'kategori' => 'required|string|max:50',
        ]);

        // Generate ID baru (STOK##)
        $last = Stok::orderBy('ID_Barang', 'desc')->first();
        if ($last) {
            // Ambil 2 digit terakhir dari ID lama
            $lastNumber = intval(substr($last->ID_Barang, 4)); // bukan 5!
        } else {
            $lastNumber = 0;
        }

        // Naikkan 1 angka dari ID terakhir
        $newId = 'STOK' . str_pad($lastNumber + 1, 2, '0', STR_PAD_LEFT);

        Stok::create([
            'ID_Barang' => $newId,
            'Nama' => $request->nama,
            'Jumlah_Item' => $request->jumlah,
            'Kategori' => $request->kategori,
            'Created_At' => now(),
            'Updated_At' => now(),
        ]);
        return redirect()->route('stok.index')->with('success', 'Stok baru berhasil ditambahkan!');
    }

    // SKPL-SPSFC-005-02: Ubah Informasi Stok
    public function edit($id)
    {
        $stokItem = Stok::where('ID_Barang', $id)->firstOrFail();
        return view('editstok', compact('stokItem'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'jumlah' => 'required|numeric|min:0',
            'kategori' => 'required|string|max:50',
        ]);

        $stokItem = Stok::where('ID_Barang', $id)->firstOrFail();
        $stokItem->update([
            'Nama' => $request->nama,
            'Jumlah_Item' => $request->jumlah,
            'Kategori' => $request->kategori,
            'Updated_At' => now(),
        ]);

        return redirect()->route('stok.index')->with('success', 'Data stok berhasil diperbarui!');
    }

    // SKPL-SPSFC-005-03: Hapus Informasi Stok
    public function destroy($id)
    {
        $stokItem = Stok::where('ID_Barang', $id)->firstOrFail();
        $stokItem->delete();

        return redirect()->route('stok.index')->with('success', 'Data stok berhasil dihapus!');
    }
}
