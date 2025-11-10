<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Member;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class MemberController extends Controller
{
    /**
     * Tampilkan semua member
     */
    public function index()
    {
        $members = Member::orderBy('Created_At', 'desc')->get();

        $members = $members->map(function ($m) {
            return [
                'id'      => $m->ID_Member,
                'nama'    => $m->Nama,
                'email'   => $m->Email,
                'tanggal' => $m->Created_At,
                'poin'    => $m->Poin,
            ];
        });

        return view('member', compact('members'));
    }

    /**
     * Tambah member baru
     */
   public function store(Request $request)
{
    // Validasi manual
    $validator = Validator::make($request->all(), [
        'nama'    => ['required', 'regex:/^[A-Za-z\s]+$/'],
        'email'   => ['required', 'email'],
        'no_telp' => ['required', 'regex:/^[0-9]+$/'],
        'alamat'  => ['nullable', 'string', 'max:255'],
    ]);

    // Jika gagal → kembalikan flash message umum
    if ($validator->fails()) {
        return back()
            ->with('error', 'Perubahan gagal disimpan. Data tidak valid.')
            ->withInput();
    }

    try {
        // Generate ID member
        $last = Member::orderBy('ID_Member', 'desc')->first();
        $lastNumber = $last ? intval(substr($last->ID_Member, 3)) : 0;
        $newId = 'MBR' . str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);

        // Simpan data
        Member::create([
            'ID_Member'  => $newId,
            'ID_Manager' => 'MGR001',
            'ID_Pegawai' => null,
            'Nama'       => $request->nama,
            'No_Telepon' => $request->no_telp,
            'Email'      => $request->email,
            'Alamat'     => $request->alamat,
            'Poin'       => 0,
            'Created_At' => Carbon::now(),
            'Deleted_At' => null,
        ]);

        // Berhasil → flash umum juga
        return redirect()->route('member.index')
            ->with('success', 'Member berhasil ditambahkan.');
        
    } catch (\Exception $e) {
        return back()
            ->with('error', 'Terjadi kesalahan saat menyimpan data.')
            ->withInput();
    }

}


    /**
     * JSON untuk popup Member di Kasir
     */
    public function listForKasir()
    {
        $members = Member::select(
            'ID_Member as id',
            'Nama as nama',
            'Email as email',
            'No_Telepon as no_telp',
            'Poin as poin'
        )
        ->orderBy('Nama')
        ->get();

        return response()->json($members);
    }

public function destroy($id)
{
    try {
        $member = Member::find($id);

        if (!$member) {
            return redirect()->route('member.index')->with('error', 'Member tidak ditemukan!');
        }

        $member->delete();

        return redirect()->route('member.index')->with('success', 'Member berhasil dihapus!');
    } catch (\Exception $e) {
        return redirect()->route('member.index')->with('error', 'Terjadi kesalahan saat menghapus member.');
    }
}

}
