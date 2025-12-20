<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
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
    private function getLaporanData($start, $end, $entries)
    {
        return TransaksiPenjualan::with(['detailPenjualan.menu', 'member'])
            ->whereDate('Tgl_Penjualan', '>=', $start)
            ->whereDate('Tgl_Penjualan', '<=', $end)
            ->orderBy('Tgl_Penjualan', 'desc')
            ->paginate($entries)
            ->through(function ($trx) {

                // 🧾 Dapatkan nama kasir (manager / pegawai)
                if ($trx->ID_Manager) {
                    $manager = Manager::where('ID_Manager', $trx->ID_Manager)->first();
                    $nama = $manager->Nama_Manager ?? $manager->Username ?? $manager->Email ?? 'Manager Tidak Dikenal';
                } elseif ($trx->ID_Pegawai) {
                    $pegawai = Pegawai::where('ID_Pegawai', $trx->ID_Pegawai)->first();
                    $nama = $pegawai->Nama_Pegawai ?? $pegawai->Username ?? $pegawai->Email ?? 'Pegawai Tidak Dikenal';
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
                                $harga = $detail->Harga ?? ($menu ? $menu->Harga : 0);
                                $qty = $detail->Quantity ?? 0;
                                $subtotal = $detail->Subtotal ?? ($qty * $harga);

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
                                return $detail->Subtotal ?? ($qty * $harga);
                            }),
                        ];
                    })
                    ->toArray();

                // 📋 Return hasil laporan
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
        $entries = $request->get('entries', 10);

        // Default 30 hari terakhir (bukan 7 hari)
        $start = $request->get('start') ?? Carbon::now()->subDays(30)->format('Y-m-d');
        $end   = $request->get('end') ?? Carbon::now()->format('Y-m-d');

        $laporan = $this->getLaporanData($start, $end, $entries);
        $totalMember = \App\Models\Member::whereDate('created_at', '>=', $start)
            ->whereDate('created_at', '<=', $end)
            ->count();


        // Jika user minta export PDF
        if ($request->get('export') === 'pdf') {
            try {
                // Bypass facade to avoid public path resolution issues on shared hosting
                $dompdf = new \Dompdf\Dompdf();
                $dompdf->loadHtml(view('exports.penjualan-pdf', compact('laporan', 'start', 'end', 'totalMember'))->render());
                $dompdf->setPaper('a4', 'portrait');
                $dompdf->render();

                $filename = 'laporan-penjualan-' .
                    Carbon::parse($start)->format('d-m-Y') .
                    '_sd_' .
                    Carbon::parse($end)->format('d-m-Y') .
                    '.pdf';

                return response()->streamDownload(function () use ($dompdf) {
                    echo $dompdf->output();
                }, $filename, [
                    'Content-Type' => 'application/pdf',
                ]);
            } catch (\Exception $e) {
                Log::error('PDF Export Error (Penjualan): ' . $e->getMessage());
                return back()->withErrors('error', '❌ Gagal export PDF. Error: ' . $e->getMessage());
            }
        }

        // ✅ Berhasil menampilkan laporan di halaman
        $totalTransaksi = TransaksiPenjualan::whereDate('Tgl_Penjualan', '>=', $start)
            ->whereDate('Tgl_Penjualan', '<=', $end)
            ->count();

        return view('penjualan', compact('laporan', 'start', 'end', 'entries', 'totalTransaksi'));
    }

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

                // SIMPAN ROW NUMBER SAAT INI (row ini belum diproses)
                // Saat continueImport dipanggil, akan mulai dari rowNo ini lagi
                session(['pending_import_current_index' => $rowNo]);

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
                session()->flash('error', ' Error pada baris ' . $rowNo . 'di Excel : ' . $e->getMessage());
                return back();
            }
        }

        // CLEAR SESSION SETELAH IMPORT SELESAI
        session()->forget(['pending_import_rows', 'pending_import_current_index', 'autoFillMenu']);

        Log::info("✅ Import selesai: {$successCount} baris berhasil diproses");
        return redirect()->route('penjualan.index')
            ->with('success', "✅ Import selesai! {$successCount} baris data berhasil diproses.");
    }

    /**
     * Melanjutkan import setelah menu baru dibuat
     */
    public function continueImport(Request $request)
    {
        // Clear autoFillMenu dari sesi sebelumnya untuk menghindari loop
        session()->forget('autoFillMenu');

        $rows = session('pending_import_rows');
        $processedCount = session('pending_import_current_index', 0);

        if (!$rows) {
            session()->forget(['pending_import_rows', 'pending_import_current_index']);
            return redirect()->route('penjualan.index')
                ->with('error', '❌ Tidak ada data import yang pending.');
        }

        $importer = new PenjualanImport();
        $rowNo = 0;
        $successCount = 0;

        Log::info("🔄 Continue import, total rows: " . count($rows) . ", start from row: {$processedCount}");

        foreach ($rows as $row) {
            $rowNo++;

            // Skip header
            if ($rowNo == 1) {
                Log::info("⏭️ Skip header row");
                continue;
            }

            // Skip rows yang sudah berhasil diproses sebelumnya (berdasarkan row number)
            if ($rowNo < $processedCount) {
                Log::info("⏭️ Skip baris {$rowNo} (sudah diproses sebelumnya)");
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

                // Simpan row number saat ini (row ini belum diproses)
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

        $totalProcessed = $processedCount + $successCount;
        Log::info("✅ Import selesai: Total {$totalProcessed} baris berhasil diproses");
        return redirect()->route('penjualan.index')
            ->with('success', "✅ Import selesai! Total {$totalProcessed} baris data berhasil diproses.");
    }

    /**
     * Hapus satu transaksi penjualan berdasarkan ID_Penjualan
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            // Hapus detail terlebih dahulu untuk menjaga integritas FK
            DB::table('Detail_Penjualan')->where('ID_Penjualan', $id)->delete();
            // Hapus transaksi utama
            $deleted = DB::table('TransaksiPenjualan')->where('ID_Penjualan', $id)->delete();

            DB::commit();

            if ($deleted) {
                return back()->with('success', "✅ Transaksi $id berhasil dihapus.");
            }
            return back()->with('error', "❌ Transaksi $id tidak ditemukan.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Hapus transaksi gagal: ' . $e->getMessage());
            return back()->with('error', '❌ Gagal menghapus transaksi: ' . $e->getMessage());
        }
    }
}
