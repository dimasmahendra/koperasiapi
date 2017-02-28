<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Akseskoperasi extends Model
{
    protected $table='akseskoperasi';

    protected $fillable=['akses','maxaccount'];


    public function adminkoperasi()
    {
        return $this->hasMany('App\Adminkoperasi');
    }




}
