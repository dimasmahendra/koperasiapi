<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Kecamatan extends Model
{
    protected $table='kecamatan';

    protected $fillable=['kabupatenkota_id','nama'];


    public function kelurahan()
    {
        return $this->hasMany('App\Kelurahan');
    }

    public function kabupatenkota()
    {
        return $this->belongsTo('App\Kabupatenkota');
    }


}
