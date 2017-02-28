<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class Bookingseminarkoperasi extends  Model
{

    protected $table='bookingseminarkoperasi';

    protected $fillable = [
        'seminarkoperasi_id','anggotakoperasi_id'
    ];



   public function seminarkoperasi()
    {
        return $this->BelongsTo('App\Seminarkoperasi','id');
    }

   public function anggotakoperasi()
    {
        return $this->BelongsTo('App\Anggotakoperasi','id');
    }

}
