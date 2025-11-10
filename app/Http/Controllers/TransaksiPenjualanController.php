<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use App\Models\TransaksiPenjualan;
use App\Models\Member;

class TransaksiPenjualanController extends Controller
{
    /**
     * Simpan transaksi penjualan dari halaman kasir
     * Mendukung poin member:
     *  - 1 poin = Rp100 (sebagai potongan)
     *  - Poin didapat = floor(total_setelah_diskon / 1000)
     */
    public function store(Request $request)
    {
        $request->validate([
            'items'                 => 'required|array|min:1',
            'total'                 => 'required|integer|min:0',   // total BRUTO sebelum diskon poin
            'metode'                => 'nullable|string|max:50',

            // >>> sesuai payload dari FE kamu (nested "member")
            'member'                => 'nullable|array',
            'member.id'             => 'nullable|string|max:20',
            'member.poin_pakai'     => 'nullable|integer|min:0',
        ]);

        $items      = $request->input('items', []);
        $totalBruto = (int) $request->input('total', 0);
        $metode     = $request->input('metode', 'Tunai');

        // ambil dari nested 'member'
        $memberId   = $request->input('member.id');
        $poinPakai  = (int) $request->input('member.poin_pakai', 0);

        // aturan poin
        $RP_PER_POIN = 100;    // 1 poin = Rp100
        $RP_PER_POIN_EARN = 1000; // 1 poin didapat per Rp1.000

        // Ambil pengguna yang sedang login dari session
        // Auth logic menyimpan data user di session key 'user' pada AuthController
        $user = Session::get('user'); // array: ['id'=>..,'role'=>..]
        $managerId = null;
        $pegawaiId = null;
        if (is_array($user)) {
            if (!empty($user['role']) && $user['role'] === 'manager') {
                // simpan langsung id manager (jangan bungkus dalam array)
                $managerId = $user['id'];
            } elseif (!empty($user['role']) && $user['role'] === 'pegawai') {
                $pegawaiId = $user['id'];
            }
        }
        Log::info('SESSION USER:', Session::get('user'));

    try {
        $result = DB::transaction(function () use (
            $items, $totalBruto, $metode, $memberId, $poinPakai,
            $RP_PER_POIN, $RP_PER_POIN_EARN, $manager
        ) {
            $member = null;
            $poinDipakai = 0;
            $potonganRp  = 0;
            $totalBayar  = $totalBruto;
            $poinDidapat = 0;
            $poinAkhir   = null;

            if ($memberId) {
                // kunci row biar saldo poin konsisten
                $member = Member::where('ID_Member', $memberId)->lockForUpdate()->first();
                if ($member) {
                    $maxByTotal = intdiv($totalBruto, $RP_PER_POIN);         // contoh: 23.500 -> 235 poin
                    $maxBySaldo = (int) $member->Poin;
                    $maxPakai   = min($maxByTotal, $maxBySaldo);

                    $poinDipakai = max(0, min($poinPakai, $maxPakai));
                    $potonganRp  = $poinDipakai * $RP_PER_POIN;
                    $totalBayar  = max(0, $totalBruto - $potonganRp);

                    // poin didapat dari nilai SETELAH diskon (kalau mau sebelum diskon, ganti $totalBruto)
                    $poinDidapat = (int) floor($totalBayar / $RP_PER_POIN_EARN);

                    // update saldo poin member
                    $member->Poin = (int)$member->Poin - $poinDipakai + $poinDidapat;
                    $member->save();
                    $poinAkhir = (int) $member->Poin;
                } else {
                    // kalau id ada tapi member gak ketemu, treat tanpa member
                    $memberId = null;
                }
            } else {
                // tanpa member tetap dapet poin? biasanya tidak.
                // kalau mau kasih poin umum, hilangkan else ini dan hitung dari totalBayar.
                $poinDidapat = 0;
            }

                // generate ID TRX
                $last = TransaksiPenjualan::orderBy('ID_Penjualan', 'desc')->value('ID_Penjualan');
                $num  = $last ? ((int) preg_replace('/\D/', '', $last) + 1) : 1;
                $newId = 'TRX' . str_pad($num, 3, '0', STR_PAD_LEFT);

                // SIMPAN TRANSAKSI
                // CATATAN: di sini aku simpan TotalHarga = nominal SETELAH diskon.
                // Kalau kamu mau simpan BRUTO, ganti 'TotalHarga' => $totalBruto,
                // dan tambahkan kolom 'TotalBayar' untuk nilai akhirnya.
                $transaksi = TransaksiPenjualan::create([
                    'ID_Penjualan'      => $newId,
                    'ID_Pegawai'        => $pegawaiId,
                    'ID_Manager'        => $managerId ?? null,
                    'ID_Member'         => $memberId,
                    'Tgl_Penjualan'     => now(),
                    // konsisten dengan kolom di tabel kamu:
                    // ganti ke 'Metode_Pembayaran' ATAU 'MetodeBayar' sesuai yang ada di DB
                    'Metode_Pembayaran' => $metode,
                    'TotalHarga'        => $totalBayar,            // simpan nilai akhirnya
                    'Jumlah_Item'       => count($items),
                    'Status'            => 'Selesai',
                    'Poin_Digunakan'    => $poinDipakai,
                    'Poin_Didapat'      => $poinDidapat,
                    // kalau kamu punya kolom DiskonPoin/TotalBruto/TotalBayar, isi juga:
                    // 'DiskonPoin'        => $potonganRp,
                    // 'TotalBruto'        => $totalBruto,
                    // 'TotalBayar'        => $totalBayar,
                ]);

                // (opsional) simpan detail item satu-satu di tabel detail

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
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
