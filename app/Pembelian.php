<?php

namespace App;

//use Illuminate\Foundation\Auth\Anggotakoperasi as Authenticatable;
use Illuminate\Database\Eloquent\Model;

class Pembelian extends  Model
{

    protected $table='pembelian';

    protected $fillable = [
        'koperasi_id','tahunoperasi_id', 'tanggal','totalhargabeli','metode'
    ];

    public function tahunoperasi()
    {
        return $this->belongsTo('App\Tahunoperasi');
    }



}
