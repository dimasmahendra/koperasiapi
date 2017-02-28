<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class Tipekomponenshu extends  Model
{

    protected $table='tipekomponenshu';

    protected $fillable = [
        'tipekomponenshu','maxkomponen'
    ];



    public function komponenshu()
    {
        return $this->hasMany('App\Komponenshu');
    }



}
