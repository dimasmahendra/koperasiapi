<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class Rat extends  Model
{

    protected $table='rat';

    protected $fillable = [
        'koperasi_id','tahun','tempat','tanggal','info'
    ];


}
