<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\TransaksiPenjualan;
use App\Models\Stok;
use App\Models\Member;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // ============================
        // 1. CARD SUMMARY
        // ============================

        $totalPenjualan = TransaksiPenjualan::sum('TotalHarga');
        $jumlahTransaksi = TransaksiPenjualan::count();
        $rataRata = TransaksiPenjualan::avg('TotalHarga');
        $totalBiaya = 0; // Bisa dibuat dinamis nanti
        $labaKotor = $totalPenjualan * 0.3;

        // ============================
        // 2. GRAFIK PENJUALAN (Line)
        // ============================

        $penjualanPerTanggal = TransaksiPenjualan::select(
            DB::raw('DATE(Tgl_Penjualan) as tanggal'),
            DB::raw('SUM(TotalHarga) as total')
        )
        ->groupBy(DB::raw('DATE(Tgl_Penjualan)'))
        ->orderBy(DB::raw('DATE(Tgl_Penjualan)'), 'ASC')
        ->get();

        $labels = $penjualanPerTanggal->pluck('tanggal');
        $data = $penjualanPerTanggal->pluck('total');

        // ============================
        // 3. GRAFIK DONUT STATUS STOK
        // ============================

        $stokAman = Stok::where('Status', 'Aman')->count();
        $stokMenipis = Stok::where('Status', 'Menipis')->count();
        $stokHabis = Stok::where('Status', 'Habis')->count();

        // ============================
        // 4. GRAFIK MEMBER PER BULAN (Bar)
        // ============================

        $membersPerMonth = Member::selectRaw('MONTH(Created_At) as month, COUNT(*) as total')
            ->whereYear('Created_At', date('Y'))
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        $memberLabels = [];
        $memberData = [];

        for ($i = 1; $i <= 12; $i++) {
            $memberLabels[] = Carbon::create()->month($i)->format('F');
            $memberData[] = $membersPerMonth->firstWhere('month', $i)->total ?? 0;
        }

        // ============================
        // RETURN VIEW
        // ============================

        return view('dashboard', compact(
            'totalPenjualan',
            'jumlahTransaksi',
            'rataRata',
            'labaKotor',
            'totalBiaya',
            'labels',
            'data',
            'stokAman',
            'stokMenipis',
            'stokHabis',
            'memberLabels',
            'memberData'
        ));
    }
}
