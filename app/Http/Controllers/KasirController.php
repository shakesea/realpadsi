<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Menu;

class KasirController extends Controller
{
    // Menampilkan halaman kasir
    public function index()
    {
        $menus = Menu::orderBy('Kategori')->get();
        return view('kasir', compact('menus'));
    }

    // Menyimpan produk baru dari modal Tambah Produk
    public function store(Request $request)
    {
        $request->validate([
            'Nama' => 'required|string|max:100',
            'Harga' => 'required|numeric',
            'Kategori' => 'required|string|max:100',
            'Foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Simpan foto sebagai BLOB
        $fotoData = null;
        if ($request->hasFile('Foto')) {
            $fotoData = file_get_contents($request->file('Foto')->getRealPath());
        }

        // Buat ID unik (contoh: MENU031)
        $newId = 'MENU' . str_pad(Menu::count() + 1, 3, '0', STR_PAD_LEFT);

        Menu::create([
            'ID_Menu' => $newId,
            'Nama' => $request->Nama,
            'Harga' => $request->Harga,
            'Kategori' => $request->Kategori,
            'Foto' => $fotoData,
            'Created_At' => now(),
            'Updated_At' => now(),
        ]);

        return redirect()->back()->with('success', '✅ Produk baru berhasil ditambahkan!');
    }
}
