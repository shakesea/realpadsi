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
<<<<<<< HEAD
        'ID_Member',
=======
        'ID_Member',          // ✅ tambahkan untuk relasi member
>>>>>>> 78f4fb824686e50d06c81e9f5689bc4de2c874e4
        'Tgl_Penjualan',
        'Metode_Pembayaran',  // ✅ tambahkan untuk metode pembayaran
        'TotalHarga',
        'Jumlah_Item',
        'Status',
<<<<<<< HEAD
        'Metode_Pembayaran',
        'Poin_Digunakan',
        'Poin_Didapat'
=======
        'Poin_Digunakan',     // ✅ tambahkan poin yang digunakan
        'Poin_Didapat',       // ✅ tambahkan poin yang didapat
>>>>>>> 78f4fb824686e50d06c81e9f5689bc4de2c874e4
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
