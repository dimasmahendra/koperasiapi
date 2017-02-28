<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Komentarinformasikementerian extends Model
{
    protected $table='komentarinformasikementerian';

    protected $fillable=['infokementerian_id','anggotakoperasi_id','komentar'];


    public function infokementerian()
    {
        return $this->belongsTo('App\Infokementerian');
    }

    public function anggotakoperasi()
    {
        return $this->belongsTo('App\Anggotakoperasi')->select(['id', 'nama','koperasi_id']);
    }





}
