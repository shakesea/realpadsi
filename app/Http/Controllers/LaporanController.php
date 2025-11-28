<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\TransaksiPenjualan;
use App\Models\Manager;
use App\Models\Pegawai;
use App\Models\Menu;
use App\Models\Stok;
use App\Helpers\MenuHelper;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Imports\PenjualanImport;
use PhpOffice\PhpSpreadsheet\IOFactory;

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

    /**
     * Handle GET request ke /penjualan/import
     * Cek apakah ada pending import, jika ada tampilkan modal, jika tidak redirect ke penjualan
     */
    public function showImport(Request $request)
    {
        // Cek apakah ada pending import
        $autoFill = session('autoFillMenu');

        if ($autoFill) {
            // Ada pending import, tampilkan modal create menu
            return view('modal.import-create-menu', [
                'autoFill' => $autoFill,
                'stok' => Stok::all(),
                'categories' => MenuHelper::getCategories()
            ]);
        }

        // Tidak ada pending import, redirect ke halaman penjualan
        return redirect()->route('penjualan.index')
            ->with('info', '💡 Upload file CSV/Excel untuk melakukan import.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        $path = $request->file('file')->getPathname();
        $spreadsheet = IOFactory::load($path);
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        // SIMPAN SEMUA ROWS KE SESSION SUPAYA BISA DILANJUTKAN NANTI
        session(['pending_import_rows' => $rows]);
        session(['pending_import_current_index' => 0]); // Belum ada yang diproses

        $importer = new PenjualanImport();
        $rowNo = 0;
        $successCount = 0;

        Log::info("📄 Memulai import, total rows: " . count($rows));

        foreach ($rows as $row) {
            $rowNo++;

            if ($rowNo == 1) {
                Log::info("⏭️ Skip header row");
                continue; // skip header
            }

            $mapped = [
                'id_penjualan'   => $row['A'] ?? null,
                'tgl_penjualan'  => $row['B'] ?? null,
                'id_pegawai'     => $row['C'] ?? null,
                'id_manager'     => $row['D'] ?? null,
                'id_member'      => $row['E'] ?? null,
                'metode_pembayaran' => $row['F'] ?? null,
                'totalharga'     => $row['G'] ?? null,
                'jumlah_item'    => $row['H'] ?? null,
                'status'         => $row['I'] ?? null,
                'poin_digunakan' => $row['J'] ?? null,
                'poin_didapat'   => $row['K'] ?? null,
                'id_menu'        => $row['L'] ?? null,
                'quantity'       => $row['M'] ?? null,
                'subtotal'       => $row['N'] ?? null,
                // Tambahan kolom untuk autofill menu (jika ada di CSV)
                'nama_menu'      => $row['O'] ?? null,
                'kategori_menu'  => $row['P'] ?? null,
                'harga_menu'     => $row['Q'] ?? null,
            ];

            Log::info("🔍 Baris {$rowNo}: ID_Penjualan={$mapped['id_penjualan']}, ID_Menu={$mapped['id_menu']}");

            // CEK APAKAH MENU SUDAH ADA DI DATABASE
            if (!empty($mapped['id_menu']) && !Menu::where('ID_Menu', $mapped['id_menu'])->exists()) {
                Log::info("⚠️ Menu {$mapped['id_menu']} tidak ditemukan di baris {$rowNo}, tampilkan modal create");

                // SIMPAN INDEX ROW INI DIKURANGI 1 (supaya row ini diproses ulang di continueImport)
                session(['pending_import_current_index' => $rowNo - 1]);

                // SIAPKAN DATA AUTOFILL UNTUK POPUP CREATE MENU
                $autoFill = [
                    'id_menu'  => $mapped['id_menu'],
                    'nama'     => $mapped['nama_menu'] ?? 'Menu ' . $mapped['id_menu'],
                    'kategori' => $mapped['kategori_menu'] ?? '',
                    'harga'    => $mapped['harga_menu'] ?? $mapped['subtotal'] ?? 0,
                ];

                session(['autoFillMenu' => $autoFill]);

                // TAMPILKAN MODAL CREATE MENU
                return view('modal.import-create-menu', [
                    'autoFill' => $autoFill,
                    'stok' => Stok::all(),
                    'categories' => MenuHelper::getCategories()
                ]);
            }

            // KALAU MENU SUDAH ADA → LANJUT PROSES IMPORT
            try {
                $result = $importer->handle($mapped);
                $successCount++;
                Log::info("✅ Import baris {$rowNo}: Transaksi {$result->ID_Penjualan} berhasil disimpan");
            } catch (\Exception $e) {
                // Simpan error ke session
                Log::error("❌ Import baris {$rowNo} gagal: " . $e->getMessage());
                session()->flash('error', '❌ Error pada baris ' . $rowNo . ': ' . $e->getMessage());
                return back();
            }
        }

        // CLEAR SESSION SETELAH IMPORT SELESAI
        session()->forget(['pending_import_rows', 'pending_import_current_index', 'autoFillMenu']);

        Log::info("✅ Import selesai: {$successCount} transaksi berhasil disimpan ke database");
        return back()->with('success', "✅ Import berhasil! {$successCount} transaksi telah disimpan ke database dan akan muncul di riwayat penjualan.");
    }

    /**
     * Melanjutkan import setelah menu baru dibuat
     */
    public function continueImport(Request $request)
    {
        $rows = session('pending_import_rows');
        $currentIndex = session('pending_import_current_index', 0);

        if (!$rows) {
            return back()->withErrors(['error' => '❌ Tidak ada data import yang pending.']);
        }

        $importer = new PenjualanImport();
        $rowNo = 0;
        $successCount = 0;

        Log::info("🔄 Continue import, total rows: " . count($rows) . ", currentIndex: {$currentIndex}");

        foreach ($rows as $row) {
            $rowNo++;

            // Skip header dan rows yang sudah diproses
            if ($rowNo == 1 || $rowNo <= $currentIndex) {
                Log::info("⏭️ Skip baris {$rowNo} (header atau sudah diproses, currentIndex={$currentIndex})");
                continue;
            }

            $mapped = [
                'id_penjualan'   => $row['A'] ?? null,
                'tgl_penjualan'  => $row['B'] ?? null,
                'id_pegawai'     => $row['C'] ?? null,
                'id_manager'     => $row['D'] ?? null,
                'id_member'      => $row['E'] ?? null,
                'metode_pembayaran' => $row['F'] ?? null,
                'totalharga'     => $row['G'] ?? null,
                'jumlah_item'    => $row['H'] ?? null,
                'status'         => $row['I'] ?? null,
                'poin_digunakan' => $row['J'] ?? null,
                'poin_didapat'   => $row['K'] ?? null,
                'id_menu'        => $row['L'] ?? null,
                'quantity'       => $row['M'] ?? null,
                'subtotal'       => $row['N'] ?? null,
                'nama_menu'      => $row['O'] ?? null,
                'kategori_menu'  => $row['P'] ?? null,
                'harga_menu'     => $row['Q'] ?? null,
            ];

            // CEK APAKAH MENU SUDAH ADA DI DATABASE
            if (!empty($mapped['id_menu']) && !Menu::where('ID_Menu', $mapped['id_menu'])->exists()) {

                session(['pending_import_current_index' => $rowNo]);

                $autoFill = [
                    'id_menu'  => $mapped['id_menu'],
                    'nama'     => $mapped['nama_menu'] ?? 'Menu ' . $mapped['id_menu'],
                    'kategori' => $mapped['kategori_menu'] ?? '',
                    'harga'    => $mapped['harga_menu'] ?? $mapped['subtotal'] ?? 0,
                ];

                session(['autoFillMenu' => $autoFill]);

                return view('modal.import-create-menu', [
                    'autoFill' => $autoFill,
                    'stok' => Stok::all(),
                    'categories' => MenuHelper::getCategories()
                ]);
            }

            // PROSES IMPORT
            try {
                $result = $importer->handle($mapped);
                $successCount++;
                Log::info("✅ Import baris {$rowNo}: Transaksi {$result->ID_Penjualan} berhasil disimpan");
            } catch (\Exception $e) {
                Log::error("❌ Import baris {$rowNo} gagal: " . $e->getMessage());
                session()->flash('error', '❌ Error pada baris ' . $rowNo . ': ' . $e->getMessage());
                return back();
            }
        }

        // CLEAR SESSION SETELAH SELESAI
        session()->forget(['pending_import_rows', 'pending_import_current_index', 'autoFillMenu']);

        Log::info("✅ Import dilanjutkan selesai: {$successCount} transaksi berhasil disimpan ke database");
        return back()->with('success', "✅ Import dilanjutkan berhasil! {$successCount} transaksi telah disimpan ke database dan akan muncul di riwayat penjualan.");
    }
}
