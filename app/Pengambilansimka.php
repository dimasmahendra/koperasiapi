<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pengambilansimka extends Model
{
    protected $table='pengambilansimka';

    protected $fillable = [
        'simpananberjangka_id','metode_id', 'setingsimka_id','refnumberssp','penerima','administrasi'
    ];
	
	public function simpananberjangka(){
		return $this->hasOne('App\Simpananberjangka');
	}
	public function setingsimka(){
		return $this->belongsTo('App\Setingsimka');
	}
	public function metode(){
		return $this->belongsTo('App\Metode');
	}
}
