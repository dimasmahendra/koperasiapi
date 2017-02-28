<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class Shu extends  Model
{

    protected $table='shu';

    protected $fillable = [
        'koperasi_id','tahunoperasi_id','anggotakoperasi_id','jumlahtransaksi','jumlahsimpanan','shu'
    ];



    public function tahunoperasi()
    {
        return $this->belongsTo('App\tahunoperasi');
    }

    public function anggotakoperasi()
    {
        return $this->belongsTo('App\Anggotakoperasi')->select('id','nama');
    }



}
