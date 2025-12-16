<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Pegawai;
use App\Models\InformasiPegawai;
use Carbon\Carbon;

class PegawaiController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));

        $pegawai = DB::table('Pegawai')
            ->select('ID_Pegawai as ID', 'ID_Role', 'Username', 'Password')
            ->when($q !== '', function ($query) use ($q) {
                $query->where('Username', 'like', "%{$q}%")
                    ->orWhere('ID_Pegawai', 'like', "%{$q}%");
            });

        $finance = DB::table('Finance')
            ->select('ID_Finance as ID', 'ID_Role', 'Username', 'Password')
            ->when($q !== '', function ($query) use ($q) {
                $query->where('Username', 'like', "%{$q}%")
                    ->orWhere('ID_Finance', 'like', "%{$q}%");
            });

        $data = $pegawai->unionAll($finance)->orderBy('ID')->get();

        return view('pegawai', ['pegawai' => $data, 'q' => $q]);
    }

    public function create()
    {
        return view('tambahpegawai');
    }

    public function store(Request $request)
    {
        // Validasi manual
        $validator = Validator::make($request->all(), [
            'nama' => ['required', 'max:100', 'regex:/^[a-zA-Z0-9\s]+$/'],
            'email' => 'required|email|max:100',
            'telp' => 'required|max:15',
            'tanggal_lahir' => 'required|date',
            'alamat' => 'required|max:100',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->with('error', 'Perubahan Gagal di Simpan. Data Tidak Valid atau Kosong')
                ->withInput();
        }

        // ==========================================
        // 🔍 CEK DUPLIKASI DATA
        // ==========================================
        $cekUsername = Pegawai::where('Username', $request->nama)->exists();
        $cekEmail = InformasiPegawai::where('Email', $request->email)->exists();
        $cekTelp = InformasiPegawai::where('No_Telepon', $request->telp)->exists();

        if ($cekUsername || $cekEmail || $cekTelp) {
            return back()
                ->with('error', 'Data sudah ada, silakan periksa kembali.')
                ->withInput();
        }
        // ==========================================

        // Generate ID Pegawai dengan cara yang lebih reliable
        $lastPegawai = DB::table('Pegawai')
            ->selectRaw("CAST(SUBSTRING(ID_Pegawai, 4) AS UNSIGNED) as num")
            ->orderByDesc('num')
            ->first();
        $lastPegawaiNumber = $lastPegawai ? $lastPegawai->num : 0;
        $newId = 'EMP' . str_pad($lastPegawaiNumber + 1, 3, '0', STR_PAD_LEFT);

        // Generate ID Informasi Pegawai dengan cara yang lebih reliable
        $lastInfo = DB::table('Informasi_Pegawai')
            ->selectRaw("CAST(SUBSTRING(ID_InfoPegawai, 4) AS UNSIGNED) as num")
            ->orderByDesc('num')
            ->first();
        $lastInfoNumber = $lastInfo ? $lastInfo->num : 0;
        $newInfoId = 'INF' . str_pad($lastInfoNumber + 1, 3, '0', STR_PAD_LEFT);

        DB::beginTransaction();

        try {
            Pegawai::create([
                'ID_Pegawai' => $newId,
                'ID_Role' => 'ROL002',
                'Username' => $request->nama,
                'Password' => bcrypt('default123'),
            ]);

            InformasiPegawai::create([
                'ID_InfoPegawai' => $newInfoId,
                'ID_Pegawai' => $newId,
                'Nama' => $request->nama,
                'Email' => $request->email,
                'No_Telepon' => $request->telp,
                'Tgl_Lahir' => $request->tanggal_lahir,
                'Umur' => Carbon::parse($request->tanggal_lahir)->age,
                'Jenis_Kelamin' => 'L',
                'Created_At' => now(),
            ]);

            DB::commit();

            return redirect()->route('pegawai.index')
                ->with('success', 'Pegawai baru berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Pegawai gagal ditambahkan. Error: ' . $e->getMessage());
        }
    }
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            // Cek apakah pegawai ada
            $pegawai = Pegawai::where('ID_Pegawai', $id)->first();

            if (!$pegawai) {
                return back()->with('error', 'Pegawai tidak ditemukan!');
            }

            // Hapus informasi pegawai terlebih dahulu (child record)
            InformasiPegawai::where('ID_Pegawai', $id)->delete();

            // Set null untuk foreign key di TransaksiPenjualan agar tidak error
            DB::table('TransaksiPenjualan')
                ->where('ID_Pegawai', $id)
                ->update(['ID_Pegawai' => null]);

            // Hapus data di tabel Pegawai (parent record)
            $pegawai->delete();

            DB::commit();

            return back()->with('success', 'Pegawai berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Delete Pegawai Error: ' . $e->getMessage());

            return back()->with('error', 'Gagal menghapus pegawai. Error: ' . $e->getMessage());
        }
    }
}
