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
        // === 1️⃣ CARD SUMMARY ===
        $totalPenjualan = TransaksiPenjualan::sum('TotalHarga');
        $jumlahTransaksi = TransaksiPenjualan::count();
        $rataRata = TransaksiPenjualan::avg('TotalHarga');
        $totalBiaya = 0;
        $labaKotor = $totalPenjualan * 0.3;
        $totalMember = Member::count();

        // === 2️⃣ GRAFIK PENJUALAN ===
        $penjualanPerTanggal = TransaksiPenjualan::select(
            DB::raw('DATE(Tgl_Penjualan) as tanggal'),
            DB::raw('SUM(TotalHarga) as total')
        )
            ->groupBy(DB::raw('DATE(Tgl_Penjualan)'))
            ->orderBy(DB::raw('DATE(Tgl_Penjualan)'), 'ASC')
            ->get();

        $labels = $penjualanPerTanggal->pluck('tanggal');
        $data = $penjualanPerTanggal->pluck('total');

        // === 3️⃣ STATUS STOK ===
        $stokAman = Stok::where('Status', 'Aman')->count();
        $stokMenipis = Stok::where('Status', 'Menipis')->count();
        $stokHabis = Stok::where('Status', 'Habis')->count();

        // === 4️⃣ MEMBER BAR CHART ===
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

        // === 5️⃣ TOP MEMBER ===
        $topMembers = Member::orderBy('Poin', 'desc')->take(5)->get();
        $topMemberNames = $topMembers->pluck('Nama');
        $topMemberPoints = $topMembers->pluck('Poin');

        // === 6️⃣ TOP STOK SERING DIGUNAKAN ===
        $topStok = Stok::select('Nama', 'Jumlah_Item')
            ->orderBy('Jumlah_Item', 'asc') // stok paling sedikit berarti paling sering digunakan
            ->limit(10)
            ->get();

        $topStokNames = $topStok->pluck('Nama');
        $topStokCounts = $topStok->pluck('Jumlah_Item');

        return view('dashboard', compact(
            'totalPenjualan',
            'jumlahTransaksi',
            'rataRata',
            'labaKotor',
            'totalBiaya',
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
            'topStokCounts'
        ));
    }

    /**
     * ===============================
     * AJAX FILTER (POST)
     * ===============================
     */
    public function filterAjax(Request $request)
    {
        $start_date = $request->input('start_date', date('Y-m-01'));
        $end_date = $request->input('end_date', date('Y-m-t'));

        // === PENJUALAN ===
        $query = TransaksiPenjualan::whereBetween('Tgl_Penjualan', [$start_date, $end_date]);

        $totalPenjualan = $query->sum('TotalHarga');
        $jumlahTransaksi = $query->count();
        $rataRata = $jumlahTransaksi > 0 ? $totalPenjualan / $jumlahTransaksi : 0;
        $labaKotor = $totalPenjualan * 0.3;
        $totalBiaya = 0;

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
        $totalMember = Member::count();
        $topMembers = Member::orderBy('Poin', 'desc')->take(5)->get();
        $topMemberNames = $topMembers->pluck('Nama');
        $topMemberPoints = $topMembers->pluck('Poin');

        $topStok = Stok::select('Nama', 'Jumlah_Item')
            ->orderBy('Jumlah_Item', 'asc')
            ->limit(10)
            ->get();

        $topStokNames = $topStok->pluck('Nama');
        $topStokCounts = $topStok->pluck('Jumlah_Item');

        return response()->json([
            'totalPenjualan' => number_format($totalPenjualan, 0, ',', '.'),
            'jumlahTransaksi' => $jumlahTransaksi,
            'rataRata' => number_format($rataRata, 0, ',', '.'),
            'labaKotor' => number_format($labaKotor, 0, ',', '.'),
            'totalBiaya' => number_format($totalBiaya, 0, ',', '.'),
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
