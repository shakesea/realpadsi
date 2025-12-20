<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BahanPenyusun extends Model
{
    protected $table = 'Bahan_Penyusun';
    protected $primaryKey = 'ID_Penyusun';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'ID_Penyusun',
        'ID_Menu',
        'ID_Barang',
        'Jumlah_Digunakan',
        'Kategori',
        'Created_At',
        'Updated_At',
    ];

    // Relasi ke Stok
    public function stok()
    {
        return $this->belongsTo(Stok::class, 'ID_Barang', 'ID_Barang');
    }

    // Relasi ke Menu
    public function menu()
    {
        return $this->belongsTo(Menu::class, 'ID_Menu', 'ID_Menu');
    }
}
