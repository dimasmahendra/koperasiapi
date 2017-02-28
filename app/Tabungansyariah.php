<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tabungansyariah extends Model
{
    protected $connection = 'mysql2';
   protected $table='tabungansyariah';

    protected $fillable = [
        'koperasi_id','tabunganproduk_id', 'anggotakoperasi_id','rekening','saldo','bonuscurrency','bonuspersen','diambilpada','status'
         ];

    public function koperasi()
    {
    	return $this->belongsTo('App\Koperasi');
    }

     public function anggotakoperasi()
    {
        return $this->belongsTo('App\Anggotakoperasi');
    }

     public function tabunganproduk()
    {
        return $this->belongsTo('App\Tabunganproduk');
    }



}
