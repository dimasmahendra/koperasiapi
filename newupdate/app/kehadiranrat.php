<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class Kehadiranrat extends  Model
{

    protected $table='kehadiranrat';

    protected $fillable = [
        'koperasi_id','rat_id','anggotakoperasi_id','kehadiran',
    ];


}
