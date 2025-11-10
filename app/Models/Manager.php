<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Manager extends Model
{
    protected $table = 'Manager';
    protected $primaryKey = 'ID_Manager';
    public $incrementing = false;        // ❗ Wajib: karena ID_Manager bukan auto increment
    protected $keyType = 'string';       // ❗ Wajib: agar MGR001 tidak di-cast jadi 0
    public $timestamps = false;

    protected $fillable = [
        'ID_Manager',
        'ID_Role',
        'Username',
        'Password'
    ];
}
