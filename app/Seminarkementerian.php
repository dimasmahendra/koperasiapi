<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class Seminarkementerian extends  Model
{

    protected $table='seminarkementerian';

    protected $fillable = [
        'judul','isi','tempat','tanggal','durasi','kapasitas','foto'
    ];


}
