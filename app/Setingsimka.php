<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Setingsimka extends Model
{
	protected $table = 'setingsimka';
	
	protected $fillable = [
        'koperasi_id','bunga','periodebunga', 'minimalsimpanan','tenor','administrasi'
    ];
	
	public function koperasi(){
		return $this->belongsTo('App\Koperasi');
	}
	public function simpananberjangka(){
		return $this->hasOne('App\Simpananberjangka');
	}
	public function pengambilansimka(){
		return $this->hasOne('App\Pengambilansimka');
	}
}
