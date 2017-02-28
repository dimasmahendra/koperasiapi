<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class Pesankementeriankoperasi extends  Model
{

    protected $table='pesankementeriankoperasi';

    protected $fillable = [
        'koperasi_id','judul','isi','status'
    ];



    public function koperasi()
    {
        return $this->Belongsto('App\Koperasi');
    }





}
