<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $table = 'Member'; // nama tabel persis dari phpMyAdmin
    protected $primaryKey = 'ID_Member'; // kolom primary key
    public $timestamps = false; // karena tabel kamu pakai Created_At manual, bukan created_at otomatis

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
