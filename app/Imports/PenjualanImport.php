<?php

namespace App\Imports;

use App\Models\TransaksiPenjualan;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class PenjualanImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new TransaksiPenjualan([
            'ID_Penjualan'      => $row['id_penjualan'] ?? null,
            'Tgl_Penjualan'     => isset($row['tgl_penjualan']) 
                                    ? Carbon::parse($row['tgl_penjualan'])
                                    : null,
            'ID_Pegawai'        => $row['id_pegawai'] ?? null,
            'ID_Manager'        => $row['id_manager'] ?? null,
            'ID_Member'         => $row['id_member'] ?? null,
            'Metode_Pembayaran' => $row['metode_pembayaran'] ?? null,
            'TotalHarga'        => $row['totalharga'] ?? 0,
            'Jumlah_Item'       => $row['jumlah_item'] ?? 0,
            'Status'            => $row['status'] ?? 'Selesai',
            'Poin_Digunakan'    => $row['poin_digunakan'] ?? 0,
            'Poin_Didapat'      => $row['poin_didapat'] ?? 0,
        ]);
    }
}
