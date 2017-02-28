<?php

namespace App;

//use Illuminate\Foundation\Auth\Anggotakoperasi as Authenticatable;
use Illuminate\Database\Eloquent\Model;

class Kategori extends  Model
{

    protected $table='kategori';

    protected $fillable = [
        'koperasi_id','nama'
    ];


    public function produk()
    {
        return $this->hasMany('App\Produk');
    }
}
