<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Komentarseminarkoperasi extends Model
{
    protected $table='komentarseminarkoperasi';

    protected $fillable=['seminarkoperasi_id','anggotakoperasi_id','komentar'];


    public function Seminarkoperasi()
    {
        return $this->hasMany('App\Seminarkoperasi');
    }

    public function Anggotakoperasi()
    {
        return $this->hasMany('App\Anggotakoperasi');
    }





}
