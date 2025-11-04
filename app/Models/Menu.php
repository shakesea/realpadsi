<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'Menu';
    protected $primaryKey = 'ID_Menu';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'ID_Menu',
        'Nama',
        'Harga',
        'Kategori',
        'Foto',
        'Created_At',
        'Updated_At'
    ];

    public function bahanPenyusun()
    {
        return $this->hasMany(BahanPenyusun::class, 'ID_Menu', 'ID_Menu');
    }
}
