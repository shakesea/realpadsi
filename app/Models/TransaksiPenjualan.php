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

    protected $fillable = [
        'ID_Penjualan',
        'ID_Pegawai',
        'ID_Manager',
        'Tgl_Penjualan',
        'TotalHarga',
        'Jumlah_Item',
        'Status',
    ];
}
