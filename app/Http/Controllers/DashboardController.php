<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;


use Illuminate\Http\Request;
use App\Models\TransaksiPenjualan;

class DashboardController extends Controller
{
    public function index()
    {
        $totalPenjualan = TransaksiPenjualan::sum('TotalHarga');
        $jumlahTransaksi = TransaksiPenjualan::count();
        $rataRata = TransaksiPenjualan::avg('TotalHarga');
        $totalBiaya = 0;
        $labaKotor = $totalPenjualan * 0.3;

         $penjualanPerTanggal = TransaksiPenjualan::select(
            DB::raw('DATE(Tgl_Penjualan) as tanggal'),
            DB::raw('SUM(TotalHarga) as total')
        )
        ->groupBy(DB::raw('DATE(Tgl_Penjualan)'))
        ->orderBy(DB::raw('DATE(Tgl_Penjualan)'), 'ASC')
        ->get();

            $labels = $penjualanPerTanggal->pluck('tanggal');
            $data = $penjualanPerTanggal->pluck('total');


        return view('dashboard', compact(
            'totalPenjualan',
            'jumlahTransaksi',
            'rataRata',
            'labaKotor',
            'totalBiaya',
            'labels',
            'data',
        ));
    }
}
