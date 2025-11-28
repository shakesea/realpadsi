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
            'kategori' => ['required', 'regex:/^[a-zA-Z0-9\s,]+$/'],
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

    // ===== Update Data =====
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nama' => ['required', 'regex:/^[a-zA-Z0-9\s]+$/'],
            'jumlah' => ['required', 'numeric', 'min:0'],
            'kategori' => ['required', 'regex:/^[a-zA-Z0-9\s,]+$/'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->with('error', 'Perubahan Gagal di Simpan. Data Tidak Valid atau Kosong')
                ->withInput();
        }

        $stokItem = Stok::where('ID_Barang', $id)->firstOrFail();

        // Hitung ulang status berdasarkan stok baru
        $status = $this->getStatus($request->jumlah);

        $stokItem->update([
            'Nama'        => $request->nama,
            'Jumlah_Item' => $request->jumlah,
            'Kategori'    => $request->kategori,
            'Status'      => $status,
            'Updated_At'  => now(),
        ]);

        return redirect()->route('stok.index')
            ->with('success', 'Data stok berhasil diperbarui!');
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
