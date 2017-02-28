<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sektorusaha extends Model
{
    protected $table='sektorusaha';

    protected $fillable=['sektor'];

    public function koperasi()
    {
        return $this->hasMany('App\Koperasi');
    }
}
