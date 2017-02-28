<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Aspekpertanyaan extends Authenticatable
{
    protected $table='aspekpertanyaan';
    protected $fillable = [
        'koperasi_id', 'aspekmanajemen_id', 'tanggalbuat','nilai1','nilai2','nilai3','nilai4','nilai5','nilai6','nilai7','nilai8','nilai9','nilai10','nilai11', 'nilai12'
    ];

    public function aspekmanajemen()
    {
        return $this->belongsTo('App\Aspekmanajemen');
    }
}