<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class Transaksidetailtemp extends  Model
{

    protected $table='transaksidetailtemp';

    protected $fillable = [
        'transaksi_id','tanggal','produk_id','hargabeli','hargajual','kuantitas','subtotalhargajual'
    ];



    public function produk()
    {
        return $this->belongsTo('App\Produk');
    }


}
