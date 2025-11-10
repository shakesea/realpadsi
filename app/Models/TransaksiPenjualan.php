<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiPenjualan extends Model
{
    protected $table = 'TransaksiPenjualan';
    protected $primaryKey = 'ID_Penjualan';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    // Kolom yang boleh diisi (fillable)
    protected $fillable = [
        'ID_Penjualan',
        'ID_Pegawai',
        'ID_Manager',
        'ID_Member',          // ✅ tambahkan untuk relasi member
        'Tgl_Penjualan',
        'Metode_Pembayaran',  // ✅ tambahkan untuk metode pembayaran
        'TotalHarga',
        'Jumlah_Item',
        'Status',
        'Poin_Digunakan',     // ✅ tambahkan poin yang digunakan
        'Poin_Didapat',       // ✅ tambahkan poin yang didapat
    ];
}
