<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class Seminarkoperasi extends  Model
{

    protected $table='seminarkoperasi';

    protected $fillable = [
        'koperasi_id','judul','isi','tempat','tanggal','durasi','kapasitas','foto'
    ];


}
