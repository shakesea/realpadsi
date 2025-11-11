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
                $items, $totalBruto, $metode, $memberId, $poinPakai,
                $RP_PER_POIN, $RP_PER_POIN_EARN, $managerId, $pegawaiId
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
                foreach ($items as $item) {
                    $menuId = $item['id'] ?? $item['ID_Menu'] ?? null;
                    $qtyJual = $item['qty'] ?? $item['Quantity'] ?? 1;
                    if (!$menuId) continue;

                    $bahanList = BahanPenyusun::where('ID_Menu', $menuId)->get();

                    foreach ($bahanList as $bahan) {
                        $jumlahTerpakai = $bahan->Jumlah_Digunakan * $qtyJual;

                        $stok = Stok::where('ID_Barang', $bahan->ID_Barang)->lockForUpdate()->first();

                        if ($stok) {
                            // Cek apakah stok cukup
                            if ($stok->Jumlah_Item < $jumlahTerpakai) {
                                throw new \Exception("Stok bahan {$stok->Nama} tidak mencukupi untuk menu {$menuId}.");
                            }

                            $stok->Jumlah_Item -= $jumlahTerpakai;
                            $stok->Updated_At = now();
                            $stok->save();
                        }
                    }
                }

                Log::info('ITEMS PAYLOAD:', $items);

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
            Log::error('ERROR SIMPAN TRANSAKSI: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
