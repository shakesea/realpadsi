<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\Stok;
use App\Models\BahanPenyusun;
use Illuminate\Support\Facades\DB;

class KasirController extends Controller
{
    // 1️⃣ TAMPILKAN HALAMAN KASIR
    public function index(Request $request)
    {
        $kategori = $request->get('kategori');
        $menus = Menu::when($kategori, function ($query, $kategori) {
            return $query->where('Kategori', $kategori);
        })->orderBy('Kategori')->get();

        $stok = Stok::all();
        $categories = \App\Helpers\MenuHelper::getCategories();

        return view('kasir', compact('menus', 'stok', 'categories', 'kategori'));
    }

    // 2️⃣ TAMBAH PRODUK
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

        DB::beginTransaction();
        try {
            // --- Simpan foto ---
            $fotoData = $request->hasFile('Foto')
                ? file_get_contents($request->file('Foto')->getRealPath())
                : null;

            // --- Generate ID MENU ---
            $lastMenu = Menu::orderBy('ID_Menu', 'desc')->first();
            $lastNum = $lastMenu ? intval(substr($lastMenu->ID_Menu, 4)) : 0;
            $newId = 'MENU' . str_pad($lastNum + 1, 3, '0', STR_PAD_LEFT);

            // --- Simpan ke tabel Menu ---
            Menu::create([
                'ID_Menu' => $newId,
                'Nama' => $request->Nama,
                'Harga' => $request->Harga,
                'Kategori' => $request->Kategori,
                'Foto' => $fotoData,
                'Created_At' => now(),
                'Updated_At' => now(),
            ]);

            // --- Simpan ke tabel Bahan_Penyusun ---
            if ($request->has('bahan') && is_array($request->bahan)) {
                $lastBP = BahanPenyusun::orderBy('ID_Penyusun', 'desc')->first();
                $lastNumBP = $lastBP ? intval(substr($lastBP->ID_Penyusun, 2)) : 0;

                foreach ($request->bahan as $i => $idBarang) {
                    if (!$idBarang) continue;

                    $idPenyusun = 'BP' . str_pad(++$lastNumBP, 3, '0', STR_PAD_LEFT);

                    BahanPenyusun::create([
                        'ID_Penyusun' => $idPenyusun,
                        'ID_Menu' => $newId,
                        'ID_Barang' => $idBarang,
                        'Jumlah_Digunakan' => $request->jumlah_digunakan[$i] ?? 1,
                        'Kategori' => $request->Kategori,
                        'Created_At' => now(),
                        'Updated_At' => now(),
                    ]);
                }
            }

            DB::commit();
            return redirect()->back()->with('success', '✅ Produk & bahan penyusun berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', '❌ Gagal menambah produk: ' . $e->getMessage());
        }
    }

    // 3️⃣ EDIT PRODUK
    public function update(Request $request, $id)
    {
        $request->validate([
            'Nama' => 'required|string|max:100',
            'Harga' => 'required|numeric',
            'Kategori' => 'required|string|max:100',
            'Deskripsi' => 'nullable|string',
            'Foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'bahan' => 'nullable|array',
            'jumlah_digunakan' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            $menu = Menu::findOrFail($id);

            if ($request->hasFile('Foto')) {
                $menu->Foto = file_get_contents($request->file('Foto')->getRealPath());
            }

            $menu->Nama = $request->Nama;
            $menu->Harga = $request->Harga;
            $menu->Kategori = $request->Kategori;
            $menu->Deskripsi = $request->Deskripsi;
            $menu->Updated_At = now();
            $menu->save();

            // Update bahan penyusun
            if ($request->has('bahan')) {
                // Delete existing bahan
                BahanPenyusun::where('ID_Menu', $id)->delete();

                // Get last ID_Penyusun
                $lastBP = BahanPenyusun::orderBy('ID_Penyusun', 'desc')->first();
                $lastNumBP = $lastBP ? intval(substr($lastBP->ID_Penyusun, 2)) : 0;

                // Add new bahan
                foreach ($request->bahan as $i => $idBarang) {
                    if (!$idBarang) continue;

                    $idPenyusun = 'BP' . str_pad(++$lastNumBP, 3, '0', STR_PAD_LEFT);

                    BahanPenyusun::create([
                        'ID_Penyusun' => $idPenyusun,
                        'ID_Menu' => $id,
                        'ID_Barang' => $idBarang,
                        'Jumlah_Digunakan' => $request->jumlah_digunakan[$i] ?? 1,
                        'Kategori' => $request->Kategori,
                        'Created_At' => now(),
                        'Updated_At' => now(),
                    ]);
                }
            }

            DB::commit();
            return redirect()->back()->with('success', '✅ Produk berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', '❌ Gagal memperbarui produk: ' . $e->getMessage());
        }
    }

    // 4️⃣ HAPUS PRODUK
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            BahanPenyusun::where('ID_Menu', $id)->delete();
            Menu::where('ID_Menu', $id)->delete();
            DB::commit();
            return redirect()->back()->with('success', '🗑️ Produk berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', '❌ Gagal menghapus produk!');
        }
    }
}
