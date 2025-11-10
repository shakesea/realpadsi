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
        'ID_Member',
        'Tgl_Penjualan',
        'TotalHarga',
        'Jumlah_Item',
        'Status',
        'Metode_Pembayaran',
        'Poin_Digunakan',
        'Poin_Didapat'
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'ID_Pegawai', 'ID_Pegawai');
    }

    public function manager()
    {
        return $this->belongsTo(Manager::class, 'ID_Manager', 'ID_Manager');
    }

    public function member()
    {
        return $this->belongsTo(Member::class, 'ID_Member', 'ID_Member');
    }

    public function detailPenjualan()
    {
        return $this->hasMany(DetailPenjualan::class, 'ID_Penjualan', 'ID_Penjualan');
    }
}
