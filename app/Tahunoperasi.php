<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tahunoperasi extends Model
{
    protected $table='tahunoperasi';

    protected $fillable=['koperasi_id','tanggalmulai','tanggalselesai','status'];


    public function Biayausaha()
    {
        return $this->hasMany('App\Biayausaha');
    }

    public function jurnalkoperasi()
    {
        return $this->hasMany('App\Jurnalkoperasi');
    }


    public function komponenshu()
    {
        return $this->hasMany('App\Komponenshu');
    }


    public function pembelian()
    {
        return $this->hasMany('App\Pembelian');
    }


    public function kehadiranrat()
    {
        return $this->HasMany('App\Kehadiranrat','tahunoperasi_id');
    }


}
