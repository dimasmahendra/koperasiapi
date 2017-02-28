<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Komentartrainingkoperasi extends Model
{
    protected $table='komentartrainingkoperasi';

    protected $fillable=['trainingkoperasi_id','anggotakoperasi_id','komentar'];


    public function Seminarkoperasi()
    {
        return $this->hasMany('App\Seminarkoperasi');
    }

    public function Anggotakoperasi()
    {
        return $this->hasMany('App\Anggotakoperasi');
    }




}
