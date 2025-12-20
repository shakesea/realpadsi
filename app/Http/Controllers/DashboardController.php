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
    /**
     * ===============================
     * DASHBOARD UTAMA (GET)
     * ===============================
     */
    public function index()
    {
        $firstDateRaw = TransaksiPenjualan::min('Tgl_Penjualan');
        $firstDate = $firstDateRaw ? date('Y-m-d', strtotime($firstDateRaw)) : date('Y-m-d');
        $today = date('Y-m-d');

        // === CARD SUMMARY ===
        $totalPenjualan = TransaksiPenjualan::sum('TotalHarga');
        $jumlahTransaksi = TransaksiPenjualan::count();
        $rataRata = TransaksiPenjualan::avg('TotalHarga');
        $labaKotor = $totalPenjualan * 0.3;
        $totalMember = Member::count();

        // === MENU PALING LARIS ===
        $menuTerlaris = DB::table('Detail_Penjualan')
            ->join('Menu', 'Detail_Penjualan.ID_Menu', '=', 'Menu.ID_Menu')
            ->select('Menu.Nama', DB::raw('SUM(Detail_Penjualan.Quantity) as total_jual'))
            ->groupBy('Menu.Nama')
            ->orderByDesc('total_jual')
            ->first();

        $menuPalingLaris = $menuTerlaris ? $menuTerlaris->Nama : '-';

        // === GRAFIK PENJUALAN ===
        $penjualanPerTanggal = TransaksiPenjualan::select(
            DB::raw('DATE(Tgl_Penjualan) as tanggal'),
            DB::raw('SUM(TotalHarga) as total')
        )
            ->groupBy(DB::raw('DATE(Tgl_Penjualan)'))
            ->orderBy(DB::raw('DATE(Tgl_Penjualan)'), 'ASC')
            ->get();

        $labels = $penjualanPerTanggal->pluck('tanggal');
        $data = $penjualanPerTanggal->pluck('total');

        // === STATUS STOK ===
        $stokAman = Stok::where('Status', 'Aman')->count();
        $stokMenipis = Stok::where('Status', 'Menipis')->count();
        $stokHabis = Stok::where('Status', 'Habis')->count();

        // === MEMBER BAR CHART ===
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

        // === TOP MEMBER ===
        $topMembers = Member::orderBy('Poin', 'desc')->take(5)->get();
        $topMemberNames = $topMembers->pluck('Nama');
        $topMemberPoints = $topMembers->pluck('Poin');

        // === TOP STOK (Paling Sering Digunakan dalam Transaksi) ===
        $topStok = DB::table('Bahan_Penyusun')
            ->join('Stok', 'Bahan_Penyusun.ID_Barang', '=', 'Stok.ID_Barang')
            ->join('Menu', 'Bahan_Penyusun.ID_Menu', '=', 'Menu.ID_Menu')
            ->join('Detail_Penjualan', 'Menu.ID_Menu', '=', 'Detail_Penjualan.ID_Menu')
            ->select('Stok.Nama', DB::raw('SUM(Detail_Penjualan.Quantity * Bahan_Penyusun.Jumlah_Digunakan) as total_digunakan'))
            ->groupBy('Stok.ID_Barang', 'Stok.Nama')
            ->orderByDesc('total_digunakan')
            ->limit(10)
            ->get();

        $topStokNames = $topStok->pluck('Nama');
        $topStokCounts = $topStok->pluck('total_digunakan');

        return view('dashboard', compact(
            'totalPenjualan',
            'jumlahTransaksi',
            'rataRata',
            'labaKotor',
            'menuPalingLaris',
            'totalMember',
            'labels',
            'data',
            'stokAman',
            'stokMenipis',
            'stokHabis',
            'memberLabels',
            'memberData',
            'topMemberNames',
            'topMemberPoints',
            'topStokNames',
            'topStokCounts',
            'firstDate',
            'today'
        ));
    }

    /**
     * ===============================
     * AJAX FILTER (POST)
     * ===============================
     */
    public function filterAjax(Request $request)
    {
        $start_date = $request->input('start_date', TransaksiPenjualan::min('Tgl_Penjualan'));
        $end_date   = $request->input('end_date', date('Y-m-d'));

        // === PENJUALAN ===
        $query = TransaksiPenjualan::whereBetween('Tgl_Penjualan', [$start_date, $end_date]);

        $totalPenjualan = $query->sum('TotalHarga');
        $jumlahTransaksi = $query->count();
        $rataRata = $jumlahTransaksi > 0 ? $totalPenjualan / $jumlahTransaksi : 0;
        $labaKotor = $totalPenjualan * 0.3;
        $totalMember = Member::whereBetween('Created_At', [$start_date, $end_date])->count();

        // === MENU PALING LARIS (FILTERED) ===
        $menuTerlaris = DB::table('Detail_Penjualan')
            ->join('Menu', 'Detail_Penjualan.ID_Menu', '=', 'Menu.ID_Menu')
            ->join('TransaksiPenjualan', 'Detail_Penjualan.ID_Penjualan', '=', 'TransaksiPenjualan.ID_Penjualan')
            ->whereBetween('TransaksiPenjualan.Tgl_Penjualan', [$start_date, $end_date])
            ->select('Menu.Nama', DB::raw('SUM(Detail_Penjualan.Quantity) as total_jual'))
            ->groupBy('Menu.Nama')
            ->orderByDesc('total_jual')
            ->first();

        $menuPalingLaris = $menuTerlaris ? $menuTerlaris->Nama : '-';

        // === PENJUALAN PER TANGGAL ===
        $penjualanPerTanggal = $query->selectRaw('DATE(Tgl_Penjualan) as tanggal, SUM(TotalHarga) as total')
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();

        $labels = $penjualanPerTanggal->pluck('tanggal');
        $data = $penjualanPerTanggal->pluck('total');

        // === MEMBER ===
        $memberDataRaw = Member::selectRaw('MONTH(Created_At) as bulan, COUNT(*) as total')
            ->whereBetween('Created_At', [$start_date, $end_date])
            ->groupBy('bulan')
            ->pluck('total', 'bulan');

        $memberLabels = collect(range(1, 12))->map(fn($m) => date('F', mktime(0, 0, 0, $m, 1)));
        $memberData = $memberLabels->map(fn($_, $i) => $memberDataRaw[$i + 1] ?? 0);

        // === STOK ===
        $stokAman = Stok::where('Status', 'Aman')->count();
        $stokMenipis = Stok::where('Status', 'Menipis')->count();
        $stokHabis = Stok::where('Status', 'Habis')->count();

        // === TOP MEMBER & STOK ===
        $topMembers = Member::orderBy('Poin', 'desc')->take(5)->get();
        $topMemberNames = $topMembers->pluck('Nama');
        $topMemberPoints = $topMembers->pluck('Poin');

        // Top Stok (filtered by date range)
        $topStok = DB::table('Bahan_Penyusun')
            ->join('Stok', 'Bahan_Penyusun.ID_Barang', '=', 'Stok.ID_Barang')
            ->join('Menu', 'Bahan_Penyusun.ID_Menu', '=', 'Menu.ID_Menu')
            ->join('Detail_Penjualan', 'Menu.ID_Menu', '=', 'Detail_Penjualan.ID_Menu')
            ->join('TransaksiPenjualan', 'Detail_Penjualan.ID_Penjualan', '=', 'TransaksiPenjualan.ID_Penjualan')
            ->whereBetween('TransaksiPenjualan.Tgl_Penjualan', [$start_date, $end_date])
            ->select('Stok.Nama', DB::raw('SUM(Detail_Penjualan.Quantity * Bahan_Penyusun.Jumlah_Digunakan) as total_digunakan'))
            ->groupBy('Stok.ID_Barang', 'Stok.Nama')
            ->orderByDesc('total_digunakan')
            ->limit(10)
            ->get();

        $topStokNames = $topStok->pluck('Nama');
        $topStokCounts = $topStok->pluck('total_digunakan');

        // === RETURN JSON KE DASHBOARD (AJAX) ===
        return response()->json([
            'totalPenjualan' => number_format($totalPenjualan, 0, ',', '.'),
            'jumlahTransaksi' => $jumlahTransaksi,
            'rataRata' => number_format($rataRata, 0, ',', '.'),
            'labaKotor' => number_format($labaKotor, 0, ',', '.'),
            'menuPalingLaris' => $menuPalingLaris,
            'totalMember' => $totalMember,
            'labels' => $labels,
            'data' => $data,
            'memberLabels' => $memberLabels,
            'memberData' => $memberData,
            'stok' => [
                'Aman' => $stokAman,
                'Menipis' => $stokMenipis,
                'Habis' => $stokHabis,
            ],
            'topMemberNames' => $topMemberNames,
            'topMemberPoints' => $topMemberPoints,
            'topStokNames' => $topStokNames,
            'topStokCounts' => $topStokCounts,
        ]);
    }
}
