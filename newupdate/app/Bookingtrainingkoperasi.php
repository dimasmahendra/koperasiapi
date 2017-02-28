<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class Bookingtrainingkoperasi extends  Model
{

    protected $table='bookingtrainingkoperasi';

    protected $fillable = [
        'trainingkoperasi_id','anggotakoperasi_id'
    ];

    

   public function trainingkoperasi()
    {
        return $this->BelongsTo('App\Trainingkoperasi','id');
    }

   public function anggotakoperasi()
    {
        return $this->BelongsTo('App\Anggotakoperasi','id');
    }


    public function cobacount()
    {
        return $this->hasOne('bookingtrainingkoperasi')->selectRaw('anggotakoperasi_id, count(anggotakoperasi_id) as countt')->groupBy('trainingkoperasi_id');
        // replace module_id with appropriate foreign key if needed
    }

}
