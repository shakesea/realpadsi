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
        'Status',
        'Created_At',
        'Updated_At',
    ];

    /**
     * Update status stok berdasarkan jumlah item
     */
    public function updateStatus()
    {
        $jumlah = (int) $this->Jumlah_Item;

        if ($jumlah == 0) {
            $this->Status = 'Habis';
        } elseif ($jumlah <= 200) {
            $this->Status = 'Menipis';
        } else {
            $this->Status = 'Aman';
        }

        return $this;
    }
}
