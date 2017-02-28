<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tabunganproduk extends Model
{
    protected $connection = 'mysql2';
   protected $table='tabunganproduk';

    protected $fillable = [
        'koperasi_id','namaprogram', 'akad','deskripsi'
    ];

     public function koperasi()
    {
        return $this->belongsTo('App\Koperasi');
    }

}
