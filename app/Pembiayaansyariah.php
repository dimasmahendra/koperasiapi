<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pembiayaansyariah extends Model
{

	protected $connection = 'mysql2';
   protected $table='pembiayaansyariah';

    protected $fillable = [
        'koperasi_id','pembiayaan_id', 'anggotakoperasi_id','rekening','jumlahpinjam','nobukti','bonuspersen','bonusfix','tenor',
        'sisatenor','jatuhtempo','angsuran','sisa','keteranganlain','status'
    ];

     public function koperasi()
    {
        return $this->belongsTo('App\Koperasi');
    }

     public function anggotakoperasi()
    {
        return $this->belongsTo('App\Anggotakoperasi');
    }

     public function pembiayaan()
    {
        return $this->belongsTo('App\Pembiayaan');
    }
    public function pembiayaansyariahdetail()
    {
        return $this->hasMany('App\Pembiayaansyariahdetail');
    }
}
