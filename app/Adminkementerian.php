<?php

namespace App;

//use Illuminate\Foundation\Auth\Anggotakoperasi as Authenticatable;
use Illuminate\Database\Eloquent\Model;

class Adminkementerian extends  Model
{

    protected $table='adminkementerian';

    protected $fillable = [
        'nama','jabatan','email','username','password','akseskementerian_id','logingagal','status'

    ];

    protected $hidden = [
        'password',
    ];

    public function koperasi()
    {
        return $this->HasMany('App\Koperasi');
    }


    public function akseskementerian()
    {
        return $this->BelongsTo('App\Akseskementerian');
    }







}
