<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class Transaksidetail extends  Model
{

    protected $table='transaksidetail';

    protected $fillable = [
        'transaksi_id','tanggal','produk_id','hargabeli','hargajual','kuantitas','subtotalhargabeli','subtotalhargajual','kadaluarsa'
    ];



    public function produk()
    {
        return $this->belongsTo('App\Produk');
    }


}
