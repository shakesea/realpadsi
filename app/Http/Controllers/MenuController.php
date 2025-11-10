<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
        $menu = new Menu();
        $menu->Nama = $request->Nama;
        $menu->Harga = $request->Harga;
        $menu->Kategori = $request->Kategori;
        $menu->Deskripsi = $request->Deskripsi;

        if ($request->hasFile('Foto')) {
            $menu->Foto = file_get_contents($request->file('Foto')->path());
        }

        $menu->save();

        // Handle bahan penyusun
        if ($request->has('bahan') && $request->has('jumlah_digunakan')) {
            foreach ($request->bahan as $index => $bahanId) {
                if (!empty($bahanId)) {
                    $jumlahDigunakan = $request->jumlah_digunakan[$index];
                    BahanPenyusun::create([
                        'ID_Menu' => $menu->ID_Menu,
                        'ID_Barang' => $bahanId,
                        'Jumlah_Item' => $jumlahDigunakan
                    ]);
                }
            }
        }

        return redirect()->back()->with('success', 'Menu berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $menu = Menu::findOrFail($id);
        $menu->Nama = $request->Nama;
        $menu->Harga = $request->Harga;
        $menu->Kategori = $request->Kategori;
        $menu->Deskripsi = $request->Deskripsi;

        if ($request->hasFile('Foto')) {
            $menu->Foto = file_get_contents($request->file('Foto')->path());
        }

        $menu->save();

        // Update bahan penyusun
        BahanPenyusun::where('ID_Menu', $id)->delete(); // Remove existing ingredients
        if ($request->has('bahan') && $request->has('jumlah_digunakan')) {
            foreach ($request->bahan as $index => $bahanId) {
                if (!empty($bahanId)) {
                    $jumlahDigunakan = $request->jumlah_digunakan[$index];
                    BahanPenyusun::create([
                        'ID_Menu' => $menu->ID_Menu,
                        'ID_Barang' => $bahanId,
                        'Jumlah_Item' => $jumlahDigunakan
                    ]);
                }
            }
        }

        return redirect()->back()->with('success', 'Menu berhasil diperbarui!');
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
        $bahanPenyusun = BahanPenyusun::where('ID_Menu', $id)
            ->join('stok', 'bahan_penyusun.ID_Barang', '=', 'stok.ID_Barang')
            ->select('bahan_penyusun.*', 'stok.Nama')
            ->get();

        return response()->json($bahanPenyusun);
    }
}
