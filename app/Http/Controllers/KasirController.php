<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KasirController extends Controller
{
    public function index()
    {
        // menampilkan halaman utama kasir
        return view('kasir');
    }

    public function store(Request $request)
    {
        // untuk sementara hanya demo
        return back()->with('success', 'Produk berhasil ditambahkan (demo)');
    }

    public function update(Request $request, $id)
    {
        // untuk sementara hanya demo
        return back()->with('success', 'Produk berhasil diupdate (demo)');
    }

    public function destroy($id)
    {
        // untuk sementara hanya demo
        return back()->with('success', 'Produk berhasil dihapus (demo)');
    }
}
