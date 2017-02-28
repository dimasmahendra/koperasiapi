<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class Komponenshu extends  Model
{

    protected $table='komponenshu';

    protected $fillable = [
        'koperasi_id','tahunoperasi_id','tipekomponenshu_id','komponen','persentase'
    ];



    public function tahunoperasi()
    {
        return $this->belongsTo('App\Tahunoperasi');
    }


    public function tipekomponenshu()
    {
        return $this->belongsTo('App\Tipekomponenshu');
    }



}
