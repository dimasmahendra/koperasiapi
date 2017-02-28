<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class Bookingseminarkementerian extends  Model
{

    protected $table='bookingseminarkementerian';

    protected $fillable = [
        'seminarkementerian_id','anggotakoperasi_id','koperasi_id','status'
    ];



   public function seminarkementerian()
    {
        return $this->BelongsTo('App\Seminarkementerian','id');
    }

    public function anggotakoperasi()
    {
        return $this->HasMany('App\Anggotakoperasi','id','anggotakoperasi_id')->select(['id','nama','email','telepon','jeniskelamin','alamat','foto','status']);
    }

}
