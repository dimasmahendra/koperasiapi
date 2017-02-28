<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Komentarinformasikoperasi extends Model
{
    protected $table='komentarinformasikoperasi';

    protected $fillable=['infokoperasi_id','anggotakoperasi_id','komentar'];


    public function infokoperasi()
    {
        return $this->belongsTo('App\Infokoperasi');
    }

    public function anggotakoperasi()
    {
        return $this->belongsTo('App\Anggotakoperasi')->select(['id', 'nama']);
    }







}
