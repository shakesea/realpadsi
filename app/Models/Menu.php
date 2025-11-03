<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'Menu';
    protected $primaryKey = 'ID_Menu';
    public $timestamps = true;

    protected $fillable = [
        'ID_Stok','Nama','Harga','Foto','Kategori'
    ];
}
