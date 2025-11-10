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
        $RP_PER_POIN = 100;      // 1 poin = Rp100
        $RP_PER_POIN_EARN = 1000; // 1 poin didapat per Rp1.000

        // Ambil pengguna yang sedang login dari session
        $user = Session::get('user'); // array: ['id'=>..,'role'=>..]
        $managerId = null;
        $pegawaiId = null;
        if (is_array($user)) {
            if (!empty($user['role']) && $user['role'] === 'manager') {
                $managerId = $user['id'];
            } elseif (!empty($user['role']) && $user['role'] === 'pegawai') {
                $pegawaiId = $user['id'];
            }
        }

        Log::info('SESSION USER:', Session::get('user'));

        try {
            $result = DB::transaction(function () use (
                $items, $totalBruto, $metode, $memberId, $poinPakai,
                $RP_PER_POIN, $RP_PER_POIN_EARN, $managerId, $pegawaiId
            ) {
                $member = null;
                $poinDipakai = 0;
                $potonganRp  = 0;
                $totalBayar  = $totalBruto;
                $poinDidapat = 0;
                $poinAkhir   = null;

                if ($memberId) {
                    // Lock row agar saldo poin konsisten
                    $member = Member::where('ID_Member', $memberId)->lockForUpdate()->first();

                    if ($member) {
                        // Pastikan poin yang digunakan tidak melebihi saldo dan batas berdasarkan total
                        $maxByTotal = intdiv($totalBruto, $RP_PER_POIN); // contoh: 23.500 -> 235 poin
                        $maxBySaldo = (int) $member->Poin;
                        $maxPakai   = min($maxByTotal, $maxBySaldo);

                        $poinDipakai = max(0, min($poinPakai, $maxPakai));

                        // Hitung potongan harga dari poin
                        $potonganRp  = $poinDipakai * $RP_PER_POIN;

                        // Total yang harus dibayar setelah potongan (tidak boleh negatif)
                        $totalBayar  = max(0, $totalBruto - $potonganRp);

                        // ✅ Member tetap mendapatkan poin dari total SETELAH potongan
                        $poinDidapat = (int) floor($totalBayar / $RP_PER_POIN_EARN);

                        // Update saldo poin akhir
                        $member->Poin = (int)$member->Poin - $poinDipakai + $poinDidapat;
                        $member->save();

                        $poinAkhir = (int) $member->Poin;
                    } else {
                        // Jika ID tidak ditemukan, abaikan sebagai non-member
                        $memberId = null;
                    }
                } else {
                    // Non-member tetap bisa transaksi, tapi tidak dapat poin
                    $poinDidapat = 0;
                }

                // Generate ID Transaksi baru
                $last = TransaksiPenjualan::orderBy('ID_Penjualan', 'desc')->value('ID_Penjualan');
                $num  = $last ? ((int) preg_replace('/\D/', '', $last) + 1) : 1;
                $newId = 'TRX' . str_pad($num, 3, '0', STR_PAD_LEFT);

                // SIMPAN TRANSAKSI
                $transaksi = TransaksiPenjualan::create([
                    'ID_Penjualan'      => $newId,
                    'ID_Pegawai'        => $pegawaiId,
                    'ID_Manager'        => $managerId,
                    'ID_Member'         => $memberId,
                    'Tgl_Penjualan'     => now(),
                    // kolom disesuaikan dengan DB
                    'Metode_Pembayaran' => $metode,
                    'TotalHarga'        => $totalBayar,            
                    'Jumlah_Item'       => count($items),
                    'Status'            => 'Selesai',
                    'Poin_Digunakan'    => $poinDipakai,
                    'Poin_Didapat'      => $poinDidapat,
                    // jika kolom tambahan tersedia, aktifkan:
                    // 'DiskonPoin'     => $potonganRp,
                    // 'TotalBruto'     => $totalBruto,
                    // 'TotalBayar'     => $totalBayar,
                ]);

                // 🔹 Simpan detail item ke tabel Detail_Penjualan
                foreach ($items as $item) {
                    // Ambil nilai ID_Menu dan lainnya secara aman
                    $menuId  = $item['id'] ?? $item['ID_Menu'] ?? null;
                    $qty     = $item['qty'] ?? $item['Quantity'] ?? 1;
                    $harga   = $item['harga'] ?? $item['Harga'] ?? 0;

                    // Jika tidak ada ID_Menu, lewati item ini (biar gak error)
                    if (!$menuId) continue;

                    DB::table('Detail_Penjualan')->insert([
                        'ID_Detail_Penjualan' => 'DTL' . str_pad(rand(1, 9999), 5, '0', STR_PAD_LEFT),
                        'ID_Menu'             => $menuId,
                        'ID_Penjualan'        => $transaksi->ID_Penjualan,
                        'Quantity'            => $qty,
                        'Subtotal'            => $qty * $harga,
                    ]);
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
