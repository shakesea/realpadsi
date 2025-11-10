<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Stok;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StokController extends Controller
{
    // Tampilkan Data Stok
    public function index()
    {
        $stokData = Stok::orderBy('ID_Barang')->get();
        return view('stok', compact('stokData'));
    }

    // Form Tambah
    public function create()
    {
        return view('tambahstok');
    }

    // Simpan Data
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => ['required', 'regex:/^[a-zA-Z0-9\s]+$/'],
            'jumlah' => ['required', 'numeric', 'min:0'],
            'kategori' => ['required', 'regex:/^[a-zA-Z0-9\s,]+$/'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', 'Perubahan Gagal di Simpan. Data Tidak Valid atau Kosong')->withInput();
        }

        $last = Stok::orderBy('ID_Barang', 'desc')->first();
        $lastNumber = $last ? intval(substr($last->ID_Barang, 4)) : 0;
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

    // Form Edit
    public function edit($id)
    {
        $stokItem = Stok::where('ID_Barang', $id)->firstOrFail();
        return view('editstok', compact('stokItem'));
    }

    // Update Data
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nama' => ['required', 'regex:/^[a-zA-Z0-9\s]+$/'],
            'jumlah' => ['required', 'numeric', 'min:0'],
            'kategori' => ['required', 'regex:/^[a-zA-Z0-9\s,]+$/'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', 'Perubahan Gagal di Simpan. Data Tidak Valid atau Kosong')->withInput();
        }

        $stokItem = Stok::where('ID_Barang', $id)->firstOrFail();
        $stokItem->update([
            'Nama' => $request->nama,
            'Jumlah_Item' => $request->jumlah,
            'Kategori' => $request->kategori,
            'Updated_At' => now(),
        ]);

        return redirect()->route('stok.index')->with('success', 'Data stok berhasil diperbarui!');
    }

    // Hapus Data
    public function destroy($id)
    {
        $stokItem = Stok::where('ID_Barang', $id)->firstOrFail();
        $stokItem->delete();

        return redirect()->route('stok.index')->with('success', 'Data stok berhasil dihapus!');
    }
}
