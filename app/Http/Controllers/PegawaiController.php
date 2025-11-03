<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PegawaiController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));

        $pegawai = collect([
            ['id'=>1,'nama'=>'Fy Ivan 2','email'=>'ivan@example.com','telp'=>'0812-1111-2222'],
            ['id'=>2,'nama'=>'Zamir','email'=>'zamir@example.com','telp'=>'0812-3333-4444'],
            ['id'=>3,'nama'=>'Sisil','email'=>'sisil@example.com','telp'=>'0812-5555-6666'],
        ])->when($q !== '', fn($c) =>
            $c->filter(fn($p) => str_contains(strtolower($p['nama']), strtolower($q)))
        )->values();

        return view('pegawai', ['pegawai' => $pegawai, 'q' => $q]);
    }

    public function destroy($id)
    {
        return back()->with('ok', 'Data dihapus (demo)');
    }
    public function create()
    {
        // Menampilkan halaman form tambah pegawai
        return view('tambahpegawai');
    }

    public function store(Request $request)
    {
        // Validasi input dari form
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email',
            'telp' => 'required|string|max:20',
            'tanggal_lahir' => 'required|date',
            'alamat' => 'required|string|max:500',
        ]);

        // (sementara) tampilkan data untuk memastikan sudah masuk
        // dd($validated); // <-- aktifkan ini dulu untuk testing, nanti bisa hapus

        // Contoh kalau nanti sudah pakai database:
        // Pegawai::create($validated);

        // Redirect kembali ke daftar pegawai dengan pesan sukses
        return redirect()->route('pegawai.index')->with('success', 'Pegawai berhasil ditambahkan!');
    }

}
