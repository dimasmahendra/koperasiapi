<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Kelurahan extends Model
{
    //protected $primaryKey ='id';


    protected $table='kelurahan';



    protected $fillable=['id','kecamatan_id','nama'];

    public function kecamatan()
    {
        return $this->belongsTo('App\Kecamatan');
    }

    public function anggotakoperasi()
    {
        return $this->hasMany('App\Anggotakoperasi');
    }





}
