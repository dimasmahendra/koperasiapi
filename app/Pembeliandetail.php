<?php

namespace App;

//use Illuminate\Foundation\Auth\Anggotakoperasi as Authenticatable;
use Illuminate\Database\Eloquent\Model;

class Pembeliandetail extends  Model
{

    protected $table='pembeliandetail';

    protected $fillable = [
        'pembelian_id','tanggal', 'produk_id','hargabeli','kuantitas','subtotalhargabeli'
    ];

    public function produk()
    {
        return $this->belongsTo('App\Produk');
    }



}
