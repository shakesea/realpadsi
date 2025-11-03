<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Manager extends Model
{
    protected $table = 'Manager'; // Nama tabel di database
    protected $primaryKey = 'ID_Manager'; // Primary key dari tabel
    public $timestamps = false; // Karena tabel tidak punya created_at / updated_at

    protected $fillable = [
        'ID_Manager',
        'ID_Role',
        'Username',
        'Password'
    ];
}
