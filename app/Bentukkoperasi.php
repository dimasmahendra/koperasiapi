<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Bentukkoperasi extends Model
{
    protected $table='bentukkoperasi';

    protected $fillable = [
        'bentuk'

    ];


    public function koperasi()
    {
        return $this->hasMany('App\Koperasi');
    }
}
