<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Member;
use Carbon\Carbon;

class MemberController extends Controller
{
    /**
     * Tampilkan semua member
     */
    public function index()
    {
        $members = Member::orderBy('Created_At', 'desc')->get();

        // Mapping data agar sesuai dengan field di view (nama kecil)
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
     * Simpan member baru
     */
    public function store(Request $request)
{
    $request->validate([
        'nama' => 'required|string|max:255',
        'email' => 'required|email',
        'no_telp' => 'required|string|max:20',
        'alamat' => 'nullable|string|max:255',
    ]);

    try {
        $id = 'MBR' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);

        Member::create([
            'ID_Member'  => $id,
            'ID_Manager' => 'MGR001', // sementara default, bisa kamu ubah sesuai user login
            'ID_Pegawai' => null,
            'Nama'       => $request->nama,
            'No_Telepon' => $request->no_telp,
            'Email'      => $request->email,
            'Alamat'     => $request->alamat,
            'Poin'       => 0,
            'Created_At' => now(),
            'Deleted_At' => null,
        ]);

        return redirect()->route('member.index')->with('ok', 'Member baru berhasil ditambahkan.');

    } catch (\Exception $e) {
        return back()->with('error', 'Gagal menambah member: ' . $e->getMessage());
    }
}

    /**
     * Hapus member
     */
public function destroy($id)
{
    try {
        $member = \Member::where('ID_Member', $id)->first();

        if (! $member) {
            return redirect()->route('member.index')->with('error', 'Member tidak ditemukan.');
        }

        $member->delete();

        return redirect()->route('member.index')->with('ok', 'Member berhasil dihapus.');
    } catch (\Exception $e) {
        return redirect()->route('member.index')->with('error', 'Gagal menghapus member: ' . $e->getMessage());
    }
}
}