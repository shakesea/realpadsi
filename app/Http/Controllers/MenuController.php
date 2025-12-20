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
        // ================= VALIDASI DASAR =================
        try {
            $rules = [
                'Nama' => 'required|string|max:100',
                'Harga' => 'required|numeric',
                'Kategori' => 'required|string|max:100',
                'Deskripsi' => 'nullable|string',
                'Foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ];

            // Jika BUKAN dari import, wajibkan bahan penyusun
            if (!$request->boolean('continue_import')) {
                $rules['bahan'] = 'required|array|min:1';
                $rules['bahan.*'] = 'required|string';
                $rules['jumlah_digunakan'] = 'required|array|min:1';
                $rules['jumlah_digunakan.*'] = 'required|numeric|min:1';
            } else {
                // Jika dari import, bahan opsional
                $rules['bahan'] = 'nullable|array';
                $rules['bahan.*'] = 'nullable|string';
                $rules['jumlah_digunakan'] = 'nullable|array';
                $rules['jumlah_digunakan.*'] = 'nullable|numeric|min:1';
            }

            $request->validate($rules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withInput()
                ->with('flash_error', '❌ Data menu tidak valid. Pastikan Anda telah mengisi nama, harga, kategori, dan minimal 1 bahan penyusun.');
        }

        // ================= VALIDASI BAHAN PENYUSUN (Harus Ada Minimal 1 untuk Create Normal) =================
        $validBahan = collect($request->bahan ?? [])
            ->filter(function ($bahan, $index) use ($request) {
                $jumlah = $request->jumlah_digunakan[$index] ?? 0;
                return !empty($bahan) && $jumlah > 0;
            });

        if (!$request->boolean('continue_import') && $validBahan->isEmpty()) {
            return redirect()->back()
                ->withInput()
                ->with('flash_error', '❌ Menu harus memiliki minimal 1 bahan penyusun dengan jumlah yang valid.');
        }

        // ================= CEK DUPLIKASI BAHAN PENYUSUN (untuk create normal) =================
        if (!$request->boolean('continue_import')) {
            $filteredBahan = $validBahan->values()->all();
            $dupeMap = array_count_values($filteredBahan);
            $hasDuplicateBahan = collect($dupeMap)->filter(function ($count) {
                return $count > 1;
            })->isNotEmpty();
            if ($hasDuplicateBahan) {
                return redirect()->back()
                    ->withInput()
                    ->with('flash_error', '❌ Bahan penyusun tidak boleh duplikat. Mohon hapus item yang sama.');
            }
        }

        // ================= CEK DUPLIKAT MENU =================
        // Izinkan nama yang sama saat proses lanjut-import, karena ID_Menu harus mengikuti CSV
        if (!$request->boolean('continue_import') && Menu::where('Nama', $request->Nama)->exists()) {
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

        // Clear cache kategori agar filter kategori baru langsung muncul
        \Illuminate\Support\Facades\Cache::forget('menu_categories');

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
        // Debug: Log request data
        \Log::info("=== UPDATE MENU {$id} ===");
        \Log::info("Request bahan: " . json_encode($request->input('bahan')));
        \Log::info("Request jumlah: " . json_encode($request->input('jumlah_digunakan')));

        DB::beginTransaction();
        try {
            // ================= VALIDASI =================
            $request->validate([
                'Nama' => 'required|string|max:100',
                'Harga' => 'required|numeric',
                'Kategori' => 'required|string|max:100',
                'Deskripsi' => 'nullable|string',
                'Foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'bahan' => 'required|array|min:1',
                'bahan.*' => 'required|string',
                'jumlah_digunakan' => 'required|array|min:1',
                'jumlah_digunakan.*' => 'required|numeric|min:1',
            ]);

            // Pastikan nama menu tidak duplikat dengan menu lain
            $isDuplicateName = Menu::where('Nama', $request->Nama)
                ->where('ID_Menu', '!=', $id)
                ->exists();

            if ($isDuplicateName) {
                return redirect()->back()
                    ->withInput()
                    ->with('flash_error', '❌ Menu sudah ada, silakan gunakan nama lain.');
            }

            // ================= VALIDASI BAHAN PENYUSUN (Harus Ada Minimal 1) =================
            $validBahan = collect($request->bahan ?? [])
                ->filter(function ($bahan, $index) use ($request) {
                    $jumlah = $request->jumlah_digunakan[$index] ?? 0;
                    return !empty($bahan) && $jumlah > 0;
                });

            if ($validBahan->isEmpty()) {
                return redirect()->back()
                    ->withInput()
                    ->with('flash_error', '❌ Menu harus memiliki minimal 1 bahan penyusun dengan jumlah yang valid.');
            }

            // ================= CEK DUPLIKASI BAHAN PENYUSUN (untuk edit) =================
            $filteredBahan = $validBahan->values()->all();
            $dupeMap = array_count_values($filteredBahan);
            $hasDuplicateBahan = collect($dupeMap)->filter(function ($count) {
                return $count > 1;
            })->isNotEmpty();
            if ($hasDuplicateBahan) {
                return redirect()->back()
                    ->withInput()
                    ->with('flash_error', '❌ Bahan penyusun tidak boleh duplikat. Mohon hapus item yang sama.');
            }

            $menu = Menu::findOrFail($id);

            $menu->Nama = $request->Nama;
            $menu->Harga = $request->Harga;
            $menu->Kategori = $request->Kategori;
            $menu->Deskripsi = $request->Deskripsi;

            if ($request->hasFile('Foto')) {
                $menu->Foto = file_get_contents($request->file('Foto')->getRealPath());
            }

            $menu->save();

            // Hapus bahan lama
            BahanPenyusun::where('ID_Menu', $id)->delete();

            // Tambahkan bahan baru
            if ($request->has('bahan') && is_array($request->bahan)) {
                $lastBP = DB::table('Bahan_Penyusun')
                    ->selectRaw("CAST(SUBSTRING(ID_Penyusun, 3) AS UNSIGNED) as num")
                    ->orderByDesc('num')
                    ->first();
                $lastNum = $lastBP ? $lastBP->num : 0;

                $savedCount = 0;
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
                    $savedCount++;
                }

                // Log untuk debugging
                \Log::info("Menu {$id} updated with {$savedCount} bahan penyusun");
            }

            DB::commit();

            // Clear cache kategori agar filter kategori baru langsung muncul
            \Illuminate\Support\Facades\Cache::forget('menu_categories');

            return redirect()->back()
                ->with('flash_success', '✅ Menu berhasil diperbarui!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('flash_error', '❌ Data menu tidak valid. Pastikan Anda telah mengisi nama, harga, kategori, dan minimal 1 bahan penyusun.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error updating menu {$id}: " . $e->getMessage());
            return redirect()->back()
                ->with('flash_error', '❌ Gagal memperbarui menu: ' . $e->getMessage());
        }
    }

    public function getBahanPenyusun($id)
    {
        $bahan = BahanPenyusun::where('ID_Menu', $id)
            ->join('Stok', 'Bahan_Penyusun.ID_Barang', '=', 'Stok.ID_Barang')
            ->select('Bahan_Penyusun.*', 'Stok.Nama as Nama_Barang')
            ->get();

        return response()->json($bahan);
    }

    public function destroy($id)
    {
        $menu = Menu::findOrFail($id);
        BahanPenyusun::where('ID_Menu', $id)->delete();
        $menu->delete();

        // Clear cache kategori agar perubahan langsung terlihat
        \Illuminate\Support\Facades\Cache::forget('menu_categories');

        return response()->json(['status' => 'success']);
    }
}
