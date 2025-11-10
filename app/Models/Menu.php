<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'Menu';
    protected $primaryKey = 'ID_Menu';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'ID_Menu',
        'Nama',
        'Harga',
        'Kategori',
        'Deskripsi',
        'Foto',
        'Created_At',
        'Updated_At'
    ];

    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->Created_At = now();
            $model->Updated_At = now();
        });
        static::updating(function ($model) {
            $model->Updated_At = now();
        });
    }

    public function bahanPenyusun()
    {
        return $this->hasMany(BahanPenyusun::class, 'ID_Menu', 'ID_Menu');
    }
}
