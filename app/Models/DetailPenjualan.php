<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailPenjualan extends Model
{
    protected $table = 'Detail_Penjualan';
    protected $primaryKey = 'ID_Detail_Penjualan';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'ID_Detail_Penjualan',
        'ID_Penjualan',
        'ID_Menu',
        'Quantity',
        'Subtotal',
    ];

    public function transaksiPenjualan()
    {
        return $this->belongsTo(TransaksiPenjualan::class, 'ID_Penjualan', 'ID_Penjualan');
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'ID_Menu', 'ID_Menu');
    }
}
