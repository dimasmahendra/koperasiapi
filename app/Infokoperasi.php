<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class Infokoperasi extends  Model
{

    protected $table='infokoperasi';

    protected $fillable = [
        'koperasi_id','judul','isi','foto','tanggalmulai','tanggalselesai'
    ];



    public function komentarinformasikoperasi()
    {
        return $this->hasMany('App\Komentarinformasikoperasi')->orderby('id','desc');
    }





}
