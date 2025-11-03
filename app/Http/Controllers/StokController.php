<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StokController extends Controller
{
    private $stokData = [
        ['id' => 1, 'nama' => 'Abc Juice', 'jumlah' => 30, 'satuan' => 'PCS'],
        ['id' => 2, 'nama' => 'Additional Oat', 'jumlah' => 32, 'satuan' => 'PCS'],
        ['id' => 3, 'nama' => 'Aglio Olio With Cauliflower', 'jumlah' => 5, 'satuan' => 'PCS'],
        ['id' => 4, 'nama' => 'Air Putih', 'jumlah' => 86, 'satuan' => 'PCS'],
        ['id' => 5, 'nama' => 'Americano', 'jumlah' => 23, 'satuan' => 'PCS'],
        ['id' => 6, 'nama' => 'Asam Jawa', 'jumlah' => 44, 'satuan' => 'PCS'],
    ];

    public function index()
    {
        $stokData = $this->stokData;
        return view('stok', compact('stokData'));
    }

    public function create()
    {
        return view('tambahstok');
    }

    public function store(Request $request)
    {
        return redirect()->route('stok.index')->with('success', 'Stok baru berhasil ditambahkan (dummy)');
    }

    public function edit($id)
    {
        $stokItem = collect($this->stokData)->firstWhere('id', (int)$id);

        if (!$stokItem) {
            abort(404, "Data stok tidak ditemukan");
        }
        return view('editstok', compact('stokItem'));
    }

    public function update(Request $request, $id)
    {
        return redirect()->route('stok.index')->with('success', 'Stok berhasil diperbarui (dummy)');
    }

    public function destroy($id)
    {
        return redirect()->route('stok.index')->with('success', 'Stok berhasil dihapus (dummy)');
    }
}
