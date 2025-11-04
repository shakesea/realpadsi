<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TransaksiPenjualan;
use App\Models\Manager;
use App\Models\Pegawai;
use Carbon\Carbon;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        // Default tanggal (7 hari terakhir)
        $start = $request->get('start') ?? Carbon::now()->subDays(7)->format('Y-m-d');
        $end   = $request->get('end') ?? Carbon::now()->format('Y-m-d');

        // Ambil data transaksi berdasarkan periode
        $laporan = TransaksiPenjualan::whereDate('Tgl_Penjualan', '>=', $start)
            ->whereDate('Tgl_Penjualan', '<=', $end)
            ->orderBy('Tgl_Penjualan', 'desc')
            ->get()
            ->map(function ($trx) {
                // Ambil nama dari Manager atau Pegawai
                $nama = null;
                if ($trx->ID_Manager) {
                    $manager = \App\Models\Manager::where('ID_Manager', $trx->ID_Manager)->first();
                    $nama = $manager ? $manager->Nama_Manager : 'Manager Tidak Dikenal';
                } elseif ($trx->ID_Pegawai) {
                    $pegawai = \App\Models\Pegawai::where('ID_Pegawai', $trx->ID_Pegawai)->first();
                    $nama = $pegawai ? $pegawai->Nama_Pegawai : 'Pegawai Tidak Dikenal';
                } else {
                    $nama = 'Tidak Diketahui';
                }

                return [
                    'nama' => $nama,
                    'total' => $trx->TotalHarga,
                    'kode' => $trx->ID_Penjualan,
                    'waktu' => Carbon::parse($trx->Tgl_Penjualan)->format('H:i:s'),
                ];
            });

        return view('penjualan', compact('laporan', 'start', 'end'));
    }
}
