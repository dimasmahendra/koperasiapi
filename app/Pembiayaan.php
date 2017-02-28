<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pembiayaan extends Model
{
	protected $connection = 'mysql2';
     protected $table='pembiayaan';

    protected $fillable = [
        'koperasi_id','namaprogram', 'akad','deskripsi'
    ];

    public function koperasi()
    {
        return $this->belongsTo('App\Koperasi','koperasi_id');
    }

    public function pembiayaansyariah()
    {
    	return $this->hasMany('App\Pembiayaansyariah');
    }

}
