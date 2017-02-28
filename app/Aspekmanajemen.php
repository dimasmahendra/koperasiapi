<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Aspekmanajemen extends Authenticatable
{
    protected $table='aspekmanajemen';
    protected $fillable = [
        'aspek', 'jumlahpertanyaan'
    ];

    public function aspekmanajemen()
    {
        return $this->hasMany('App\Aspekpertanyaan');
    }    
}