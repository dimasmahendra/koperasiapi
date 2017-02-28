<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Indikatorusaha extends Model
{
    protected $table='indikatorusaha';

    protected $fillable = [
        'koperasi_id','tahun','modalsendiri', 'modalluar','asset','volumeusaha','selisihhasilusaha'
    ];

    public function koperasi()
    {
        return $this->belongsTo('App\Koperasi','id');
    }
}
