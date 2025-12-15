<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Stok;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;


class StokController extends Controller
{
    // ===== Helper Status Stok =====
    private function getStatus($jumlah)
    {
        if ($jumlah == 0) {
            return 'Habis';
        } elseif ($jumlah <= 200) {
            return 'Menipis';
        } else {
            return 'Aman';
        }
    }

    // ===== Tampilkan Data Stok =====
    public function index(Request $request)
    {
        $filter = $request->query('status');

        $stokData = Stok::when($filter, function ($query) use ($filter) {
            return $query->where('Status', $filter);
        })->orderBy('ID_Barang')->get();

        return view('stok', compact('stokData', 'filter'));
    }


    // ===== Form Tambah =====
    public function create()
    {
        return view('tambahstok');
    }

    // ===== Simpan Data Baru =====
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => ['required', 'regex:/^[a-zA-Z0-9\s]+$/'],
            'jumlah' => ['required', 'numeric', 'min:0'],
            'kategori' => ['required', 'regex:/^[a-zA-Z0-9\s,&\-]+$/'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->with('error', 'Perubahan Gagal di Simpan. Data Tidak Valid atau Kosong')
                ->withInput();
        }

        // Generate ID STOK
        $last = Stok::orderBy('ID_Barang', 'desc')->first();
        $lastNumber = $last ? intval(substr($last->ID_Barang, 4)) : 0;
        $newId = 'STOK' . str_pad($lastNumber + 1, 2, '0', STR_PAD_LEFT);

        // Hitung status otomatis
        $status = $this->getStatus($request->jumlah);

        // Insert data
        Stok::create([
            'ID_Barang'   => $newId,
            'Nama'        => $request->nama,
            'Jumlah_Item' => $request->jumlah,
            'Kategori'    => $request->kategori,
            'Status'      => $status,
            'Created_At'  => now(),
            'Updated_At'  => now(),
        ]);

        return redirect()->route('stok.index')
            ->with('success', 'Stok baru berhasil ditambahkan!');
    }

    // ===== Form Edit =====
    public function edit($id)
    {
        $stokItem = Stok::where('ID_Barang', $id)->firstOrFail();
        return view('editstok', compact('stokItem'));
    }

    // ===== Update Data (REVISI RIGORIS) =====
    public function update(Request $request, $id)
    {
        // 1. Validasi Input
        $validator = Validator::make($request->all(), [
            'nama' => ['required', 'regex:/^[a-zA-Z0-9\s]+$/'],
            'jumlah' => ['required', 'numeric', 'min:0'],
            'kategori' => ['required', 'regex:/^[a-zA-Z0-9\s,&\-]+$/'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->with('error', 'Validasi Gagal.')
                ->withInput();
        }

        // 2. Hitung Status
        $status = $this->getStatus($request->jumlah);

        // 3. EKSEKUSI UPDATE LANGSUNG (Bypass Eloquent Model Magic)
        // Kita menggunakan 'where' string secara eksplisit agar ID 'STOK35' terbaca benar.
        $affectedRows = Stok::where('ID_Barang', $id)->update([
            'Nama'        => $request->nama,
            'Jumlah_Item' => $request->jumlah,
            'Kategori'    => $request->kategori,
            'Status'      => $status,
            'Updated_At'  => now(), // Kita paksa update timestamp
        ]);

        // 4. VERIFIKASI KEBENARAN
        // Jika $affectedRows bernilai 0, berarti database TIDAK menyentuh data apapun.
        if ($affectedRows === 0) {
            // Cek apakah ID-nya memang ada?
            $exists = Stok::where('ID_Barang', $id)->exists();

            if (!$exists) {
                return redirect()->back()
                    ->with('error', "CRITICAL ERROR: ID Barang '$id' tidak ditemukan di Database.");
            } else {
                // Jika ID ada tapi tidak update, berarti data yang dikirim SAMA PERSIS dengan yang di DB
                // atau ada masalah locking. Kita anggap warning saja.
                return redirect()->route('stok.index')
                    ->with('warning', 'Data tidak berubah (isi data sama dengan sebelumnya).');
            }
        }

        // 5. Sukses
        \Log::info('Stok Force Update Berhasil', ['id' => $id, 'rows' => $affectedRows]);

        return redirect()->route('stok.index')
            ->with('success', 'Data stok diperbarui!');
    }

    public function exportPDF(Request $request)
    {
        $filter = $request->query('status');

        $stokData = Stok::when($filter, function ($query) use ($filter) {
            return $query->where('Status', $filter);
        })->orderBy('ID_Barang')->get();

        $pdf = Pdf::loadView('pdf.stok_report', compact('stokData', 'filter'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('laporan_stok.pdf');
    }



    // ===== Hapus Data =====
    public function destroy($id)
    {
        $stokItem = Stok::where('ID_Barang', $id)->firstOrFail();
        $stokItem->delete();

        return redirect()->route('stok.index')
            ->with('success', 'Data stok berhasil dihapus!');
    }
}
