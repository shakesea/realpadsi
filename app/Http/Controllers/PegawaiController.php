<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pegawai;

class PegawaiController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));

        // Ambil data dari database Hostinger
        $pegawai = Pegawai::when($q !== '', function ($query) use ($q) {
            $query->where('Username', 'like', "%{$q}%")
                  ->orWhere('ID_Pegawai', 'like', "%{$q}%");
        })->orderBy('ID_Pegawai')->get();

        return view('pegawai', ['pegawai' => $pegawai, 'q' => $q]);
    }

    public function destroy($id)
    {
        Pegawai::where('ID_Pegawai', $id)->delete();
        return back()->with('ok', 'Pegawai berhasil dihapus!');
    }

    public function create()
    {
        return view('tambahpegawai');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ID_Pegawai' => 'required|string|max:8|unique:Pegawai,ID_Pegawai',
            'ID_Role' => 'required|string|max:8',
            'Username' => 'required|string|max:30',
            'Password' => 'required|string|max:30',
        ]);

        Pegawai::create($validated);

        return redirect()->route('pegawai.index')
            ->with('success', 'Pegawai baru berhasil ditambahkan!');
    }
}
