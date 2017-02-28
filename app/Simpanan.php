<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Simpanan extends Model
{
    protected $table='simpanan';

    protected $fillable=['koperasi_id','anggotakoperasi_id','jenissimpanan','jumlah','tanggalbayar','status'];


    public function anggotakoperasi()
       {
           return $this->belongsTo('App\Anggotakoperasi')->select(['id','nama']);
       }

}
