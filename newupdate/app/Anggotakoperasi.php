<?php

namespace App;

//use Illuminate\Foundation\Auth\Anggotakoperasi as Authenticatable;
use Illuminate\Database\Eloquent\Model;

class Anggotakoperasi extends  Model
{

    protected $table='anggotakoperasi';

    protected $fillable = [
        'koperasi_id','nama', 'telepon','email','jeniskelamin','username','password',
        'alamat','kelurahan_id','foto','logingagal','status'
    ];

    protected $hidden = [
        'password'
    ];


    public function kelurahan()
    {
        return $this->belongsTo('App\Kelurahan');
    }

    public function koperasi()
    {
        return $this->belongsTo('App\Koperasi')->select(['id','nama']);
    }

    public function shu()
    {
        return $this->belongsTo('App\Shu');
    }

    /*public function koperasinama()
    {
        return $this->belongsTo('App\Koperasi')->select(['id','nama']);
    } */


    public function bookingtrainingkoperasi()
    {
        return $this->HasMany('App\Bookingtrainingkoperasi','anggotakoperasi_id');
    }


    public function komentarinformasikoperasi()
    {
        return $this->hasMany('App\Komentarinformasikoperasi');
    }

    public function komentarinformasikementerian()
    {
        return $this->hasMany('App\Komentarinformasikementerian');
    }


    public function simpanan()
    {
        return $this->HasMany('App\Simpanan','anggotakoperasi_id');
    }


    public function bookingseminarkementerian()
    {
        return $this->HasMany('App\Bookingseminarkementerian','id');
    }


    public function bookingtrainingkementerian()
    {
        return $this->belongsTo('App\Bookingtrainingkementerian','id');
    }


    public function transaksi()
    {
        return $this->HasMany('App\Transaksi');
    }



}
