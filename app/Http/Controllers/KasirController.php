<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\Stok;
use App\Models\BahanPenyusun;

class KasirController extends Controller
{
    // 🧩 1. TAMPILKAN HALAMAN KASIR
    public function index()
    {
        $menus = Menu::orderBy('Kategori')->get();
        $stok = Stok::all(); // Untuk dropdown bahan penyusun
        return view('kasir', compact('menus', 'stok'));
    }

    // 🧩 2. TAMBAH MENU BARU + SIMPAN BAHAN PENYUSUN
    public function store(Request $request)
    {
        $request->validate([
            'Nama' => 'required|string|max:100',
            'Harga' => 'required|numeric',
            'Kategori' => 'required|string|max:100',
            'Foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'bahan' => 'nullable|array',
            'jumlah_digunakan' => 'nullable|array',
        ]);

        // Simpan foto sebagai BLOB
        $fotoData = null;
        if ($request->hasFile('Foto')) {
            $fotoData = file_get_contents($request->file('Foto')->getRealPath());
        }

        // Generate ID unik (contoh: MENU031)
        $lastMenu = Menu::orderBy('ID_Menu', 'desc')->first();
        $lastNumber = 0;
        if ($lastMenu && preg_match('/(\d+)$/', $lastMenu->ID_Menu, $matches)) {
            $lastNumber = intval($matches[1]);
        }
        $newId = 'MENU' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);

        // Simpan menu baru
        $menu = Menu::create([
            'ID_Menu' => $newId,
            'Nama' => $request->Nama,
            'Harga' => $request->Harga,
            'Kategori' => $request->Kategori,
            'Foto' => $fotoData,
            'Created_At' => now(),
            'Updated_At' => now(),
        ]);

        // Simpan bahan penyusun (jika ada)
        if ($request->has('bahan') && is_array($request->bahan)) {
            $lastPenyusun = BahanPenyusun::orderBy('ID_Penyusun', 'desc')->first();
            $lastNumber = 0;
            if ($lastPenyusun && preg_match('/(\d+)$/', $lastPenyusun->ID_Penyusun, $matches)) {
                $lastNumber = intval($matches[1]);
            }

            foreach ($request->bahan as $i => $idBarang) {
                if (!$idBarang) continue;

                $idPenyusun = 'BP' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
                $lastNumber++;

                BahanPenyusun::create([
                    'ID_Penyusun' => $idPenyusun,
                    'ID_Menu' => $menu->ID_Menu,
                    'ID_Barang' => $idBarang,
                    'Jumlah_Digunakan' => $request->jumlah_digunakan[$i] ?? 1,
                    'Kategori' => $request->Kategori,
                    'Created_At' => now(),
                    'Updated_At' => now(),
                ]);
            }
        }

        return redirect()->back()->with('success', '✅ Produk baru berhasil ditambahkan!');
    }
}
