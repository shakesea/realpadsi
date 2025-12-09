<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PegawaiController extends Controller
{
    /**
     * ===============================
     * TAMPILKAN SEMUA PEGAWAI
     * ===============================
     */
    public function index()
    {
        $pegawai = DB::table('Pegawai')
            ->orderBy('ID_Pegawai', 'asc')
            ->get();

        return view('pegawai', compact('pegawai'));
    }

    /**
     * ===============================
     * TAMPILKAN FORM TAMBAH PEGAWAI
     * ===============================
     */
    public function create()
    {
        return view('tambahpegawai');
    }

    /**
     * ===============================
     * SIMPAN DATA PEGAWAI BARU
     * ===============================
     */
    public function store(Request $request)
    {
        // === VALIDASI INPUT ===
        $validator = Validator::make($request->all(), [
            'username'  => ['required', 'regex:/^[A-Za-z0-9\s]+$/', 'max:50'],
            'password'  => ['required', 'string', 'min:6'],
            'role'      => ['required', 'string'],
        ], [
            'username.required' => 'Nama pegawai wajib diisi!',
            'username.regex' => 'Nama hanya boleh berisi huruf dan angka tanpa simbol!',
            'password.required' => 'Password wajib diisi!',
            'role.required' => 'Role wajib dipilih!',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->with('error', 'Gagal menambahkan pegawai. Periksa data Anda!')
                ->withInput();
        }

        try {
            // === CEK DUPLIKAT USERNAME ===
            $exists = DB::table('Pegawai')
                ->where('Username', $request->username)
                ->exists();

            if ($exists) {
                return redirect()->back()
                    ->with('error', 'Username sudah digunakan!')
                    ->withInput();
            }

            // === GENERATE ID PEGAWAI OTOMATIS ===
            $last = DB::table('Pegawai')->orderBy('ID_Pegawai', 'desc')->first();
            $lastNumber = $last ? intval(substr($last->ID_Pegawai, 3)) : 0;
            $newId = 'EMP' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);

            // === SIMPAN DATA ===
            DB::table('Pegawai')->insert([
                'ID_Pegawai' => $newId,
                'Role'       => $request->role,
                'Username'   => $request->username,
                'Password'   => $request->password,
            ]);

            return redirect()->route('pegawai.index')
                ->with('success', 'Pegawai baru berhasil ditambahkan!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * ===============================
     * HAPUS DATA PEGAWAI
     * ===============================
     */
    public function destroy($id)
    {
        try {
            DB::table('Pegawai')->where('ID_Pegawai', $id)->delete();
            return redirect()->route('pegawai.index')->with('success', 'Pegawai berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->route('pegawai.index')->with('error', 'Gagal menghapus pegawai: ' . $e->getMessage());
        }
    }

    /**
     * ===============================
     * EDIT DATA PEGAWAI
     * ===============================
     */
    public function edit($id)
    {
        $pegawai = DB::table('Pegawai')->where('ID_Pegawai', $id)->first();
        if (!$pegawai) {
            return redirect()->route('pegawai.index')->with('error', 'Pegawai tidak ditemukan.');
        }

        return view('editpegawai', compact('pegawai'));
    }

    /**
     * ===============================
     * UPDATE DATA PEGAWAI
     * ===============================
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'username'  => ['required', 'regex:/^[A-Za-z0-9\s]+$/', 'max:50'],
            'password'  => ['required', 'string', 'min:6'],
            'role'      => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->with('error', 'Gagal memperbarui pegawai.')
                ->withInput();
        }

        try {
            DB::table('Pegawai')->where('ID_Pegawai', $id)->update([
                'Username' => $request->username,
                'Password' => $request->password,
                'Role'     => $request->role,
            ]);

            return redirect()->route('pegawai.index')->with('success', 'Data pegawai berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }
}
