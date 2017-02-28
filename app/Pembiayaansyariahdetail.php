<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pembiayaansyariahdetail extends Model
{
     protected $connection = 'mysql2';
   protected $table='pembiayaansyariahdetail';

    protected $fillable = [
       'pembiayaansyariah_id', 'angsuran','metode','nobukti','tanggalbayar'
    ];

     public function pembiayaansyariah()
    {
         return $this->belongsTo('App\Pembiayaansyariah');
    }
}
