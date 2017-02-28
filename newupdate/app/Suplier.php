<?php

namespace App;

//use Illuminate\Foundation\Auth\Anggotakoperasi as Authenticatable;
use Illuminate\Database\Eloquent\Model;

class Suplier extends  Model
{

    protected $table='suplier';

    protected $fillable = [
        'koperasi_id','nama', 'telepon','email','alamat','kelurahan_id','penanggungjawab','kontakperson'
    ];


    public function produk()
    {
        return $this->hasmany('App\Produk');
    }

    public function kelurahan()
    {
        return $this->belongsTo('App\Kelurahan');
    }



}
