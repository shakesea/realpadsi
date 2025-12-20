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

        // Cek duplikasi nama stok (case-insensitive)
        $namaInput = trim($request->nama);
        $isDuplicate = Stok::whereRaw('LOWER(`Nama`) = LOWER(?)', [$namaInput])->exists();
        if ($isDuplicate) {
            return redirect()->back()
                ->with('error', '❌ Stok dengan nama ini sudah ada, silakan gunakan nama lain.')
                ->withInput();
        }

        // Generate ID STOK
        $last = Stok::orderBy('ID_Barang', 'desc')->first();
        $lastNumber = $last ? intval(substr($last->ID_Barang, 4)) : 0;
        $newId = 'STOK' . str_pad($lastNumber + 1, 2, '0', STR_PAD_LEFT);

        // Insert data
        $stok = new Stok([
            'ID_Barang'   => $newId,
            'Nama'        => $request->nama,
            'Jumlah_Item' => $request->jumlah,
            'Kategori'    => $request->kategori,
            'Created_At'  => now(),
            'Updated_At'  => now(),
        ]);

        // Update status otomatis berdasarkan jumlah
        $stok->updateStatus();
        $stok->save();

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

        // 1.5. Cek duplikasi nama stok selain ID yang sedang diedit (case-insensitive)
        $namaInput = trim($request->nama);
        $isDuplicate = Stok::whereRaw('LOWER(`Nama`) = LOWER(?)', [$namaInput])
            ->where('ID_Barang', '!=', $id)
            ->exists();
        if ($isDuplicate) {
            return redirect()->back()
                ->with('error', '❌ Stok dengan nama ini sudah ada, silakan gunakan nama lain.')
                ->withInput();
        }

        // 2. Ambil data stok
        $stok = Stok::where('ID_Barang', $id)->firstOrFail();

        // 3. Update data
        $stok->Nama = $request->nama;
        $stok->Jumlah_Item = $request->jumlah;
        $stok->Kategori = $request->kategori;
        $stok->Updated_At = now();

        // Update status otomatis berdasarkan jumlah
        $stok->updateStatus();
        $affectedRows = $stok->save() ? 1 : 0;

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
        try {
            $filter = $request->query('status');
            $sortBy = $request->query('sortBy', 'ID_Barang'); // Default sort by ID
            $sortDir = $request->query('sortDir', 'asc'); // Default ascending

            // Validasi kolom sorting untuk keamanan
            $allowedColumns = ['Nama', 'Jumlah_Item', 'Kategori', 'Status', 'ID_Barang'];
            if (!in_array($sortBy, $allowedColumns)) {
                $sortBy = 'ID_Barang';
            }

            // Validasi direction
            $sortDir = strtolower($sortDir) === 'desc' ? 'desc' : 'asc';

            $stokData = Stok::when($filter, function ($query) use ($filter) {
                return $query->where('Status', $filter);
            })->orderBy($sortBy, $sortDir)->get();

            // Bypass facade to avoid public path resolution issues on shared hosting
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml(view('pdf.stok_report', compact('stokData', 'filter'))->render());
            $dompdf->setPaper('a4', 'portrait');
            $dompdf->render();

            return response()->streamDownload(function () use ($dompdf) {
                echo $dompdf->output();
            }, 'laporan_stok.pdf', [
                'Content-Type' => 'application/pdf',
            ]);
        } catch (\Exception $e) {
            \Log::error('PDF Export Error: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Gagal menghasilkan PDF. Error: ' . $e->getMessage());
        }
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
