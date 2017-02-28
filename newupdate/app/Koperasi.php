<?php

namespace App;

//use Illuminate\Foundation\Auth\Anggotakoperasi as Authenticatable;
use Illuminate\Database\Eloquent\Model;

class Koperasi extends  Model
{

    protected $table='koperasi';

    protected $fillable = [
        'tipekoperasi_id','skalakoperasi_id','nama', 'telepon','email',
        'alamat','kelurahan_id','nomorregistrasi','foto','latitude','longitude'
    ];

    protected $hidden = [
        'password',
    ];


    public function kelurahan()
    {
        return $this->belongsTo('App\Kelurahan');
    }

    public function anggotakoperasi()
    {
        return $this->hasMany('App\Anggotakoperasi');
    }

    public function adminkoperasi()
    {
        return $this->hasMany('App\Adminkoperasi');
    }



}
