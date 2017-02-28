<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class Kehadiranrat extends  Model
{

    protected $table='kehadiranrat';

    protected $fillable = [
        'koperasi_id','rat_id','anggotakoperasi_id','kehadiran'
    ];



    public function anggotakoperasi()
    {
        return $this->belongsTo('App\Anggotakoperasi');
    }


    public function rat()
    {
        return $this->belongsTo('App\rat');
    }


    public function tahunoperasi()
    {
        return $this->belongsTo('App\Tahunoperasi','tahunoperasi_id');
    }


}
