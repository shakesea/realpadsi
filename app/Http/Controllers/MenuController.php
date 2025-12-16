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

        // ================= TENTUKAN ID MENU =================
        // Jika datang dari modal import, kita pakai ID_Menu dari form (harus sama dengan CSV)
        // Jika tidak ada, baru generate otomatis MNxxx
        $menuId = $request->input('ID_Menu');
        if (empty($menuId)) {
            $lastMenu = DB::table('Menu')
                ->selectRaw("CAST(SUBSTRING(ID_Menu, 3) AS UNSIGNED) as num")
                ->orderByDesc('num')
                ->first();
            $lastNum = $lastMenu ? $lastMenu->num : 0;
            $menuId = 'MN' . str_pad($lastNum + 1, 3, '0', STR_PAD_LEFT);
        } else {
            // Pastikan ID_Menu belum terpakai
            if (Menu::where('ID_Menu', $menuId)->exists()) {
                return redirect()->back()->with('flash_error', '❌ ID Menu sudah digunakan.');
            }
        }

        // ================= SIMPAN MENU =================
        $menu = new Menu();
        $menu->ID_Menu = $menuId;
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
            $lastBP = DB::table('Bahan_Penyusun')
                ->selectRaw("CAST(SUBSTRING(ID_Penyusun, 3) AS UNSIGNED) as num")
                ->orderByDesc('num')
                ->first();
            $lastNumBP = $lastBP ? $lastBP->num : 0;

            foreach ($request->bahan as $i => $idBarang) {
                if (empty($idBarang)) continue;

                $jumlah = $request->jumlah_digunakan[$i] ?? 0;
                if ($jumlah <= 0) continue;

                $lastNumBP++;
                $newBP = 'BP' . str_pad($lastNumBP, 3, '0', STR_PAD_LEFT);

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

        // Jika datang dari proses import, lanjutkan import
        if ($request->boolean('continue_import')) {
            // Bersihkan autofill agar tidak muncul lagi
            session()->forget('autoFillMenu');

            return redirect()->route('laporan.continue-import')
                ->with('flash_success', '✅ Menu berhasil dibuat. Import dilanjutkan.');
        }

        return redirect()->back()->with('flash_success', '✅ Menu berhasil ditambahkan!');
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
                $lastBP = DB::table('Bahan_Penyusun')
                    ->selectRaw("CAST(SUBSTRING(ID_Penyusun, 3) AS UNSIGNED) as num")
                    ->orderByDesc('num')
                    ->first();
                $lastNum = $lastBP ? $lastBP->num : 0;

                foreach ($request->bahan as $i => $idBarang) {
                    if (empty($idBarang)) continue;

                    $jumlah = $request->jumlah_digunakan[$i] ?? 0;
                    if ($jumlah <= 0) continue;

                    $lastNum++;
                    $newId = 'BP' . str_pad($lastNum, 3, '0', STR_PAD_LEFT);

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
