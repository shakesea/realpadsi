<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Pegawai;
use App\Models\Finance;
use App\Models\InformasiPegawai;
use Carbon\Carbon;

class PegawaiController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));

        // Ambil data dari tabel Pegawai
        $pegawai = DB::table('Pegawai')
            ->select('ID_Pegawai as ID', 'ID_Role', 'Username', 'Password')
            ->when($q !== '', function ($query) use ($q) {
                $query->where('Username', 'like', "%{$q}%")
                      ->orWhere('ID_Pegawai', 'like', "%{$q}%");
            });

        // Ambil data dari tabel Finance
        $finance = DB::table('Finance')
            ->select('ID_Finance as ID', 'ID_Role', 'Username', 'Password')
            ->when($q !== '', function ($query) use ($q) {
                $query->where('Username', 'like', "%{$q}%")
                      ->orWhere('ID_Finance', 'like', "%{$q}%");
            });

        // Gabungkan hasil (union)
        $data = $pegawai->unionAll($finance)->orderBy('ID')->get();

        return view('pegawai', ['pegawai' => $data, 'q' => $q]);
    }

    public function create()
    {
        return view('tambahpegawai');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:30',
            'email' => 'required|email|max:50',
            'telp' => 'required|string|max:15',
            'tanggal_lahir' => 'required|date',
            'alamat' => 'required|string|max:100',
        ]);

        // Buat ID baru otomatis
        $lastPegawai = Pegawai::orderBy('ID_Pegawai', 'desc')->first();
        $lastPegawaiNumber = $lastPegawai ? intval(substr($lastPegawai->ID_Pegawai, 3)) : 0;
        $newId = 'EMP' . str_pad($lastPegawaiNumber + 1, 3, '0', STR_PAD_LEFT);

        // Buat ID Info Pegawai baru (ambil dari tabel Informasi_Pegawai, bukan Pegawai)
        $lastInfo = InformasiPegawai::orderBy('ID_InfoPegawai', 'desc')->first();
        $lastInfoNumber = $lastInfo ? intval(substr($lastInfo->ID_InfoPegawai, 3)) : 0;
        $newInfoId = 'INF' . str_pad($lastInfoNumber + 1, 3, '0', STR_PAD_LEFT);

        DB::beginTransaction();

        try {
            // Tambah data pegawai
            Pegawai::create([
                'ID_Pegawai' => $newId,
                'ID_Role' => 'ROL002', // Default role: kasir
                'Username' => $request->nama,
                'Password' => 'default123', // Default password
            ]);

            // Tambah data informasi pegawai
            InformasiPegawai::create([
                'ID_InfoPegawai' => $newInfoId,
                'ID_Pegawai' => $newId,
                'Nama' => $request->nama,
                'Email' => $request->email,
                'No_Telepon' => $request->telp,
                'Tgl_Lahir' => $request->tanggal_lahir,
                'Umur' => Carbon::parse($request->tanggal_lahir)->age,
                'Jenis_Kelamin' => 'L', // default sementara
                'Created_At' => now(),
            ]);

            DB::commit();

            return redirect()->route('pegawai.index')
                ->with('success', 'Pegawai baru berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage()); // tampilkan error detail sementara
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        // Cari username sebelum dihapus
        $username = Pegawai::where('ID_Pegawai', $id)->value('Username');

        // Hapus data pegawai dan finance dengan ID/username yang sama
        Pegawai::where('ID_Pegawai', $id)->delete();
        Finance::where('ID_Finance', $id)->delete();

        if ($username) {
            Finance::where('Username', $username)->delete();
        }

        // Hapus juga di tabel informasi pegawai
        InformasiPegawai::where('ID_Pegawai', $id)->delete();

        return back()->with('ok', 'Pegawai berhasil dihapus!');
    }
}
