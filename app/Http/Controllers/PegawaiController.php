<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
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
        // Validasi manual
        $validator = Validator::make($request->all(), [
            'nama' => ['required', 'max:100', 'regex:/^[a-zA-Z0-9\s]+$/'],
            'email' => 'required|email|max:100',
            'telp' => 'required|max:15',
            'tanggal_lahir' => 'required|date',
            'alamat' => 'required|max:100',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'role' => 'required|in:ROL002,ROL003',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->with('error', 'Perubahan Gagal di Simpan. Data Tidak Valid atau Kosong')
                ->withInput();
        }

        // ==========================================
        // 🔍 CEK DUPLIKASI DATA
        // ==========================================
        $cekUsername = Pegawai::where('Username', $request->nama)->exists() ||
            Finance::where('Username', $request->nama)->exists();
        $cekEmail = InformasiPegawai::where('Email', $request->email)->exists();
        $cekTelp = InformasiPegawai::where('No_Telepon', $request->telp)->exists();

        if ($cekUsername || $cekEmail || $cekTelp) {
            return back()
                ->with('error', 'Data sudah ada, silakan periksa kembali.')
                ->withInput();
        }
        // ==========================================

        DB::beginTransaction();

        try {
            // Convert jenis_kelamin ke single character
            $jenisKelamin = $request->jenis_kelamin === 'Laki-laki' ? 'L' : 'P';

            // Jika role Finance (ROL003), simpan ke tabel Finance
            if ($request->role === 'ROL003') {
                // Generate ID Finance
                $lastFinance = DB::table('Finance')
                    ->selectRaw("CAST(SUBSTRING(ID_Finance, 4) AS UNSIGNED) as num")
                    ->orderByDesc('num')
                    ->first();
                $lastFinanceNumber = $lastFinance ? $lastFinance->num : 0;
                $newId = 'FIN' . str_pad($lastFinanceNumber + 1, 3, '0', STR_PAD_LEFT);

                Finance::create([
                    'ID_Finance' => $newId,
                    'ID_Role' => $request->role,
                    'Username' => $request->nama,
                    'Password' => bcrypt('default123'),
                ]);
            }
            // Jika role Kasir (ROL002), simpan ke tabel Pegawai
            else {
                // Generate ID Pegawai
                $lastPegawai = DB::table('Pegawai')
                    ->selectRaw("CAST(SUBSTRING(ID_Pegawai, 4) AS UNSIGNED) as num")
                    ->orderByDesc('num')
                    ->first();
                $lastPegawaiNumber = $lastPegawai ? $lastPegawai->num : 0;
                $newId = 'EMP' . str_pad($lastPegawaiNumber + 1, 3, '0', STR_PAD_LEFT);

                Pegawai::create([
                    'ID_Pegawai' => $newId,
                    'ID_Role' => $request->role,
                    'Username' => $request->nama,
                    'Password' => bcrypt('default123'),
                ]);
            }

            // Generate ID Informasi Pegawai
            $lastInfo = DB::table('Informasi_Pegawai')
                ->selectRaw("CAST(SUBSTRING(ID_InfoPegawai, 4) AS UNSIGNED) as num")
                ->orderByDesc('num')
                ->first();
            $lastInfoNumber = $lastInfo ? $lastInfo->num : 0;
            $newInfoId = 'INF' . str_pad($lastInfoNumber + 1, 3, '0', STR_PAD_LEFT);

            // Jika Finance, nonaktifkan foreign key check sementara
            if ($request->role === 'ROL003') {
                DB::statement('SET FOREIGN_KEY_CHECKS=0');
            }

            // Simpan informasi pegawai dengan ID yang sesuai (ID_Pegawai atau ID_Finance)
            InformasiPegawai::create([
                'ID_InfoPegawai' => $newInfoId,
                'ID_Pegawai' => $newId,
                'Nama' => $request->nama,
                'Email' => $request->email,
                'No_Telepon' => $request->telp,
                'Tgl_Lahir' => $request->tanggal_lahir,
                'Umur' => Carbon::parse($request->tanggal_lahir)->age,
                'Jenis_Kelamin' => $jenisKelamin,
                'Created_At' => now(),
            ]);

            // Aktifkan kembali foreign key check
            if ($request->role === 'ROL003') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            }

            DB::commit();

            $roleText = ($request->role === 'ROL003') ? 'Finance' : 'Pegawai';
            return redirect()->route('pegawai.index')
                ->with('success', $roleText . ' baru berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Pegawai gagal ditambahkan. Error: ' . $e->getMessage());
        }
    }
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            // Cek apakah pegawai ada di tabel Pegawai atau Finance
            $pegawai = Pegawai::where('ID_Pegawai', $id)->first();
            $finance = Finance::where('ID_Finance', $id)->first();

            if (!$pegawai && !$finance) {
                return back()->with('error', 'Data tidak ditemukan!');
            }

            // Hapus informasi pegawai terlebih dahulu (child record)
            InformasiPegawai::where('ID_Pegawai', $id)->delete();

            // Set null untuk foreign key di TransaksiPenjualan agar tidak error
            DB::table('TransaksiPenjualan')
                ->where('ID_Pegawai', $id)
                ->update(['ID_Pegawai' => null]);

            // Hapus data di tabel yang sesuai (Pegawai atau Finance)
            if ($pegawai) {
                $pegawai->delete();
            } else {
                $finance->delete();
            }

            DB::commit();

            return back()->with('success', 'Data berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Delete Pegawai/Finance Error: ' . $e->getMessage());

            return back()->with('error', 'Gagal menghapus data. Error: ' . $e->getMessage());
        }
    }
}
