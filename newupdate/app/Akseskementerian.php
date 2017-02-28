<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Akseskementerian extends Model
{
    protected $table='akseskementerian';

    protected $fillable=['akses','maxaccount'];


    public function adminkementerian()
    {
        return $this->hasMany('App\Adminkementerian');
    }




}
