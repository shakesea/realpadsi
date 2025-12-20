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
     * 🟢 Tampilkan semua member (dengan fitur pencarian)
     */
    public function index(Request $request)
    {
        // Ambil kata kunci pencarian dari input (GET ?q=)
        $search = $request->input('q');

        // Buat query dasar
        $query = Member::orderBy('Created_At', 'desc');

        // Jika ada kata kunci pencarian, filter berdasarkan nama atau email
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('Nama', 'like', "%{$search}%")
                    ->orWhere('Email', 'like', "%{$search}%");
            });
        }

        // Jalankan query
        $members = $query->get();

        // Ubah format data agar sesuai view
        $members = $members->map(function ($m) {
            return [
                'id'      => $m->ID_Member,
                'nama'    => $m->Nama,
                'email'   => $m->Email,
                'tanggal' => $m->Created_At,
                'poin'    => $m->Poin,
            ];
        });

        // Kirim data ke view
        return view('member', compact('members'));
    }

    /**
     * 🟢 Tambah member baru
     */
    public function store(Request $request)
    {
        // Normalisasi input
        $namaInput = trim(preg_replace('/\s+/', ' ', (string)$request->nama));
        $telpInputRaw = (string)$request->no_telp;
        $telpNormalized = preg_replace('/[^0-9\+]/', '', $telpInputRaw);
        if (strpos($telpNormalized, '+62') === 0) {
            $telpNormalized = '0' . substr($telpNormalized, 3);
        }

        // Validasi dengan aturan yang lebih ketat + pesan khusus
        $validator = Validator::make(
            [
                'nama'    => $namaInput,
                'email'   => $request->email,
                'no_telp' => $telpNormalized,
                'alamat'  => $request->alamat,
            ],
            [
                'nama'    => ['required', 'min:3', 'max:100', "regex:/^[A-Za-zÀ-ÖØ-öø-ÿ\s'\-\.]+$/", 'unique:Member,Nama'],
                'email'   => ['required', 'email', 'max:150'],
                // Format nomor HP Indonesia: 08xxxxxxxx atau +62/62 di-normalisasi jadi 0
                'no_telp' => ["required", "regex:/^(0)8[0-9]{7,}$/"],
                'alamat'  => ['nullable', 'string', 'max:255'],
            ],
            [
                'nama.required'   => 'Nama wajib diisi.',
                'nama.min'        => 'Nama minimal 3 karakter.',
                'nama.max'        => 'Nama maksimal 100 karakter.',
                'nama.regex'      => "Nama hanya boleh huruf, spasi, tanda - . ' ",
                'nama.unique'     => 'Nama member sudah ada.',
                'email.required'  => 'Email wajib diisi.',
                'email.email'     => 'Format email tidak valid.',
                'email.max'       => 'Email terlalu panjang.',
                'no_telp.required' => 'Nomor telepon wajib diisi.',
                'no_telp.regex'   => 'Nomor telepon Indonesia tidak valid (contoh: 08xxxxxxxx).',
                'alamat.max'      => 'Alamat maksimal 255 karakter.',
            ]
        );

        // Cek duplikasi nama secara case-insensitive pada tahap validator
        $validator->after(function ($v) use ($namaInput) {
            if (!empty($namaInput)) {
                $exists = Member::whereRaw('LOWER(Nama) = ?', [strtolower($namaInput)])
                    ->exists();
                if ($exists) {
                    $v->errors()->add('nama', 'Member sudah ada, silakan gunakan nama lain.');
                }
            }
        });

        // Jika gagal → kembalikan flash message + errors
        if ($validator->fails()) {
            $errors = $validator->errors();
            $flash = null;
            if ($errors->has('nama')) {
                foreach ($errors->get('nama') as $msg) {
                    if (stripos($msg, 'Nama member sudah ada') !== false) {
                        $flash = 'Nama member sudah ada.';
                        break;
                    }
                }
            }

            return back()
                ->withErrors($validator)
                ->with('error', $flash)
                ->withInput();
        }

        try {
            // Generate ID member baru
            $last = Member::orderBy('ID_Member', 'desc')->first();
            $lastNumber = $last ? intval(substr($last->ID_Member, 3)) : 0;
            $newId = 'MBR' . str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);

            // Simpan data baru
            Member::create([
                'ID_Member'  => $newId,
                'ID_Manager' => 'MGR001',
                'ID_Pegawai' => null,
                'Nama'       => $namaInput,
                'No_Telepon' => $telpNormalized,
                'Email'      => $request->email,
                'Alamat'     => $request->alamat,
                'Poin'       => 0,
                'Created_At' => Carbon::now(),
                'Deleted_At' => null,
            ]);

            return redirect()->route('member.index')
                ->with('success', 'Member berhasil ditambahkan.');
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Terjadi kesalahan saat menyimpan data.')
                ->withInput();
        }
    }

    /**
     * 🟢 JSON untuk popup Member di Kasir
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

    /**
     * 🔴 Hapus member
     */
    public function destroy($id)
    {
        try {
            $member = Member::find($id);

            if (!$member) {
                return redirect()->route('member.index')
                    ->with('error', 'Member tidak ditemukan!');
            }

            $member->delete();

            return redirect()->route('member.index')
                ->with('success', 'Member berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->route('member.index')
                ->with('error', 'Terjadi kesalahan saat menghapus member.');
        }
    }
}
