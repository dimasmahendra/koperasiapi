<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class Trainingkementerian extends  Model
{

    protected $table='trainingkementerian';

    protected $fillable = [
        'judul','isi','tempat','tanggal','durasi','kapasitas','foto'
    ];


}
