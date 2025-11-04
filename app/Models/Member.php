<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $table = 'Member';          // sesuai tabel di phpMyAdmin
    protected $primaryKey = 'ID_Member';  // primary key kamu
    public $incrementing = false;         // ID_Member bukan auto increment
    public $timestamps = false;           // tidak pakai created_at & updated_at bawaan
    protected $keyType = 'string';        // karena ID_Member adalah varchar

    protected $fillable = [
        'ID_Member',
        'ID_Manager',
        'ID_Pegawai',
        'Nama',
        'No_Telepon',
        'Email',
        'Alamat',
        'Poin',
        'Created_At',
        'Deleted_At',
    ];
}
