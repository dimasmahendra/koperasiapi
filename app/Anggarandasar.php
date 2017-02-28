<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Anggarandasar extends Model
{
    protected $table='anggarandasar';

    protected $fillable = [
        'koperasi_id','nomorpad','tanggalpad', 'nomorlembaran','tanggallembaran','notarispad','camatpad','dokumen','status'
    ];
    public function koperasi()
    {
        return $this->belongsTo('App\Koperasi');
    }
}
