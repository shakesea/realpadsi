<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InformasiPegawai extends Model
{
    protected $table = 'Informasi_Pegawai';
    protected $primaryKey = 'ID_InfoPegawai';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'ID_InfoPegawai',
        'ID_Pegawai',
        'Nama',
        'Email',
        'No_Telepon',
        'Tgl_Lahir',
        'Umur',
        'Jenis_Kelamin',
        'Created_At',
    ];
}
