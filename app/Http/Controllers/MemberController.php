<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class MemberController extends Controller
{
    /**
     * Seed dummy data jika belum ada di session.
     */
    private function seedIfEmpty(Request $request)
    {
        if (! $request->session()->has('members')) {
            $request->session()->put('members', [
                [
                    'id' => 1,
                    'nama' => 'John Doe',
                    'tanggal' => '2024-10-20',
                    'email' => 'john@example.com',
                    'poin' => 200,
                    'alamat' => 'Jl. Mawar No. 10',
                    'hp' => '08123456789',
                ],
                [
                    'id' => 2,
                    'nama' => 'Jane Smith',
                    'tanggal' => '2024-11-02',
                    'email' => 'jane@example.com',
                    'poin' => 150,
                    'alamat' => 'Jl. Melati No. 20',
                    'hp' => '08987654321',
                ],
            ]);
        }
    }

    /**
     * Tampilkan daftar member (halaman utama)
     */
    public function index(Request $request)
    {
        $this->seedIfEmpty($request);
        $members = $request->session()->get('members', []);
        return view('member', compact('members'));
    }

    /**
     * Form tambah member
     */
    public function create()
    {
        return view('member-create');
    }

    /**
     * Simpan member baru (dummy mode)
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nama'    => 'required|string|max:255',
            'email'   => 'required|email',
            'tanggal' => 'required|date',
            'poin'    => 'nullable|integer|min:0',
            'hp'      => 'nullable|string|max:20',
            'alamat'  => 'nullable|string|max:255',
        ]);

        $members = $request->session()->get('members', []);
        $data['id'] = collect($members)->max('id') + 1 ?? 1;
        $data['poin'] = $data['poin'] ?? 0;

        // 🟢 Tambahkan field waktu dibuat otomatis
        $data['member_sejak'] = now()->format('d/m/Y');

        $members[] = $data;
        $request->session()->put('members', $members);

        return redirect()->route('member.index')->with('ok', 'Member baru berhasil ditambahkan.');
    }
    /**
     * Tampilkan form edit
     */
    public function edit(Request $request, $id)
    {
        $members = $request->session()->get('members', []);
        $member = collect($members)->firstWhere('id', (int) $id);
        abort_unless($member, 404);

        return view('member-edit', compact('member'));
    }

    /**
     * Update data member
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email',
            'tanggal' => 'required|date',
            'poin' => 'nullable|integer|min:0',
            'hp' => 'nullable|string|max:20',
            'alamat' => 'nullable|string|max:255',
        ]);
        $data['poin'] = $data['poin'] ?? 0;

        $members = $request->session()->get('members', []);
        foreach ($members as &$m) {
            if ($m['id'] == (int)$id) {
                $m = array_merge($m, $data);
                break;
            }
        }
        $request->session()->put('members', $members);

        return redirect()->route('member.index')->with('ok', 'Data member diperbarui (Dummy Mode)');
    }

    /**
     * Hapus member dari session
     */
    public function destroy(Request $request, $id)
    {
        $members = collect($request->session()->get('members', []))
            ->reject(fn($m) => $m['id'] == (int)$id)
            ->values()
            ->all();

        $request->session()->put('members', $members);

        return redirect()->route('member.index')->with('ok', 'Member berhasil dihapus (Dummy Mode)');
    }
}
