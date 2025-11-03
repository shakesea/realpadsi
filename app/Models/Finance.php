<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Finance extends Model
{
    protected $table = 'Finance';
    protected $primaryKey = 'ID_Finance';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'ID_Finance',
        'ID_Role',
        'Username',
        'Password',
    ];
}
