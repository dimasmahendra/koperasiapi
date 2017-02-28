<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Kelompokkoperasi extends Model
{
    protected $table='kelompokkoperasi';

    protected $fillable=['kelompok'];

    public function koperasi()
    {
        return $this->hasMany('App\Koperasi','kelompokkoperasi_id');
    }
}
