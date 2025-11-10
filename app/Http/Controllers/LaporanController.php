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
        return TransaksiPenjualan::with(['detailPenjualan.menu', 'member'])
            ->whereDate('Tgl_Penjualan', '>=', $start)
            ->whereDate('Tgl_Penjualan', '<=', $end)
            ->orderBy('Tgl_Penjualan', 'desc')
            ->get()
            ->map(function ($trx) {

                // 🧾 Dapatkan nama kasir (manager / pegawai)
                if ($trx->ID_Manager) {
                    $manager = Manager::where('ID_Manager', $trx->ID_Manager)->first();
                    // Prefer a human-friendly name field if available, fallback to username/email
                    if ($manager) {
                        $nama = $manager->Nama_Manager ?? $manager->Username ?? $manager->Email ?? 'Manager Tidak Dikenal';
                    } else {
                        $nama = 'Manager Tidak Dikenal';
                    }
                } elseif ($trx->ID_Pegawai) {
                    $pegawai = Pegawai::where('ID_Pegawai', $trx->ID_Pegawai)->first();
                    if ($pegawai) {
                        $nama = $pegawai->Nama_Pegawai ?? $pegawai->Username ?? $pegawai->Email ?? 'Pegawai Tidak Dikenal';
                    } else {
                        $nama = 'Pegawai Tidak Dikenal';
                    }
                } else {
                    $nama = 'Tidak Diketahui';
                }

                // 🍽️ Kelompokkan item berdasarkan kategori menu
                $items = collect($trx->detailPenjualan)
                    ->groupBy(function ($detail) {
                        return $detail->menu ? $detail->menu->Kategori : 'Tanpa Kategori';
                    })
                    ->map(function ($details) {
                        return [
                            'items' => $details->map(function ($detail) {
                                $menu = $detail->menu;
                                // prefer harga recorded in detail, else fallback to menu price
                                $harga = $detail->Harga ?? ($menu ? $menu->Harga : 0);
                                $qty = $detail->Quantity ?? 0;
                                $subtotal = ($detail->Subtotal ?? null) ?: ($qty * $harga);

                                return [
                                    'nama' => $menu ? $menu->Nama : 'Menu Tidak Ditemukan',
                                    'qty' => $qty,
                                    'harga' => $harga,
                                    'subtotal' => $subtotal,
                                ];
                            }),
                            'total_qty' => $details->sum('Quantity'),
                            'total_amount' => $details->sum(function ($detail) {
                                $harga = $detail->Harga ?? ($detail->menu ? $detail->menu->Harga : 0);
                                $qty = $detail->Quantity ?? 0;
                                return ($detail->Subtotal ?? null) ?: ($qty * $harga);
                            }),
                        ];
                    })->toArray();

                // 📋 Return hasil laporan per transaksi
                return [
                    'nama' => $nama,
                    'total' => $trx->TotalHarga,
                    'kode' => $trx->ID_Penjualan,
                    'waktu' => Carbon::parse($trx->Tgl_Penjualan)->format('H:i:s'),
                    'tanggal' => Carbon::parse($trx->Tgl_Penjualan)->format('d/m/Y'),
                    'metode' => $trx->Metode_Pembayaran,
                    'items' => $items,
                    'member' => $trx->member ? [
                        'nama' => $trx->member->Nama,
                        'poin_digunakan' => $trx->Poin_Digunakan ?? 0,
                        'poin_didapat' => $trx->Poin_Didapat ?? 0,
                    ] : null,
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

    // Jika user minta export PDF
    if ($request->get('export') === 'pdf') {
        try {
            $pdf = PDF::loadView('exports.penjualan-pdf', compact('laporan', 'start', 'end'));
            $pdf->setPaper('a4');

            $filename = 'laporan-penjualan-' .
                Carbon::parse($start)->format('d-m-Y') .
                '_sd_' .
                Carbon::parse($end)->format('d-m-Y') .
                '.pdf';

            // ✅ Berhasil export PDF
            session()->flash('success', '✅ Laporan berhasil diekspor ke PDF!');
            return $pdf->download($filename);

        } catch (\Exception $e) {
            return back()->withErrors(['error' => '❌ Gagal menghasilkan PDF. Silakan coba lagi.']);
        }
    }

    // ✅ Berhasil menampilkan laporan di halaman
    return view('penjualan', compact('laporan', 'start', 'end'))
        ->with('success', '✅ Laporan berhasil ditampilkan!');
}

}
