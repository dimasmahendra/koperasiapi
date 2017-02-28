<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class Infokementerian extends  Model
{

    protected $table='infokementerian';

    protected $fillable = [
        'adminkementerian_id','judul','isi','foto','tanggalmulai','tanggalselesai'
    ];


    public function komentarinformasikementerian()
    {
        return $this->hasMany('App\Komentarinformasikementerian')->orderby('id','desc');
    }


}
