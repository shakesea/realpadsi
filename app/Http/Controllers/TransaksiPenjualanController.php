<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use App\Models\TransaksiPenjualan;
use App\Models\Member;
use App\Models\BahanPenyusun;
use App\Models\Stok;

class TransaksiPenjualanController extends Controller
{
    public function store(Request $request)
    {
        // Generate unique transaction ID untuk tracking
        $trackId = uniqid('TXN_', true);
        Log::info("==== MULAI TRANSAKSI {$trackId} ====");
        Log::info("Stack trace:", ['trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)]);

        $request->validate([
            'items'                 => 'required|array|min:1',
            'total'                 => 'required|integer|min:0',
            'metode'                => 'nullable|string|max:50',
            'member'                => 'nullable|array',
            'member.id'             => 'nullable|string|max:20',
            'member.poin_pakai'     => 'nullable|integer|min:0',
        ]);

        $items      = $request->input('items', []);
        $totalBruto = (int) $request->input('total', 0);
        $metode     = $request->input('metode', 'Tunai');
        $memberId   = $request->input('member.id');
        $poinPakai  = (int) $request->input('member.poin_pakai', 0);

        Log::info("[{$trackId}] Items payload:", $items);

        // aturan poin
        $RP_PER_POIN = 100;        // 1 poin = Rp100
        $RP_PER_POIN_EARN = 1000;  // 1 poin didapat per Rp1.000

        // Ambil pengguna login
        $user = Session::get('user');
        $managerId = $pegawaiId = null;

        if (is_array($user)) {
            if (($user['role'] ?? '') === 'manager') $managerId = $user['id'];
            if (($user['role'] ?? '') === 'pegawai') $pegawaiId = $user['id'];
        }

        Log::info('SESSION USER:', Session::get('user'));

        try {
            $result = DB::transaction(function () use (
                $items,
                $totalBruto,
                $metode,
                $memberId,
                $poinPakai,
                $RP_PER_POIN,
                $RP_PER_POIN_EARN,
                $managerId,
                $pegawaiId,
                $trackId
            ) {
                // ===========================
                // 1️⃣ PENGOLAHAN MEMBER & POIN
                // ===========================
                $member = null;
                $poinDipakai = $potonganRp = 0;
                $totalBayar = $totalBruto;
                $poinDidapat = $poinAkhir = 0;

                if ($memberId) {
                    $member = Member::where('ID_Member', $memberId)->lockForUpdate()->first();

                    if ($member) {
                        $maxByTotal = intdiv($totalBruto, $RP_PER_POIN);
                        $maxBySaldo = (int) $member->Poin;
                        $maxPakai = min($maxByTotal, $maxBySaldo);

                        $poinDipakai = max(0, min($poinPakai, $maxPakai));
                        $potonganRp  = $poinDipakai * $RP_PER_POIN;
                        $totalBayar  = max(0, $totalBruto - $potonganRp);
                        $poinDidapat = (int) floor($totalBayar / $RP_PER_POIN_EARN);

                        $member->Poin = (int)$member->Poin - $poinDipakai + $poinDidapat;
                        $member->save();
                        $poinAkhir = (int) $member->Poin;
                    } else {
                        $memberId = null; // jika ID tidak valid
                    }
                }

                // ===========================
                // 0️⃣ PRECHECK STOK BAHAN (HARUS CUKUP)
                // ===========================
                $noStock = [];
                $insufficient = [];
                foreach ($items as $item) {
                    $menuId = $item['id'] ?? $item['ID_Menu'] ?? null;
                    $qtyJual = $item['qty'] ?? $item['Quantity'] ?? 1;
                    if (!$menuId) continue;

                    $bahanList = BahanPenyusun::where('ID_Menu', $menuId)->get();
                    foreach ($bahanList as $bahan) {
                        $required = (int) $bahan->Jumlah_Digunakan * (int) $qtyJual;
                        if ($required <= 0) continue; // tidak butuh bahan

                        $stok = Stok::where('ID_Barang', $bahan->ID_Barang)->lockForUpdate()->first();

                        $namaBahan = $stok ? $stok->Nama : ($bahan->ID_Barang . ' (tidak ditemukan)');
                        if (!$stok || (int)$stok->Jumlah_Item <= 0) {
                            $noStock[] = $namaBahan;
                        } elseif ((int)$stok->Jumlah_Item < $required) {
                            $insufficient[] = $namaBahan . " (butuh {$required}, sisa {$stok->Jumlah_Item})";
                        }
                    }
                }

                if (!empty($noStock) || !empty($insufficient)) {
                    $msg = 'Transaksi gagal. ';
                    if (!empty($noStock)) {
                        $msg .= 'Bahan habis: ' . implode(', ', array_unique($noStock)) . '. ';
                    }
                    if (!empty($insufficient)) {
                        $msg .= 'Bahan tidak cukup: ' . implode(', ', array_unique($insufficient)) . '.';
                    }
                    throw new \RuntimeException('STOK_KURANG|' . $msg);
                }

                // Ringkasan stok sebelum menyimpan transaksi: total stok awal dan rencana pemakaian
                $plannedUsage = [];
                $lockedStocks = [];
                foreach ($items as $item) {
                    $menuId = $item['id'] ?? $item['ID_Menu'] ?? null;
                    $qtyJual = $item['qty'] ?? $item['Quantity'] ?? 1;
                    if (!$menuId) continue;

                    $bahanList = BahanPenyusun::where('ID_Menu', $menuId)->get();
                    foreach ($bahanList as $bahan) {
                        $required = (int) $bahan->Jumlah_Digunakan * (int) $qtyJual;
                        if ($required <= 0) continue;
                        if (!isset($plannedUsage[$bahan->ID_Barang])) {
                            $plannedUsage[$bahan->ID_Barang] = 0;
                        }
                        $plannedUsage[$bahan->ID_Barang] += $required;
                    }
                }

                if (!empty($plannedUsage)) {
                    Log::info("[{$trackId}] === RINGKASAN STOK SEBELUM SIMPAN ===");
                    foreach ($plannedUsage as $idBarang => $pakaiTotal) {
                        // Lock and cache the model instance to ensure consistent reads later
                        $stok = Stok::where('ID_Barang', $idBarang)->lockForUpdate()->first();
                        if ($stok) {
                            $lockedStocks[$idBarang] = $stok; // reuse the same locked instance
                        }
                        $nama = $stok ? $stok->Nama : $idBarang;
                        $awal = $stok ? (int)$stok->Jumlah_Item : 0;
                        Log::info("[{$trackId}]   {$nama} (ID: {$idBarang}) -> stok_awal(locked): {$awal}, akan_dipakai: {$pakaiTotal}");
                    }
                }

                // ===========================
                // 2️⃣ SIMPAN TRANSAKSI UTAMA
                // ===========================
                $last = TransaksiPenjualan::orderBy('ID_Penjualan', 'desc')->value('ID_Penjualan');
                $num  = $last ? ((int) preg_replace('/\D/', '', $last) + 1) : 1;
                $newId = 'TRX' . str_pad($num, 3, '0', STR_PAD_LEFT);

                $transaksi = TransaksiPenjualan::create([
                    'ID_Penjualan'      => $newId,
                    'ID_Pegawai'        => $pegawaiId,
                    'ID_Manager'        => $managerId,
                    'ID_Member'         => $memberId,
                    'Tgl_Penjualan'     => now(),
                    'Metode_Pembayaran' => $metode,
                    'TotalHarga'        => $totalBayar,
                    'Jumlah_Item'       => count($items),
                    'Status'            => 'Selesai',
                    'Poin_Digunakan'    => $poinDipakai,
                    'Poin_Didapat'      => $poinDidapat,
                ]);

                // ===========================
                // 3️⃣ SIMPAN DETAIL PENJUALAN
                // ===========================
                foreach ($items as $item) {
                    $menuId = $item['id'] ?? $item['ID_Menu'] ?? null;
                    $qty    = $item['qty'] ?? $item['Quantity'] ?? 1;
                    $harga  = $item['harga'] ?? $item['Harga'] ?? 0;
                    if (!$menuId) continue;

                    DB::table('Detail_Penjualan')->insert([
                        'ID_Detail_Penjualan' => 'DTL' . str_pad(rand(1, 9999), 5, '0', STR_PAD_LEFT),
                        'ID_Menu'             => $menuId,
                        'ID_Penjualan'        => $transaksi->ID_Penjualan,
                        'Quantity'            => $qty,
                        'Subtotal'            => $qty * $harga,
                    ]);
                }

                // ===========================
                // 4️⃣ PENGURANGAN STOK BAHAN
                // ===========================
                // Kumpulkan total penggunaan bahan dari semua item
                $totalBahanDigunakan = [];

                foreach ($items as $item) {
                    $menuId = $item['id'] ?? $item['ID_Menu'] ?? null;
                    $qtyJual = $item['qty'] ?? $item['Quantity'] ?? 1;
                    if (!$menuId) continue;

                    // Ambil bahan penyusun untuk menu ini
                    $bahanList = BahanPenyusun::where('ID_Menu', $menuId)->get();

                    Log::info("[{$trackId}] Menu {$menuId} - Qty: {$qtyJual} - Bahan count: " . $bahanList->count());

                    // Akumulasi jumlah bahan yang digunakan
                    foreach ($bahanList as $bahan) {
                        $jumlahTerpakai = $bahan->Jumlah_Digunakan * $qtyJual;

                        if (!isset($totalBahanDigunakan[$bahan->ID_Barang])) {
                            $totalBahanDigunakan[$bahan->ID_Barang] = 0;
                        }

                        $totalBahanDigunakan[$bahan->ID_Barang] += $jumlahTerpakai;

                        Log::info("[{$trackId}]   Bahan {$bahan->ID_Barang} ({$bahan->ID_Penyusun}): Jumlah_Digunakan={$bahan->Jumlah_Digunakan} x Qty={$qtyJual} = {$jumlahTerpakai} (akumulasi: {$totalBahanDigunakan[$bahan->ID_Barang]})");
                    }
                }

                Log::info("[{$trackId}] === TOTAL AKUMULASI BAHAN ===");
                foreach ($totalBahanDigunakan as $idBarang => $total) {
                    Log::info("[{$trackId}]   {$idBarang}: {$total}");
                }

                // Sekarang kurangi stok sekali saja per bahan
                foreach ($totalBahanDigunakan as $idBarang => $totalDigunakan) {
                    // Reuse the locked instance captured in the summary to keep values consistent
                    $stok = $lockedStocks[$idBarang] ?? Stok::where('ID_Barang', $idBarang)->lockForUpdate()->first();

                    if ($stok) {
                        $stokSebelum = (int) $stok->Jumlah_Item;
                        $pakai = (int) $totalDigunakan;

                        // Jika stok tidak cukup, clamp ke 0 dan lanjutkan transaksi
                        if ($stokSebelum < $pakai) {
                            Log::warning("[{$trackId}] Stok kurang untuk {$stok->Nama} (ID: {$idBarang}). Butuh: {$pakai}, Tersedia: {$stokSebelum}. Transaksi tetap dilanjutkan, stok diset ke 0.");
                            $pakai = $stokSebelum; // gunakan sisa yang ada saja
                        }

                        // Logkan ringkasan konsisten sebelum pengurangan menggunakan nilai terkunci
                        Log::info("[{$trackId}]   RINGKASAN TERKUNCI {$stok->Nama} (ID: {$idBarang}) -> stok_awal: {$stokSebelum}, akan_dipakai: {$pakai}");

                        $stok->Jumlah_Item = $stokSebelum - $pakai; // tidak akan < 0
                        $stokSesudah = $stok->Jumlah_Item;
                        $stok->Updated_At = now();
                        $stok->save();

                        Log::info("[{$trackId}] ✓ Mengurangi stok {$stok->Nama} (ID: {$idBarang}): {$stokSebelum} - {$pakai} = {$stokSesudah}");
                    } else {
                        Log::warning("[{$trackId}] ✗ Stok dengan ID {$idBarang} tidak ditemukan!");
                    }
                }

                Log::info("[{$trackId}] ==== SELESAI TRANSAKSI ====");

                return [
                    'trx'         => $transaksi,
                    'total_bayar' => $totalBayar,
                    'potongan'    => $potonganRp,
                    'poin_pakai'  => $poinDipakai,
                    'poin_dapat'  => $poinDidapat,
                    'poin_akhir'  => $poinAkhir,
                ];
            });

            return response()->json([
                'status'             => 'success',
                'message'            => 'Transaksi berhasil disimpan',
                'id_transaksi'       => $result['trx']->ID_Penjualan,
                'total_asal'         => $totalBruto,
                'potongan_dari_poin' => $result['potongan'],
                'total_bayar'        => $result['total_bayar'],
                'poin_digunakan'     => $result['poin_pakai'],
                'poin_didapat'       => $result['poin_dapat'],
                'poin_member_akhir'  => $result['poin_akhir'],
            ]);
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            Log::error('ERROR SIMPAN TRANSAKSI: ' . $msg, ['trace' => $e->getTraceAsString()]);

            // Jika error karena stok kurang/habis, kembalikan 422 agar UI bisa menampilkan pesan validasi
            if (strpos($msg, 'STOK_KURANG|') === 0) {
                $cleanMsg = substr($msg, strlen('STOK_KURANG|'));
                return response()->json([
                    'status'  => 'error',
                    'message' => $cleanMsg,
                ], 422);
            }

            return response()->json([
                'status'  => 'error',
                'message' => $msg,
            ], 500);
        }
    }
    public function import(Request $request)
    {
        try {
            Excel::import(new PenjualanImport, $request->file('file'));
            return back()->with('success', '✔️ Import berhasil!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
