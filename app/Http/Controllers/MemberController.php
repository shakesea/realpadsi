<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Member;
use Illuminate\Support\Facades\DB;
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
                'ID_Manager' => 'MGR001',   // sementara default
                'ID_Pegawai' => null,
                'Nama'       => $request->nama,
                'No_Telepon' => $request->no_telp,
                'Email'      => $request->email,
                'Alamat'     => $request->alamat,
                'Poin'       => 0,
                'Created_At' => Carbon::now(),
                'Deleted_At' => null,
            ]);

            return redirect()->route('member.index')->with('ok', '✅ Member berhasil ditambahkan!');
        } catch (\Exception $e) {
            return back()->with('error', '❌ Gagal menambah member: ' . $e->getMessage());
        }
    }

    /**
     * Hapus member
     */
    public function destroy($id)
    {
        try {
            $member = Member::where('ID_Member', $id)->first();

            if (!$member) {
                return redirect()->route('member.index')->with('error', 'Member tidak ditemukan.');
            }

            $member->delete();

            return redirect()->route('member.index')->with('ok', '🗑️ Member berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->route('member.index')->with('error', '❌ Gagal menghapus member: ' . $e->getMessage());
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
}
