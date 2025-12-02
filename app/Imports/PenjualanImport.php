<?php

namespace App\Imports;

use App\Models\TransaksiPenjualan;
use App\Models\DetailPenjualan;
use App\Models\Member;
use App\Models\Menu;
use App\Models\BahanPenyusun;
use App\Models\Stok;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PenjualanImport
{
    public function handle(array $row)
    {
        // pastikan data sudah lowercase (mirip WithHeadingRow)
        $row = array_change_key_case($row, CASE_LOWER);

        // 1️⃣ Cari menu berdasarkan ID_Menu
        $menu = Menu::where('ID_Menu', $row['id_menu'])->first();

        if (!$menu) {
            throw new \Exception("Menu dengan ID_Menu {$row['id_menu']} tidak ditemukan.");
        }

        // 2️⃣ Validasi dan buat Pegawai jika belum ada
        $idPegawai = null;
        if (!empty($row['id_pegawai'])) {
            $pegawai = \App\Models\Pegawai::firstOrCreate(
                ['ID_Pegawai' => $row['id_pegawai']],
                [
                    'ID_Role' => null,
                    'Username' => 'kasir_' . strtolower($row['id_pegawai']),
                    'Password' => md5('password123'), // MD5 hash (32 karakter)
                ]
            );
            $idPegawai = $pegawai->ID_Pegawai;
            if ($pegawai->wasRecentlyCreated) {
                Log::info("✨ Pegawai '{$row['id_pegawai']}' dibuat otomatis dari import (Username: kasir_" . strtolower($row['id_pegawai']) . ", Password: password123)");
            }
        }

        // 3️⃣ Validasi dan buat Manager jika belum ada
        $idManager = null;
        if (!empty($row['id_manager'])) {
            $manager = \App\Models\Manager::firstOrCreate(
                ['ID_Manager' => $row['id_manager']],
                [
                    'ID_Role' => null,
                    'Username' => 'manager_' . strtolower($row['id_manager']),
                    'Password' => md5('password123'), // MD5 hash (32 karakter)
                ]
            );
            $idManager = $manager->ID_Manager;
            if ($manager->wasRecentlyCreated) {
                Log::info("✨ Manager '{$row['id_manager']}' dibuat otomatis dari import (Username: manager_" . strtolower($row['id_manager']) . ", Password: password123)");
            }
        }

        // 4️⃣ Validasi Member (tidak auto-create, hanya warning)
        $idMember = null;
        if (!empty($row['id_member'])) {
            $memberExists = Member::where('ID_Member', $row['id_member'])->exists();
            $idMember = $memberExists ? $row['id_member'] : null;
            if (!$memberExists) {
                Log::warning("⚠️ ID_Member '{$row['id_member']}' tidak ditemukan, diset null");
            }
        }

        DetailPenjualan::where('ID_Penjualan', $row['id_penjualan'])->delete();

        $penjualan = TransaksiPenjualan::updateOrCreate(
            ['ID_Penjualan' => $row['id_penjualan']],
            [
                'Tgl_Penjualan'     => Carbon::parse($row['tgl_penjualan']),
                'ID_Pegawai'        => $idPegawai,
                'ID_Manager'        => $idManager,
                'ID_Member'         => $idMember,
                'Metode_Pembayaran' => $row['metode_pembayaran'] ?? 'Tunai',
                'TotalHarga'        => $row['totalharga'] ?? 0,
                'Jumlah_Item'       => $row['jumlah_item'] ?? 0,
                'Status'            => $row['status'] ?? 'Selesai',
                'Poin_Digunakan'    => $row['poin_digunakan'] ?? 0,
                'Poin_Didapat'      => $row['poin_didapat'] ?? 0,
            ]
        );

        // 6️⃣ Update poin member
        if (!empty($row['id_member'])) {
            $member = Member::where('ID_Member', $row['id_member'])->first();

            if ($member) {
                $member->Poin =
                    ($member->Poin ?? 0)
                    - ($row['poin_digunakan'] ?? 0)
                    + ($row['poin_didapat'] ?? 0);

                $member->save();
            }
        }

        // 7️⃣ Generate ID_Detail_Penjualan otomatis
        $lastDetail = DetailPenjualan::orderBy('ID_Detail_Penjualan', 'desc')->first();

        if ($lastDetail) {
            $lastNumber = intval(substr($lastDetail->ID_Detail_Penjualan, 3));
            $newDetailId = 'DTL' . str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $newDetailId = 'DTL00001';
        }

        // Hitung quantity
        $quantity = $row['quantity'] ?? $row['qty'] ?? 1;

        // 8️⃣ Buat detail
        $detail = new DetailPenjualan([
            'ID_Detail_Penjualan' => $newDetailId,
            'ID_Menu'      => $menu->ID_Menu,
            'ID_Penjualan' => $penjualan->ID_Penjualan,
            'Quantity'     => $quantity,
            'Subtotal'     => $row['subtotal'] ?? ($row['harga'] ?? 0) * $quantity,
        ]);

        $detail->save();

        // 9️⃣ Pengurangan stok
        $bahanList = BahanPenyusun::where('ID_Menu', $menu->ID_Menu)->get();

        foreach ($bahanList as $bahan) {
            $stok = Stok::where('ID_Barang', $bahan->ID_Barang)->first();

            if ($stok) {
                $stok->Jumlah_Item -= ($bahan->Jumlah_Digunakan * $quantity);

                if ($stok->Jumlah_Item < 0) {
                    $stok->Jumlah_Item = 0;
                }

                $stok->save();
            }
        }

        return $penjualan;
    }
}
