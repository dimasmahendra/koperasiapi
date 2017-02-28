<?php

namespace App;

//use Illuminate\Foundation\Auth\Anggotakoperasi as Authenticatable;
use Illuminate\Database\Eloquent\Model;

class Adminkoperasi extends  Model
{

    protected $table='adminkoperasi';

    protected $fillable = [
        'koperasi_id','akseskoperasi_id','username','password','nama','email','telepon','logingagal','status','foto'

    ];

    protected $hidden = [
        'password',
    ];

    public function koperasi()
    {
        return $this->belongsTo('App\Koperasi');
    }
    public function akseskoperasi()
    {
        return $this->belongsTo('App\Akseskoperasi');
    }








}
