<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Provinsi extends Model
{
    protected $table='provinsi';

    protected $fillable=['nama'];


    public function kabupatenkota()
    {
        return $this->hasMany('App\Kabupatenkota');
    }


}
