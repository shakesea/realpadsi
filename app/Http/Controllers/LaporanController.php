<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TransaksiPenjualan;
use App\Models\Manager;
use App\Models\Pegawai;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    private function getLaporanData($start, $end)
    {
        return TransaksiPenjualan::whereDate('Tgl_Penjualan', '>=', $start)
            ->whereDate('Tgl_Penjualan', '<=', $end)
            ->orderBy('Tgl_Penjualan', 'desc')
            ->get()
            ->map(function ($trx) {
                // Ambil nama dari Manager atau Pegawai
                $nama = null;
                if ($trx->ID_Manager) {
                    $manager = Manager::where('ID_Manager', $trx->ID_Manager)->first();
                    $nama = $manager ? $manager->Nama_Manager : 'Manager Tidak Dikenal';
                } elseif ($trx->ID_Pegawai) {
                    $pegawai = Pegawai::where('ID_Pegawai', $trx->ID_Pegawai)->first();
                    $nama = $pegawai ? $pegawai->Nama_Pegawai : 'Pegawai Tidak Dikenal';
                } else {
                    $nama = 'Tidak Diketahui';
                }

                return [
                    'nama' => $nama,
                    'total' => $trx->TotalHarga,
                    'kode' => $trx->ID_Penjualan,
                    ''
                    'waktu' => Carbon::parse($trx->Tgl_Penjualan)->format('H:i:s'),
                    'tanggal' => Carbon::parse($trx->Tgl_Penjualan)->format('d/m/Y'),
                ];
            });
    }

    public function index(Request $request)
    {
        // Default tanggal (7 hari terakhir)
        $start = $request->get('start') ?? Carbon::now()->subDays(7)->format('Y-m-d');
        $end   = $request->get('end') ?? Carbon::now()->format('Y-m-d');

        // Ambil data transaksi berdasarkan periode
        $laporan = $this->getLaporanData($start, $end);

        if ($request->get('export') === 'pdf') {
            try {
                $pdf = PDF::loadView('exports.penjualan-pdf', compact('laporan', 'start', 'end'));
                $pdf->setPaper('a4');
                $filename = 'laporan-penjualan-' .
                    Carbon::parse($start)->format('d-m-Y') .
                    '_sd_' .
                    Carbon::parse($end)->format('d-m-Y') .
                    '.pdf';

                return $pdf->download($filename);
            } catch (\Exception $e) {
                return back()->withErrors(['error' => 'Gagal menghasilkan PDF. Silakan coba lagi.']);
            }
        }
        return view('penjualan', compact('laporan', 'start', 'end'));
    }
}
