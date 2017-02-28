<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Kabupatenkota extends Model
{
    protected $table='kabupatenkota';

    protected $fillable=['provinsi_id','nama'];

    public function kecamatan()
    {
        return $this->hasMany('App\Kecamatan');
    }

    public function provinsi()
    {
        return $this->belongsTo('App\Provinsi');
    }


}
