<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Skalakoperasi extends Model
{
    protected $table='skalakoperasi';

    protected $fillable=['skala','keterangan'];


    public function adminkementerian()
    {
        return $this->hasMany('App\Adminkementerian');
    }




}
