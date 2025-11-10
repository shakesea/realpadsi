<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator; // <--- penting
use App\Models\Pegawai;
use App\Models\Finance;
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
        // Validasi manual dengan pesan umum
        $validator = Validator::make($request->all(), [
            'nama' => ['required', 'max:30', 'regex:/^[a-zA-Z0-9\s]+$/'], // Tidak boleh special char
            'email' => 'required|email|max:50',
            'telp' => 'required|max:15',
            'tanggal_lahir' => 'required|date',
            'alamat' => 'required|max:100',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->with('error', 'Perubahan Gagal di Simpan. Data Tidak Valid atau Kosong')
                ->withInput();
        }

        // Generate ID Pegawai
        $lastPegawai = Pegawai::orderBy('ID_Pegawai', 'desc')->first();
        $lastPegawaiNumber = $lastPegawai ? intval(substr($lastPegawai->ID_Pegawai, 3)) : 0;
        $newId = 'EMP' . str_pad($lastPegawaiNumber + 1, 3, '0', STR_PAD_LEFT);

        // Generate ID Info Pegawai
        $lastInfo = InformasiPegawai::orderBy('ID_InfoPegawai', 'desc')->first();
        $lastInfoNumber = $lastInfo ? intval(substr($lastInfo->ID_InfoPegawai, 3)) : 0;
        $newInfoId = 'INF' . str_pad($lastInfoNumber + 1, 3, '0', STR_PAD_LEFT);

        DB::beginTransaction();

        try {
            Pegawai::create([
                'ID_Pegawai' => $newId,
                'ID_Role' => 'ROL002',
                'Username' => $request->nama,
                'Password' => 'default123',
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
            return back()->with('error', 'Terjadi kesalahan pada server.');
        }
    }

    public function destroy($id)
    {
        $username = Pegawai::where('ID_Pegawai', $id)->value('Username');

        Pegawai::where('ID_Pegawai', $id)->delete();
        Finance::where('ID_Finance', $id)->delete();

        if ($username) {
            Finance::where('Username', $username)->delete();
        }

        InformasiPegawai::where('ID_Pegawai', $id)->delete();

        return back()->with('success', 'Pegawai berhasil dihapus!');
    }
}
