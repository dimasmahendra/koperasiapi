<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class Transaksi extends  Model
{

    protected $table='transaksi';

    protected $fillable = [
        'koperasi_id','tahunoperasi_id','anggotakoperasi_id','tanggal','jumlah','bayar','kembali',
        'totalhargajual','metode','status','refnumberssp'
    ];


    public function anggotakoperasi()
    {
        return $this->belongsTo('App\Anggotakoperasi');
    }


}
