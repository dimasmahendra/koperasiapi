<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Administrasikoperasi extends Authenticatable
{
    protected $table='administrasikoperasi';
    protected $fillable = [
        'koperasi_id', 'visi','misi','tujuan' ,'status', 'dokijinsimpanpinjam'
    ];
}
