<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class Rat extends  Model
{

    protected $table='rat';

    protected $fillable = [
        'koperasi_id','tahunoperasi_id','tempat','tanggal','info'
    ];


    public function rat()
    {
        return $this->HasMany('App\Rat','rat_id');
    }


}
