<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stok extends Model
{
    protected $table = 'Stok';
    protected $primaryKey = 'ID_Barang'; // ✅ sesuai database
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'ID_Barang',
        'Nama',
        'Jumlah_Item',
        'Kategori',
        'Created_At',
        'Updated_At',
    ];
}
