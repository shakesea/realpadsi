<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    protected $table = 'Pegawai'; // nama tabel persis di database Hostinger
    protected $primaryKey = 'ID_Pegawai';
    public $incrementing = false; // karena ID_Pegawai = varchar (contoh: EMP001)
    protected $keyType = 'string';

    // Tidak ada timestamps di tabel
    public $timestamps = false;

    protected $fillable = [
        'ID_Pegawai',
        'ID_Role',
        'Username',
        'Password',
    ];
}
