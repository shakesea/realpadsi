<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Menu;
use App\Models\BahanPenyusun;
use App\Models\Stok;
use App\Helpers\MenuHelper;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::all();
        $stok = Stok::all();
        $categories = MenuHelper::getCategories();

        return view('menu.index', compact('menus', 'stok', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'Nama' => 'required|string|max:100',
            'Harga' => 'required|numeric',
            'Kategori' => 'required|string|max:100',
            'Deskripsi' => 'nullable|string',
            'Foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'bahan' => 'nullable|array',
            'jumlah_digunakan' => 'nullable|array',
            'jumlah_digunakan.*' => 'nullable|numeric|min:1',
        ]);

        // --- Generate ID otomatis atau gunakan ID dari import ---
        if ($request->has('ID_Menu') && !empty($request->ID_Menu)) {
            // Gunakan ID dari import (CSV)
            $newId = $request->ID_Menu;
        } else {
            // Generate ID baru otomatis dengan pengecekan duplikasi
            $lastMenu = Menu::orderBy('ID_Menu', 'desc')->first();
            $lastNum = $lastMenu ? intval(substr($lastMenu->ID_Menu, 4)) : 0;

            // Cari ID yang belum digunakan
            do {
                $lastNum++;
                $newId = 'MENU' . $lastNum;
            } while (Menu::where('ID_Menu', $newId)->exists());
        }

        $menu = new Menu();
        $menu->ID_Menu = $newId;
        $menu->Nama = $request->Nama;
        $menu->Harga = $request->Harga;
        $menu->Kategori = $request->Kategori;
        $menu->Deskripsi = $request->Deskripsi;

        if ($request->hasFile('Foto')) {
            $menu->Foto = file_get_contents($request->file('Foto')->path());
        }

        $menu->Created_At = now();
        $menu->Updated_At = now();
        $menu->save();

        // --- Simpan bahan penyusun ---
        if ($request->has('bahan') && $request->has('jumlah_digunakan')) {
            $lastBP = BahanPenyusun::orderBy('ID_Penyusun', 'desc')->first();
            $lastNumBP = $lastBP ? intval(substr($lastBP->ID_Penyusun, 2)) : 0;

            foreach ($request->bahan as $index => $bahanId) {
                // Skip jika bahan kosong atau jumlah tidak ada/kosong
                if (empty($bahanId) || empty($request->jumlah_digunakan[$index])) {
                    continue;
                }

                $jumlahDigunakan = $request->jumlah_digunakan[$index];

                $idPenyusun = 'BP' . str_pad(++$lastNumBP, 3, '0', STR_PAD_LEFT);

                BahanPenyusun::create([
                    'ID_Penyusun' => $idPenyusun,
                    'ID_Menu' => $menu->ID_Menu,
                    'ID_Barang' => $bahanId,
                    'Jumlah_Digunakan' => $jumlahDigunakan,
                    'Kategori' => $request->Kategori,
                    'Created_At' => now(),
                    'Updated_At' => now(),
                ]);
            }
        }

        // JIKA INI DARI PROSES IMPORT, REDIRECT KE CONTINUE IMPORT
        if ($request->has('continue_import') && $request->continue_import == '1') {
            return redirect()->route('laporan.continue-import')
                ->with('success', '✅ Menu berhasil dibuat! Melanjutkan import...');
        }

        return redirect()->back()->with('success', '✅ Menu berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'Nama' => 'required|string|max:100',
            'Harga' => 'required|numeric',
            'Kategori' => 'required|string|max:100',
            'Deskripsi' => 'nullable|string',
            'Foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'bahan' => 'array',
            'jumlah_digunakan' => 'array'
        ]);

        DB::beginTransaction();
        try {
            // 🔹 1. Update data menu
            $menu = Menu::findOrFail($id);
            $menu->Nama = $request->Nama;
            $menu->Harga = $request->Harga;
            $menu->Kategori = $request->Kategori;
            $menu->Deskripsi = $request->Deskripsi;

            if ($request->hasFile('Foto')) {
                $menu->Foto = file_get_contents($request->file('Foto')->getRealPath());
            }

            $menu->save();

            // 🔹 2. Hapus semua bahan lama
            BahanPenyusun::where('ID_Menu', $id)->delete();

            // 🔹 3. Tambahkan bahan baru dari form
            if ($request->has('bahan') && is_array($request->bahan)) {
                // Ambil ID terakhir untuk generate ID baru
                $lastBP = BahanPenyusun::orderBy('ID_Penyusun', 'desc')->first();
                $lastNum = $lastBP ? intval(substr($lastBP->ID_Penyusun, 2)) : 0;

                foreach ($request->bahan as $i => $idBarang) {
                    // Skip jika bahan kosong
                    if (empty($idBarang)) continue;

                    $jumlah = $request->jumlah_digunakan[$i] ?? 1;

                    // Skip jika jumlah kosong atau tidak valid
                    if (empty($jumlah) || $jumlah <= 0) continue;

                    // Generate ID baru dengan pengecekan duplikasi
                    do {
                        $lastNum++;
                        $newId = 'BP' . $lastNum;
                    } while (BahanPenyusun::where('ID_Penyusun', $newId)->exists());

                    BahanPenyusun::create([
                        'ID_Penyusun' => $newId,
                        'ID_Menu' => $id,
                        'ID_Barang' => $idBarang,
                        'Jumlah_Digunakan' => $jumlah,
                        'Kategori' => $menu->Kategori,
                        'Created_At' => now(),
                        'Updated_At' => now()
                    ]);
                }
            }

            DB::commit();
            return redirect()->back()->with('success', '✅ Menu dan bahan penyusun berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', '❌ Gagal memperbarui: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $menu = Menu::findOrFail($id);
        BahanPenyusun::where('ID_Menu', $id)->delete(); // Remove bahan penyusun first
        $menu->delete();

        return response()->json(['status' => 'success']);
    }

    public function getBahanPenyusun($id)
    {
        $bahan = DB::table('Bahan_Penyusun')
            ->join('Stok', 'Bahan_Penyusun.ID_Barang', '=', 'Stok.ID_Barang')
            ->select('Bahan_Penyusun.ID_Barang', 'Stok.Nama', 'Bahan_Penyusun.Jumlah_Digunakan')
            ->where('Bahan_Penyusun.ID_Menu', $id)
            ->get();

        return response()->json($bahan);
    }
}
