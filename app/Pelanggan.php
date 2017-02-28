<?php

namespace App;

//use Illuminate\Foundation\Auth\Anggotakoperasi as Authenticatable;
use Illuminate\Database\Eloquent\Model;

class Pelanggan extends  Model
{

    protected $table='pelanggan';

    protected $fillable = [
        'koperasi_id','nama', 'telepon','email','alamat','kelurahan_id','foto'
    ];



}
