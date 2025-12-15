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
        // ================= VALIDASI =================
        try {
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
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->with('flash_error', '❌ Data menu tidak valid, periksa kembali input.');
        }

        // ================= CEK DUPLIKAT MENU =================
        if (Menu::where('Nama', $request->Nama)->exists()) {
            return redirect()->back()
                ->with('flash_error', '❌ Menu sudah ada, silakan gunakan nama lain.');
        }

        // ================= GENERATE ID MENU =================
        $lastMenu = Menu::orderBy('ID_Menu', 'desc')->first();
        $lastNum = $lastMenu ? intval(substr($lastMenu->ID_Menu, 4)) : 0;

        do {
            $lastNum++;
            $newId = 'MENU' . $lastNum;
        } while (Menu::where('ID_Menu', $newId)->exists());

        // ================= SIMPAN MENU =================
        $menu = new Menu();
        $menu->ID_Menu = $newId;
        $menu->Nama = $request->Nama;
        $menu->Harga = $request->Harga;
        $menu->Kategori = $request->Kategori;
        $menu->Deskripsi = $request->Deskripsi;

        if ($request->hasFile('Foto')) {
            $menu->Foto = file_get_contents($request->file('Foto')->getRealPath());
        }

        $menu->Created_At = now();
        $menu->Updated_At = now();
        $menu->save();

        // ================= SIMPAN BAHAN =================
        if ($request->has('bahan') && $request->has('jumlah_digunakan')) {
            $lastBP = BahanPenyusun::orderBy('ID_Penyusun', 'desc')->first();
            $lastNumBP = $lastBP ? intval(substr($lastBP->ID_Penyusun, 2)) : 0;

            foreach ($request->bahan as $i => $idBarang) {
                if (empty($idBarang)) continue;

                $jumlah = $request->jumlah_digunakan[$i] ?? 0;
                if ($jumlah <= 0) continue;

                do {
                    $lastNumBP++;
                    $newBP = 'BP' . $lastNumBP;
                } while (BahanPenyusun::where('ID_Penyusun', $newBP)->exists());

                BahanPenyusun::create([
                    'ID_Penyusun' => $newBP,
                    'ID_Menu' => $menu->ID_Menu,
                    'ID_Barang' => $idBarang,
                    'Jumlah_Digunakan' => $jumlah,
                    'Kategori' => $request->Kategori,
                    'Created_At' => now(),
                    'Updated_At' => now(),
                ]);
            }
        }

        return redirect()->back()
            ->with('flash_success', '✅ Menu berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $menu = Menu::findOrFail($id);

            $menu->Nama = $request->Nama;
            $menu->Harga = $request->Harga;
            $menu->Kategori = $request->Kategori;
            $menu->Deskripsi = $request->Deskripsi;

            if ($request->hasFile('Foto')) {
                $menu->Foto = file_get_contents($request->file('Foto')->getRealPath());
            }

            $menu->save();

            BahanPenyusun::where('ID_Menu', $id)->delete();

            if ($request->has('bahan')) {
                $lastBP = BahanPenyusun::orderBy('ID_Penyusun', 'desc')->first();
                $lastNum = $lastBP ? intval(substr($lastBP->ID_Penyusun, 2)) : 0;

                foreach ($request->bahan as $i => $idBarang) {
                    if (empty($idBarang)) continue;

                    $jumlah = $request->jumlah_digunakan[$i] ?? 0;
                    if ($jumlah <= 0) continue;

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
                        'Updated_At' => now(),
                    ]);
                }
            }

            DB::commit();
            return redirect()->back()
                ->with('flash_success', '✅ Menu berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('flash_error', '❌ Gagal memperbarui menu.');
        }
    }

    public function destroy($id)
    {
        $menu = Menu::findOrFail($id);
        BahanPenyusun::where('ID_Menu', $id)->delete();
        $menu->delete();

        return response()->json(['status' => 'success']);
    }
}
