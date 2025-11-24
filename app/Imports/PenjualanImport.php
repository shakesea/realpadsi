<?php

namespace App\Imports;

use App\Models\TransaksiPenjualan;
use App\Models\DetailPenjualan;
use App\Models\Member;
use App\Models\Menu;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PenjualanImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // 1️⃣ Cari menu berdasarkan ID_Menu
        $menu = Menu::where('ID_Menu', $row['id_menu'])->first();

        // Jika menu belum ada → buat (agar tidak error)
        if (!$menu) {
            $menu = Menu::create([
                'ID_Menu' => $row['id_menu'],
                'Subtotal' => $row['subtotal'] ?? 0,
            ]);
        }

        // 2️⃣ Buat transaksi jika belum ada
        $penjualan = TransaksiPenjualan::firstOrCreate(
            ['ID_Penjualan' => $row['id_penjualan']],
            [
                'Tgl_Penjualan'     => Carbon::parse($row['tgl_penjualan']),
                'ID_Pegawai'        => $row['id_pegawai'] ?? null,
                'ID_Manager'        => $row['id_manager'] ?? null,
                'ID_Member'         => $row['id_member'] ?? null,
                'Metode_Pembayaran' => $row['metode_pembayaran'] ?? 'Tunai',
                'TotalHarga'        => $row['totalharga'] ?? 0,
                'Jumlah_Item'       => $row['jumlah_item'] ?? 0,
                'Status'            => $row['status'] ?? 'Selesai',
                'Poin_Digunakan'    => $row['poin_digunakan'] ?? 0,
                'Poin_Didapat'      => $row['poin_didapat'] ?? 0,
            ]
        );

        // ⭐ 3️⃣ Update poin member bila ada ID_Member
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

        // 🔢 4️⃣ Generate ID_Detail_Penjualan otomatis
        $lastDetail = DetailPenjualan::orderBy('ID_Detail_Penjualan', 'desc')->first();
        if ($lastDetail) {
            $lastNumber = intval(substr($lastDetail->ID_Detail_Penjualan, 3));
            $newDetailId = 'DTL' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newDetailId = 'DTL001';
        }

        // 5️⃣ Buat detail penjualan untuk item ini
        return new DetailPenjualan([
            'ID_Detail_Penjualan' => $newDetailId,
            'ID_Menu'      => $menu->ID_Menu,
            'ID_Penjualan' => $penjualan->ID_Penjualan,
            'Quantity'     => $row['quantity'] ?? $row['qty'] ?? 1,
            'Subtotal'     => $row['subtotal'] ?? ($row['harga'] ?? 0) * ($row['quantity'] ?? 1),
        ]);
    }
}
